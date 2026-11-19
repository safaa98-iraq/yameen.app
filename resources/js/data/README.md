# ar-words.txt

Arabic words ranked by frequency (one per line, most frequent first), used by the
PDF fix and the garbled-words tool to repair letter pairs swapped by some PDF exporters.

Merged from (each word keeps its better rank):

- **FrequencyWords** by Hermit Dave (OpenSubtitles 2018, Arabic),
  https://github.com/hermitdave/FrequencyWords, licensed
  [CC BY-SA 4.0](https://creativecommons.org/licenses/by-sa/4.0/).
- **wikipedia-word-frequency** by Ilya Semenov (arwiki-2022-08-29),
  https://github.com/IlyaSemenov/wikipedia-word-frequency, MIT licence; counts
  from Arabic Wikipedia text (CC BY-SA).

This file is a derivative work (filtered, merged, article forms removed) and is
distributed under CC BY-SA 4.0.

Rebuild with `python3 scripts/content/build_dictionary.py ar_full.txt arwiki-2022-08-29.txt`.
