---
title: 'Fix Arabic text in Photoshop: connected letters, right direction | Yameen'
description: Type Arabic and copy it ready to paste into Photoshop. Yameen connects the letters and orders them so they don't appear disconnected or reversed, with paste steps and font tips.
h1: Fix Arabic text for Photoshop
intro: Type your text here, then copy the result and paste it into Photoshop. Letters appear connected and in the right order.
paste_steps:
- Pick the Type tool (T) and click on the canvas.
- Paste with Ctrl + V (Cmd + V on Mac).
- Choose one of the Arabic fonts listed on Yameen.
- Set paragraph alignment to right in the Paragraph panel.
paste_note: 'If you enabled the Middle Eastern text engine in the Type preferences, paste the original text instead. TODO(verify): option name and location in the current version.'
faq:
- q: Why do Arabic letters appear disconnected in Photoshop?
  a: Because the app doesn't apply Arabic letter joining or right-to-left ordering in its default setup, so each letter is shown in its standalone form and in reverse order.
- q: Do I need a specific font?
  a: Use a font that includes connected Arabic letter forms. Fonts marked “Works in apps” on Yameen’s fonts page include them.
- q: Can I edit the text after pasting?
  a: You can change size, colour and font. Editing the letters inside the app breaks the ordering, so edit in Yameen and copy again.
- q: Why does the text look reversed when I paste it in a browser or WhatsApp?
  a: It is prepared for apps without Arabic support, like Photoshop. Where Arabic is supported, paste the normal text.
- q: Is my text sent to a server?
  a: No. Fixing happens entirely in your browser and the text isn't stored anywhere.
---

## Why do Arabic letters appear disconnected in Photoshop?

Computers store Arabic in logical order: first letter, second letter, third letter, without saying which shape each letter takes. Apps that support Arabic do two things when they display it: they choose each letter's shape for its position in the word (start, middle, end or standalone), then lay the line out from right to left.

Photoshop is a image editor that many designers use for posters, social media posts and ads, but in its default setup it may not do either step for Arabic. Each letter then appears separated from its neighbour and the word runs backwards from left to right, so «مرحبا» becomes scattered, reversed letters.

## How Yameen fixes the text

Yameen converts the text into a form Photoshop displays as is, without needing Arabic support:

1. **Connect letters:** each letter is replaced with its connected form for its position in the word, and lam is merged with alef in «لا», «لأ», «لإ» and «لآ».
2. **Order the line:** letters are ordered visually from left to right so the app shows them in the right order, while English words and numbers keep their normal direction.
3. **Remove diacritics:** diacritics are removed by default, because most apps without Arabic support can't place them correctly. You can turn this off in the advanced options.

All of this happens in your browser; the text is never sent to a server.

## Using it with Photoshop

Type your text in the box at the top, choose the font you will use, then press “Copy for your app”. Follow the paste steps shown under the tool. Yameen processes each line on its own, so type multi-line text with the same line breaks you want in the design.

- Use a paragraph text box (drag with the Type tool) for long text, and add line breaks in Yameen rather than in Photoshop.
- Changing the size or colour needs no new fix, but editing the letters means copying the text from Yameen again.

## Choosing the right font

An Arabic font is not enough: it must include connected letter forms (Arabic Presentation Forms), because the fixed text uses those forms directly. If Photoshop shows boxes or question marks instead of letters, the chosen font doesn't include them.

Yameen’s fonts page lists dozens of free Arabic fonts, and every font that includes the needed forms is marked “Works in apps”. Download the font from its official source, install it, then pick the same name in Photoshop.

## Common problems

- **The text looks reversed in Photoshop:** the app may support Arabic in your setup, so paste the original text instead of the fixed one.
- **Boxes instead of letters:** choose a font marked “Works in apps”.
- **Lines in the wrong order:** don't break lines inside the app; add line breaks in Yameen and copy again.
- **Diacritics in the wrong place:** keep “Remove diacritics” on.

## When you don't need Yameen

Photoshop includes a text engine that supports Arabic, but it is not enabled by default in every version or setup. If it is enabled for you, type directly in the app. On shared files or machines where you can't change settings, text fixed by Yameen works without any setup. TODO(verify): versions that enable it by default.
