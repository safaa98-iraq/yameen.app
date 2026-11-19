---
title: 'Fix Arabic text in Illustrator: connected letters, right direction | Yameen'
description: Type Arabic and copy it ready to paste into Illustrator. Yameen connects the letters and orders them so they don't appear disconnected or reversed, with paste steps and font tips.
h1: Fix Arabic text for Illustrator
intro: Type your text here, then copy the result and paste it into Illustrator. Letters appear connected and in the right order.
paste_steps:
- Pick the Type tool (T) and click on the artboard.
- Paste the fixed text.
- Choose the font in the Character panel.
- Convert to outlines (Create Outlines) if the file will be opened on another machine.
paste_note: 'If Middle Eastern options are enabled in the Type preferences, paste the original text. TODO(verify): option name in the current version.'
faq:
- q: Why do Arabic letters appear disconnected in Illustrator?
  a: Because the app doesn't apply Arabic letter joining or right-to-left ordering in its default setup, so each letter is shown in its standalone form and in reverse order.
- q: Do I need a specific font?
  a: Use a font that includes connected Arabic letter forms. Fonts marked “Works in apps” on Yameen’s fonts page include them.
- q: Can I edit the text after pasting?
  a: You can change size, colour and font. Editing the letters inside the app breaks the ordering, so edit in Yameen and copy again.
- q: Why does the text look reversed when I paste it in a browser or WhatsApp?
  a: It is prepared for apps without Arabic support, like Illustrator. Where Arabic is supported, paste the normal text.
- q: Is my text sent to a server?
  a: No. Fixing happens entirely in your browser and the text isn't stored anywhere.
---

## Why do Arabic letters appear disconnected in Illustrator?

Computers store Arabic in logical order: first letter, second letter, third letter, without saying which shape each letter takes. Apps that support Arabic do two things when they display it: they choose each letter's shape for its position in the word (start, middle, end or standalone), then lay the line out from right to left.

Illustrator is a vector design app that many designers use for logos, print and brand identities, but in its default setup it may not do either step for Arabic. Each letter then appears separated from its neighbour and the word runs backwards from left to right, so «مرحبا» becomes scattered, reversed letters.

## How Yameen fixes the text

Yameen converts the text into a form Illustrator displays as is, without needing Arabic support:

1. **Connect letters:** each letter is replaced with its connected form for its position in the word, and lam is merged with alef in «لا», «لأ», «لإ» and «لآ».
2. **Order the line:** letters are ordered visually from left to right so the app shows them in the right order, while English words and numbers keep their normal direction.
3. **Remove diacritics:** diacritics are removed by default, because most apps without Arabic support can't place them correctly. You can turn this off in the advanced options.

All of this happens in your browser; the text is never sent to a server.

## Using it with Illustrator

Type your text in the box at the top, choose the font you will use, then press “Copy for your app”. Follow the paste steps shown under the tool. Yameen processes each line on its own, so type multi-line text with the same line breaks you want in the design.

- For logos, convert the text to outlines once approved so it shows correctly anywhere, even without the font installed.
- Tracking separates connected letters in some fonts, so use it carefully with Arabic.

## Choosing the right font

An Arabic font is not enough: it must include connected letter forms (Arabic Presentation Forms), because the fixed text uses those forms directly. If Illustrator shows boxes or question marks instead of letters, the chosen font doesn't include them.

Yameen’s fonts page lists dozens of free Arabic fonts, and every font that includes the needed forms is marked “Works in apps”. Download the font from its official source, install it, then pick the same name in Illustrator.

## Common problems

- **The text looks reversed in Illustrator:** the app may support Arabic in your setup, so paste the original text instead of the fixed one.
- **Boxes instead of letters:** choose a font marked “Works in apps”.
- **Lines in the wrong order:** don't break lines inside the app; add line breaks in Yameen and copy again.
- **Diacritics in the wrong place:** keep “Remove diacritics” on.

## When you don't need Yameen

With the Middle Eastern options enabled, Illustrator supports Arabic directly. Yameen helps with older files or machines whose settings you can't change. TODO(verify): versions concerned.
