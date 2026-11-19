#!/usr/bin/env python3
"""
Build resources/js/data/ar-words.txt: Arabic words ranked by frequency, used by
the PDF fix to repair swapped letter pairs.

Source: FrequencyWords by Hermit Dave (OpenSubtitles 2018, Arabic, ar_full.txt),
https://github.com/hermitdave/FrequencyWords, licensed CC BY-SA 4.0. The
generated list is a derivative and is distributed under the same licence.

Usage: python3 scripts/content/build_dictionary.py path/to/ar_full.txt
"""
import re
import sys
from pathlib import Path

LIMIT = 320_000
OUT = Path(__file__).resolve().parents[2] / "resources" / "js" / "data" / "ar-words.txt"
LETTERS = re.compile(r"^[ء-غف-ي]{2,20}$")
# Spellings that cannot occur in correct Arabic (they come from corrupted text).
IMPOSSIBLE = re.compile(r"ى.|ة.|^ا[أإآ]|^ا[جحخ]ل")
CORRUPTED_WORDS = {"يف", "هلا", "هبا", "هلم", "هبم", "هلذا", "هبذا", "هلذه", "هبذه"}
PREFIXES = ["وال", "فال", "بال", "كال", "لل", "ال", "و", "ف", "ب", "ل", "ك"]

words, seen = [], set()
for line in open(sys.argv[1], encoding="utf-8"):
    parts = line.split()
    if not parts:
        continue
    w = parts[0]
    if LETTERS.match(w) and not IMPOSSIBLE.search(w) and w not in CORRUPTED_WORDS and w not in seen:
        seen.add(w)
        words.append(w)
        if len(words) >= LIMIT:
            break

# Drop words that are a prefix plus another listed word: the JS strips prefixes itself.
present = set(words)
def derivable(w):
    for p in PREFIXES:
        if len(w) >= len(p) + 3 and w.startswith(p):
            rest = w[len(p):]
            if rest in present or ("ال" + rest) in present:
                return True
    return False

kept = [w for w in words if not derivable(w)]
OUT.write_text("\n".join(kept) + "\n", encoding="utf-8")
print(f"{len(kept)} words -> {OUT}")
