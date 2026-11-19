---
title: Fix garbled Arabic words copied from PDF («يف» for «في») | Yameen
description: Paste Arabic text with words like «يف», «عىل» or «اجلماهريية» copied from a PDF. Yameen fixes them automatically and highlights every word it changed.
h1: Fix garbled words
intro: Words like «يف», «عىل» and «كربالء» instead of «في», «على» and «كربلاء»? Paste the text here and we fix and highlight each word.
faq:
- q: Why do these garbled words appear?
  a: Some PDFs exported from Word with certain Arabic fonts store each pair of connected letters in reverse order. When copied, every such pair arrives swapped, so «في» becomes «يف» and «على» becomes «عىل».
- q: Does Yameen change correct words?
  a: Rarely. A correct word only changes if the same kind of swap appears elsewhere in the text, or if it is one of a few words known for this error, such as «يف» and «هلا». Every changed word is highlighted for review.
- q: How is this different from PDF fix?
  a: 'PDF fix handles every copy problem: special letter forms, reversed order and garbled words. This tool focuses on garbled words and highlights each one it fixed so you can review them easily.'
- q: Is my text sent to a server?
  a: No. The word list is downloaded to your browser once, and fixing happens entirely in the browser.
---

## What is the problem?

Text copied from some PDFs arrives with many oddly garbled words: «يف» for «في», «عىل» for «على», «إىل» for «إلى», «كربالء» for «كربلاء», «اجلماهريية» for «الجماهيرية». The words look close to correct, but pairs of letters have swapped places.

This usually happens with files exported from Word or similar apps using certain Arabic fonts. These fonts draw some connected letters as one shape, such as lam with alef, yeh with noon at the end of a word, or sheen with reh. When the file is exported, that single shape is stored as two letters in reverse order.

## How Yameen fixes them

1. **Detect the problem:** it looks for spellings that are impossible in correct Arabic, such as alef maqsura or taa marbuta in the middle of a word.
2. **Try swaps:** for each unknown word it tries swapping adjacent letters and keeps the most frequent word in a dictionary of over two hundred thousand words from Arabic Wikipedia and subtitles.
3. **Learn from the text:** when a pair is swapped repeatedly in the text, such as «ني» for «ين», similar words are fixed too, even known ones like «بني», which becomes «بين».
4. **Clean up debris:** it fixes mirrored brackets, drops lines that only hold a diacritic, and moves back a letter pushed to the end of a line.

## Review the highlighted words

Every word Yameen fixed is highlighted in the result; hover it to see the original. Fixing relies on word frequency, so it may sometimes pick a common word over a rare correct one, especially for names and technical terms. Review highlighted words before using the text.

## Limits

- Lines broken in the middle of a sentence stay as they are in the file.
- A letter that was dropped or moved to another line may not be recoverable.
- Numbers and symbols inside equations may need manual reordering.
