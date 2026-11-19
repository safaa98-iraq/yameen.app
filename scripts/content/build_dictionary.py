#!/usr/bin/env python3
"""
Build resources/js/data/ar-words.txt: Arabic words ranked by frequency, used by
the PDF fix to repair swapped letter pairs.

Sources (merged; each word takes its better rank of the two):
- FrequencyWords by Hermit Dave (OpenSubtitles 2018, Arabic, ar_full.txt),
  https://github.com/hermitdave/FrequencyWords, CC BY-SA 4.0. Everyday language.
- wikipedia-word-frequency by Ilya Semenov (arwiki-2022-08-29.txt),
  https://github.com/IlyaSemenov/wikipedia-word-frequency, MIT; counts from
  Arabic Wikipedia text (CC BY-SA). Formal and technical language.
The generated list is a derivative and is distributed under CC BY-SA 4.0.

Usage: python3 scripts/content/build_dictionary.py ar_full.txt:4 arwiki-2022-08-29.txt
"""
import re
import sys
from pathlib import Path

LIMIT = 300_000
OUT = Path(__file__).resolve().parents[2] / "resources" / "js" / "data" / "ar-words.txt"
LETTERS = re.compile(r"^[ء-غف-ي]{2,20}$")
# Spellings that cannot occur in correct Arabic (they come from corrupted text).
IMPOSSIBLE = re.compile(r"ى.|ة.|^ا[أإآ]|^ا[جحخ]ل")
CORRUPTED_WORDS = {"يف", "هلا", "هبا", "هلم", "هبم", "هلذا", "هبذا", "هلذه", "هبذه"}
PREFIXES = ["وال", "فال", "بال", "كال", "لل", "ال", "و", "ف", "ب", "ل", "ك"]

def ranked(path):
    """Words in file order (most frequent first), filtered."""
    out, seen = [], set()
    for line in open(path, encoding="utf-8"):
        parts = line.split()
        if not parts:
            continue
        w = parts[0]
        if LETTERS.match(w) and not IMPOSSIBLE.search(w) and w not in CORRUPTED_WORDS and w not in seen:
            seen.add(w)
            out.append(w)
    return out

# PDFs are formal documents, so Wikipedia ranks count fully and subtitle ranks
# (everyday speech, more misspellings) are weighted down. Pass files as
# path or path:weight (rank is multiplied by the weight).
best = {}
for arg in sys.argv[1:]:
    path, _, weight = arg.partition(":")
    weight = float(weight or 1)
    for rank, w in enumerate(ranked(path)):
        r = rank * weight
        if r < best.get(w, float("inf")):
            best[w] = r
words = sorted(best, key=lambda w: (best[w], w))[:LIMIT]

# Drop article forms («الفردي») of listed words: the JS strips the article itself.
# One-letter prefixes are kept: «فردي» is not «ف» + «ردي».
ARTICLES = ["وال", "فال", "بال", "كال", "لل", "ال"]
present = set(words)
def derivable(w):
    for p in ARTICLES:
        if len(w) >= len(p) + 3 and w.startswith(p):
            rest = w[len(p):]
            if rest in present or ("ال" + rest) in present:
                return True
    return False

kept = [w for w in words if not derivable(w)]
OUT.write_text("\n".join(kept) + "\n", encoding="utf-8")
print(f"{len(kept)} words -> {OUT}")
