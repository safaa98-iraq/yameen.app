#!/usr/bin/env python3
"""
Build yameen.app font files from the google/fonts repository.

For every OFL family whose METADATA.pb lists the "arabic" subset (plus the
Latin families named in pairings.json) this script writes, per weight:

  - a full static TTF          (used by the PNG/SVG exporter)
  - a full WOFF2               (font pages, CSS snippet reference)
  - an Arabic-only WOFF2       (lazy-loaded previews)

and a manifest at <out>/fonts.json. Variable fonts are instanced to static
weights. Results are cached per family by the git tree hash of its folder,
so re-running only rebuilds families that changed (idempotent).

Usage:
  python3 scripts/fonts/build.py --out storage/app/fonts [--repo DIR] [--only cairo,amiri]
Requires: fonttools, brotli (see requirements.txt) and git.
"""

from __future__ import annotations

import argparse
import logging
import hashlib
import io
import json
import re
import shutil
import subprocess
import os
import sys
from concurrent.futures import ProcessPoolExecutor, as_completed
from pathlib import Path

from fontTools import subset as ftsubset
from fontTools.ttLib import TTFont
from fontTools.varLib import instancer

REPO_URL = "https://github.com/google/fonts.git"
BUILD_VERSION = 4  # bump to force a full rebuild when output format changes

# Characters kept in the Arabic preview subset: Arabic blocks, presentation
# forms, joiners, digits and the punctuation people type in sample text.
ARABIC_PREVIEW_UNICODES = (
    "U+0020-0040,U+005B-0060,U+007B-007E,U+00A0,U+00AB,U+00BB,U+00D7,"
    "U+0600-06FF,U+0750-077F,U+0870-08FF,U+200C-200F,U+2010-2027,U+2030-205E,"
    "U+FB50-FDFF,U+FE70-FEFF"
)
LATIN_PREVIEW_UNICODES = (
    "U+0000-00FF,U+0131,U+0152-0153,U+02BB-02BC,U+02C6,U+02DA,U+02DC,"
    "U+2000-206F,U+20AC,U+2122,U+2191,U+2193,U+2212,U+2215,U+FEFF,U+FFFD"
)

# Presentation forms the fixer emits: final, initial and medial forms of the
# basic letters plus the lam-alef ligatures. Isolated letters are emitted as
# base letters, so their isolated forms (below) are not required.
ISOLATED_FORMS = {
    0xFE80, 0xFE81, 0xFE83, 0xFE85, 0xFE87, 0xFE89, 0xFE8D, 0xFE8F, 0xFE93, 0xFE95, 0xFE99, 0xFE9D,
    0xFEA1, 0xFEA5, 0xFEA9, 0xFEAB, 0xFEAD, 0xFEAF, 0xFEB1, 0xFEB5, 0xFEB9, 0xFEBD, 0xFEC1, 0xFEC5,
    0xFEC9, 0xFECD, 0xFED1, 0xFED5, 0xFED9, 0xFEDD, 0xFEE1, 0xFEE5, 0xFEE9, 0xFEED, 0xFEEF, 0xFEF1,
}
REQUIRED_PRESENTATION = [cp for cp in range(0xFE80, 0xFEFD) if cp not in ISOLATED_FORMS]
BASE_LETTERS = list(range(0x0621, 0x063B)) + list(range(0x0641, 0x064B))
HARAKAT = list(range(0x064B, 0x0653))

CATEGORY_STYLE = {
    "SANS_SERIF": "sans",
    "SERIF": "serif",
    "DISPLAY": "display",
    "HANDWRITING": "handwriting",
    "MONOSPACE": "sans",
}


logging.getLogger("fontTools").setLevel(logging.ERROR)


def log(msg: str) -> None:
    print(msg, file=sys.stderr, flush=True)


def run(cmd: list[str], cwd: Path | None = None) -> str:
    return subprocess.run(cmd, cwd=cwd, check=True, capture_output=True, text=True).stdout


# --------------------------------------------------------------------------- repo

