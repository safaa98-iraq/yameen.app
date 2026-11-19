---
title: 'Fix Arabic text copied from PDF: restore letters and order | Yameen'
description: Repair Arabic text that comes out reversed or with odd characters after copying from a PDF. Yameen restores base letters and reverses the order when needed.
h1: Fix Arabic text copied from PDF
intro: Paste the text as you copied it from the PDF. Yameen restores the original letters and reverses the order when needed.
faq:
- q: Why do I see words like «يف» and «عىل» instead of «في» and «على»?
  a: Some PDFs exported from Word with certain fonts store connected letter pairs in reverse order. Yameen detects this and repairs the words with an Arabic dictionary, showing how many it fixed. Check the result; you can undo the repair with one button.
- q: Why is text copied from a PDF reversed?
  a: Some PDFs store letters in the order they appear on screen rather than the order they were written, so copied text comes out reversed.
- q: Why doesn’t search work on the copied text?
  a: The letters arrive as special connected forms instead of base letters. The fix converts them back so search works.
- q: What if the text isn’t reversed automatically?
  a: Press “Reverse order”. The button is always there, and pressing it again undoes it.
- q: Does it work with text copied from Photoshop or other apps?
  a: Yes. Text copied from an app that shows Arabic disconnected is handled the same way and restored.
---

## What happens when you copy from a PDF?

A PDF is designed to show the page as it looks, not to keep text in logical order. Many PDF producers therefore store Arabic letters in their displayed shapes, the connected form of each letter, and sometimes in their on-screen order from left to right.

When you copy that text into an editor or browser, two problems appear. First, the letters arrive as special forms that search, spell check and machine translation don't recognise, even if they look fine. Second, the line may arrive reversed, with words starting from their last letter.

## How the fix works

1. **Restore base letters:** special letter forms become the basic Arabic letters, and lam-alef ligatures are split into two letters.
2. **Check the order:** Yameen estimates whether the text is reversed, using the shape of the first and last letter of each word and patterns such as the article «ال» and taa marbuta.
3. **Reverse when needed:** if the estimate is clear, the line is reversed automatically while numbers and English words keep their direction. If not, a message tells you and leaves the decision to you.

Each line is processed on its own, in your browser, without sending the text to a server.

## When to press “Reverse order”

Very short text, or text mixing Arabic with other scripts, may not give enough clues about its direction. In that case a notice says the direction is unclear. Read the result: if the words look backwards, press “Reverse order”; if it reversed by mistake, press “Undo reverse”.

## Tips for better results

- Copy a whole paragraph rather than one word; longer text gives a more accurate estimate.
- If the file has columns, copy each column on its own, because copying across columns mixes lines.
- After fixing, use the text cleaner to remove diacritics or unify digits.
- A scanned PDF (an image) has no copyable text at all and needs an OCR tool.

## Examples

- Copied text that looks like «ﻢﻜﻴﻠﻋ مﻼﺴﻟا» returns to «السلام عليكم» automatically, because the letter shapes reveal it is reversed.
- Text that looks fine but search can't find, such as «ﻣﺮﺣﺒﺎ», returns to «مرحبا» in base letters, so search works.
- Text copied from an app that shows Arabic disconnected, such as «ﻥﻱﻡﻱ ﻱﻑ ﻡﻙﺏ», is detected as reversed because every letter is standalone, and returns to «بكم في يمين».
- A line with a number or an English word, such as «2026 ﻡﺎﻋ», returns to «عام 2026» and the number stays as it is.

## Using the text after fixing

After fixing, the text is normal text: paste it into an editor, email or browser, use it for search and translation, or store it in a database. To paste it into a design app without Arabic support, open the fixer, where the same text is ready in the box, and click your app.

## How this differs from the fixer

The fixer turns normal text into a form apps without Arabic support understand. PDF fix does the opposite: it takes text that arrived in that form and turns it back into normal, searchable, editable text.
