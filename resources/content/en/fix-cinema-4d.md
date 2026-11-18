---
title: Fix Arabic text in Cinema 4D | Yameen
description: Convert Arabic text so it shows connected and in the right direction in Cinema 4D, then copy and paste it directly.
h1: Fix Arabic text for Cinema 4D
intro: Type your text here, then copy the result and paste it into Cinema 4D. Letters appear connected and in the right order.
paste_steps:
- Add a text object (Text Spline or MoText).
- Paste the fixed text into the Text field in the Attribute Manager.
- Choose the font in the Font field.
- Set alignment to right.
paste_note: 'TODO(verify): object and field names in the current version.'
faq:
- q: Why do Arabic letters appear disconnected in Cinema 4D?
  a: Because the app does not apply Arabic letter joining or right-to-left ordering in its default setup, so each letter is shown in its standalone form.
- q: Do I need a specific font?
  a: Use a font that includes Arabic Presentation Forms. Each font page on Yameen shows this.
- q: Can I edit the text after pasting?
  a: Editing inside the app breaks the ordering. Edit the text in Yameen and copy it again.
---

## Why do letters appear disconnected?

Computers store Arabic in logical order, one letter after another, without saying which shape each letter takes. Apps that support Arabic choose each letter's shape (start, middle or end of a word) and lay the line out right to left. When Cinema 4D doesn't, letters show up separated and reversed.

Yameen converts each letter to its connected form and orders the line visually, while keeping English words and numbers left to right.

## Tips

- Type each line the way you want it to appear; every line is processed on its own.
- Keep "Remove diacritics" on, because most apps can't position them correctly.
- If you see boxes instead of letters, the font lacks connected letter forms. Try another font.