def ensure_repo(repo: Path) -> None:
    """Blobless, sparse clone of google/fonts with only METADATA.pb files checked out."""
    if not (repo / ".git").exists():
        repo.parent.mkdir(parents=True, exist_ok=True)
        log(f"Cloning {REPO_URL} (sparse, blobless)…")
        run(["git", "clone", "--depth", "1", "--filter=blob:none", "--no-checkout", REPO_URL, str(repo)])
        run(["git", "sparse-checkout", "set", "--no-cone", "/ofl/*/METADATA.pb"], cwd=repo)
        run(["git", "checkout", "-q", "main"], cwd=repo)
    else:
        log("Updating google/fonts…")
        run(["git", "fetch", "-q", "--depth", "1", "origin", "main"], cwd=repo)
        run(["git", "reset", "-q", "--hard", "FETCH_HEAD"], cwd=repo)


def add_sparse_dirs(repo: Path, dirs: list[str]) -> None:
    patterns = ["/ofl/*/METADATA.pb"] + [f"/ofl/{d}/" for d in dirs]
    run(["git", "sparse-checkout", "set", "--no-cone", *patterns], cwd=repo)


def tree_hash(repo: Path, folder: str) -> str:
    return run(["git", "rev-parse", f"HEAD:ofl/{folder}"], cwd=repo).strip()


# ----------------------------------------------------------------------- metadata

def parse_textproto(text: str) -> dict:
    """Minimal protobuf text-format parser, enough for METADATA.pb."""
    text = re.sub(r"(?m)^\s*#.*$", "", text)
    tokens = re.findall(r'"(?:\\.|[^"\\])*"|[{}]|[A-Za-z_][\w]*\s*:|[A-Za-z_][\w]*|-?\d+(?:\.\d+)?(?:[eE][-+]?\d+)?', text)
    pos = 0

    def value(tok: str):
        if tok.startswith('"'):
            return tok[1:-1].replace('\\"', '"').replace("\\\\", "\\")
        try:
            return float(tok) if "." in tok else int(tok)
        except ValueError:
            return tok

    def block() -> dict:
        nonlocal pos
        out: dict = {}
        while pos < len(tokens):
            tok = tokens[pos]
            if tok == "}":
                pos += 1
                return out
            key = tok.rstrip(":").strip()
            pos += 1
            if pos >= len(tokens):
                break
            if tokens[pos] == "{":
                pos += 1
                val = block()
            else:
                val = value(tokens[pos])
                pos += 1
            out.setdefault(key, []).append(val)
        return out

    return block()


def first(d: dict, key: str, default=None):
    return d.get(key, [default])[0]


def slugify(name: str) -> str:
    return re.sub(r"[^a-z0-9]+", "-", name.lower()).strip("-")


def family_weights(meta: dict) -> tuple[dict[int, str], dict | None]:
    """Map weight -> source filename (normal style only) and wght axis if variable."""
    files: dict[int, str] = {}
    for f in meta.get("fonts", []):
        if first(f, "style") == "normal":
            files.setdefault(int(first(f, "weight")), first(f, "filename"))
    axes = {first(a, "tag"): (first(a, "min_value"), first(a, "max_value")) for a in meta.get("axes", [])}
    if "wght" in axes and len(set(files.values())) == 1:
        lo, hi = axes["wght"]
        src = next(iter(files.values()))
        weights = [w for w in range(100, 1000, 100) if lo <= w <= hi]
        return {w: src for w in weights}, axes
    return files, axes or None


# -------------------------------------------------------------------- font output

def static_instance(path: Path, weight: int, axes: dict | None) -> TTFont:
    font = TTFont(path)
    if "fvar" in font:
        limits = {}
        for axis in font["fvar"].axes:
            limits[axis.axisTag] = weight if axis.axisTag == "wght" else axis.defaultValue
        font = instancer.instantiateVariableFont(font, limits, updateFontNames=False)
    return font


def to_bytes(font: TTFont, flavor: str | None) -> bytes:
    font.flavor = flavor
    buf = io.BytesIO()
    font.save(buf)
    font.flavor = None
    return buf.getvalue()


