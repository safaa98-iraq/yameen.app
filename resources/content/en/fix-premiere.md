---
title: 'Fix Arabic text in Premiere Pro: connected letters, right direction | Yameen'
description: Type Arabic and copy it ready to paste into Premiere Pro. Yameen connects the letters and orders them so they don't appear disconnected or reversed, with paste steps and font tips.
h1: Fix Arabic text for Premiere Pro
intro: Type your text here, then copy the result and paste it into Premiere Pro. Letters appear connected and in the right order.
paste_steps:
- Pick the Type tool and click on the Program Monitor.
- Paste the fixed text.
- Choose the font in the Essential Graphics panel.
- Set alignment to right.
paste_note: 'TODO(verify): current Arabic support in Premiere Pro text tools.'
faq:
- q: Why do Arabic letters appear disconnected in Premiere Pro?
  a: Because the app doesn't apply Arabic letter joining or right-to-left ordering in its default setup, so each letter is shown in its standalone form and in reverse order.
- q: Do I need a specific font?
  a: Use a font that includes connected Arabic letter forms. Fonts marked “Works in apps” on Yameen’s fonts page include them.
- q: Can I edit the text after pasting?
  a: You can change size, colour and font. Editing the letters inside the app breaks the ordering, so edit in Yameen and copy again.
- q: Why does the text look reversed when I paste it in a browser or WhatsApp?
  a: It is prepared for apps without Arabic support, like Premiere Pro. Where Arabic is supported, paste the normal text.
- q: Is my text sent to a server?
  a: No. Fixing happens entirely in your browser and the text isn't stored anywhere.
---

## Why do Arabic letters appear disconnected in Premiere Pro?

Computers store Arabic in logical order: first letter, second letter, third letter, without saying which shape each letter takes. Apps that support Arabic do two things when they display it: they choose each letter's shape for its position in the word (start, middle, end or standalone), then lay the line out from right to left.

Premiere Pro is a video editor that many designers use for titles, captions and lower thirds, but in its default setup it may not do either step for Arabic. Each letter then appears separated from its neighbour and the word runs backwards from left to right, so «مرحبا» becomes scattered, reversed letters.

## How Yameen fixes the text

Yameen converts the text into a form Premiere Pro displays as is, without needing Arabic support:

1. **Connect letters:** each letter is replaced with its connected form for its position in the word, and lam is merged with alef in «لا», «لأ», «لإ» and «لآ».
2. **Order the line:** letters are ordered visually from left to right so the app shows them in the right order, while English words and numbers keep their normal direction.
3. **Remove diacritics:** diacritics are removed by default, because most apps without Arabic support can't place them correctly. You can turn this off in the advanced options.

All of this happens in your browser; the text is never sent to a server.

## Using it with Premiere Pro

Type your text in the box at the top, choose the font you will use, then press “Copy for your app”. Follow the paste steps shown under the tool. Yameen processes each line on its own, so type multi-line text with the same line breaks you want in the design.

- For long subtitle files (SRT), use Yameen's file tool when available instead of copying each line.
- Save a Text Style after choosing the font so every title uses the same settings.

## Choosing the right font

An Arabic font is not enough: it must include connected letter forms (Arabic Presentation Forms), because the fixed text uses those forms directly. If Premiere Pro shows boxes or question marks instead of letters, the chosen font doesn't include them.

Yameen’s fonts page lists dozens of free Arabic fonts, and every font that includes the needed forms is marked “Works in apps”. Download the font from its official source, install it, then pick the same name in Premiere Pro.

## Common problems

- **The text looks reversed in Premiere Pro:** the app may support Arabic in your setup, so paste the original text instead of the fixed one.
- **Boxes instead of letters:** choose a font marked “Works in apps”.
- **Lines in the wrong order:** don't break lines inside the app; add line breaks in Yameen and copy again.
- **Diacritics in the wrong place:** keep “Remove diacritics” on.

## When you don't need Yameen

If Arabic already shows correctly when typed directly in your version, you don't need the fix; fixing it then would make it look reversed. Try one word first. TODO(verify): behaviour of the current version.