def make_subset(font: TTFont, unicodes: str) -> TTFont:
    options = ftsubset.Options()
    options.layout_features = ["*"]
    options.name_IDs = ["*"]
    options.notdef_outline = True
    options.hinting = False
    options.desubroutinize = True
    sub = TTFont(io.BytesIO(to_bytes(font, None)))
    subsetter = ftsubset.Subsetter(options)
    subsetter.populate(unicodes=ftsubset.parse_unicodes(unicodes))
    subsetter.subset(sub)
    return sub


def write_hashed(dest_dir: Path, stem: str, ext: str, data: bytes) -> str:
    digest = hashlib.sha1(data).hexdigest()[:10]
    name = f"{stem}.{digest}.{ext}"
    (dest_dir / name).write_bytes(data)
    return name


def has_arabic_marks(font: TTFont) -> bool:
    """Harakat present and positioned through GPOS mark attachment for Arabic."""
    cmap = font.getBestCmap() or {}
    if not all(cp in cmap for cp in HARAKAT):
        return False
    if "GPOS" not in font or not font["GPOS"].table.FeatureList:
        return False
    gpos = font["GPOS"].table
    scripts = {s.ScriptTag for s in gpos.ScriptList.ScriptRecord} if gpos.ScriptList else set()
    features = {f.FeatureTag for f in gpos.FeatureList.FeatureRecord}
    return "arab" in scripts and "mark" in features


def coverage(font: TTFont, codepoints: list[int]) -> float:
    cmap = font.getBestCmap() or {}
    return sum(1 for cp in codepoints if cp in cmap) / len(codepoints)


# ------------------------------------------------------------------------- build

def build_family(repo: Path, folder: str, meta: dict, out: Path, kind: str) -> dict:
    name = first(meta, "name")
    slug = slugify(name)
    weights, axes = family_weights(meta)
    src_dir = repo / "ofl" / folder
    dest = out / "files" / slug
    if dest.exists():
        shutil.rmtree(dest)
    dest.mkdir(parents=True)

    files: dict[str, dict] = {}
    sizes: dict[str, int] = {}
    checks_font = None
    for weight, filename in sorted(weights.items()):
        font = static_instance(src_dir / filename, weight, axes)
        stem = f"{slug}-{weight}"
        entry: dict[str, str] = {}
        if kind == "arabic":
            entry["ttf"] = write_hashed(dest, stem, "ttf", to_bytes(font, None))
            entry["woff2"] = write_hashed(dest, stem, "woff2", to_bytes(font, "woff2"))
            preview = to_bytes(make_subset(font, ARABIC_PREVIEW_UNICODES), "woff2")
            entry["preview"] = write_hashed(dest, stem + "-arabic", "woff2", preview)
            sizes[str(weight)] = len(preview)
        else:
            preview = to_bytes(make_subset(font, LATIN_PREVIEW_UNICODES), "woff2")
            entry["preview"] = write_hashed(dest, stem + "-latin", "woff2", preview)
            sizes[str(weight)] = len(preview)
        files[str(weight)] = entry
        if checks_font is None or weight == 400:
            checks_font = font

    licence = next((p for p in ("OFL.txt", "LICENSE.txt") if (src_dir / p).exists()), None)
    if licence:
        shutil.copy(src_dir / licence, dest / "OFL.txt")

    result = {
        "slug": slug,
        "name": name,
        "kind": kind,
        "folder": folder,
        "designer": first(meta, "designer", ""),
        "license": first(meta, "license", "OFL"),
        "category": first(meta, "category", "SANS_SERIF"),
        "style": CATEGORY_STYLE.get(first(meta, "category", "SANS_SERIF"), "sans"),
        "date_added": first(meta, "date_added"),
        "weights": sorted(int(w) for w in files),
        "variable": bool(axes and "wght" in axes),
        "files": files,
        "preview_bytes": sizes,
        "has_license_file": bool(licence),
        "google_url": "https://fonts.google.com/specimen/" + name.replace(" ", "+"),
    }
    if kind == "arabic" and checks_font is not None:
        result["presentation_forms"] = round(coverage(checks_font, REQUIRED_PRESENTATION), 3)
        result["arabic_coverage"] = round(coverage(checks_font, BASE_LETTERS), 3)
        result["diacritics"] = has_arabic_marks(checks_font)
    return result


def main() -> int:
    ap = argparse.ArgumentParser()
    ap.add_argument("--out", required=True, type=Path)
    ap.add_argument("--repo", type=Path, help="google/fonts checkout (default: <out>/source/google-fonts)")
    ap.add_argument("--pairings", type=Path, help="pairings.json listing Latin families to include")
    ap.add_argument("--only", help="comma-separated folder names to (re)build")
    ap.add_argument("--jobs", type=int, default=max(1, (os.cpu_count() or 2) - 1))
    ap.add_argument("--no-update", action="store_true", help="do not fetch google/fonts")
    args = ap.parse_args()

    out: Path = args.out.resolve()
    repo: Path = (args.repo or out / "source" / "google-fonts").resolve()
    out.mkdir(parents=True, exist_ok=True)

    if not args.no_update or not (repo / ".git").exists():
        ensure_repo(repo)

    metas: dict[str, dict] = {}
    for path in sorted((repo / "ofl").glob("*/METADATA.pb")):
        metas[path.parent.name] = parse_textproto(path.read_text(encoding="utf-8"))

    arabic = [f for f, m in metas.items() if "arabic" in m.get("subsets", [])]

    latin_names: set[str] = set()
    if args.pairings and args.pairings.exists():
        for pair in json.loads(args.pairings.read_text(encoding="utf-8")):
            latin_names.add(pair["latin"])
    by_name = {first(m, "name"): f for f, m in metas.items()}
    latin = [by_name[n] for n in sorted(latin_names) if n in by_name and by_name[n] not in arabic]
    missing = sorted(n for n in latin_names if n not in by_name)
    if missing:
        log(f"Warning: Latin families not found in google/fonts: {', '.join(missing)}")

    add_sparse_dirs(repo, arabic + latin)

    cache_path = out / "build-cache.json"
    cache = json.loads(cache_path.read_text()) if cache_path.exists() else {}
    only = set(args.only.split(",")) if args.only else None

    results = []
    todo: list[tuple[str, str, str]] = []
    for kind, folders in (("arabic", arabic), ("latin", latin)):
        for folder in folders:
            key = f"{BUILD_VERSION}:{tree_hash(repo, folder)}"
            cached = cache.get(folder)
            built = cached and (out / "files" / cached["entry"]["slug"]).exists()
            if only is not None and folder not in only:
                if built:
                    results.append(cached["entry"])
                continue
            if only is None and built and cached["key"] == key:
                results.append(cached["entry"])
                continue
            todo.append((kind, folder, key))

    # Build in parallel; save the cache after every family so an interrupted
    # run resumes where it stopped.
    with ProcessPoolExecutor(max_workers=args.jobs) as pool:
        futures = {
            pool.submit(build_family, repo, folder, metas[folder], out, kind): (folder, key)
            for kind, folder, key in todo
        }
        for done, future in enumerate(as_completed(futures), 1):
            folder, key = futures[future]
            try:
                entry = future.result()
            except Exception as exc:  # keep going; report at the end
                log(f"[{done}/{len(todo)}] {folder} failed: {exc}")
                continue
            log(f"[{done}/{len(todo)}] {folder}")
            cache[folder] = {"key": key, "entry": entry}
            results.append(entry)
            cache_path.write_text(json.dumps(cache, ensure_ascii=False, indent=1))

    # Remove output folders for families that no longer exist.
    keep = {r["slug"] for r in results} | ({v["entry"]["slug"] for v in cache.values()} if only else set())
    for d in (out / "files").iterdir() if (out / "files").exists() else []:
        if d.is_dir() and d.name not in keep:
            shutil.rmtree(d)
    cache = {k: v for k, v in cache.items() if v["entry"]["slug"] in keep}

    cache_path.write_text(json.dumps(cache, ensure_ascii=False, indent=1))
    manifest = {
        "source_commit": run(["git", "rev-parse", "HEAD"], cwd=repo).strip(),
        "fonts": sorted(results, key=lambda r: (r["kind"], r["slug"])),
    }
    (out / "fonts.json").write_text(json.dumps(manifest, ensure_ascii=False, indent=1))
    log(f"Wrote {len(results)} families to {out / 'fonts.json'}")
    return 0


if __name__ == "__main__":
    sys.exit(main())
