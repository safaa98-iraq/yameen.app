---
title: Fix Arabic text for Photoshop and apps without Arabic support | Yameen
description: Type Arabic and click your app to copy it ready to paste. Yameen connects and orders the letters so they show correctly in design, video and game tools.
h1: Fix Arabic text
intro: Type your text, then click the app you will paste it into. We copy it ready to paste.
faq:
- q: Which apps does it work with?
  a: Any app that shows Arabic letters disconnected or reversed, such as some design, video, game and 3D tools.
- q: Is my text sent to a server?
  a: No. Fixing happens entirely in your browser and the text is not stored.
- q: Why are diacritics removed?
  a: Connected letter forms don’t position diacritics, so most apps show them in the wrong place. You can turn this off on the app pages.
- q: Why does the text look reversed when I paste it in WhatsApp or a browser?
  a: It is prepared for apps without Arabic support. Where Arabic is supported, paste the normal text.
- q: What if I paste text that is already fixed?
  a: That’s fine. Yameen detects fixed text, or text copied from an app, and restores it before fixing.
---

## Why do letters appear disconnected?

Computers store Arabic in logical order, first letter, second letter, third letter, without saying which shape each letter takes. Apps that support Arabic choose each letter's shape for its position in the word, so a beh at the start of a word differs from one in the middle or at the end, then display the line from right to left.

Some design, video and game tools don't do these two steps, or don't do them in their default setup. Each letter then shows in its standalone form and the word runs backwards from left to right. This is common with shared files, ready-made templates and machines whose settings you can't change.

## What the fixer does

1. **Connect letters:** each letter is replaced with the form for its position in the word, and lam is merged with alef in «لا» and similar pairs.
2. **Order the line:** letters are ordered visually so the app shows them correctly, while English words and numbers stay left to right.
3. **Remove diacritics:** removed by default, because most apps can't place them correctly.

Each line is processed on its own and empty lines are kept. If you paste text that is already fixed, or copied from an app that shows Arabic disconnected, it is restored first and then fixed, so it is never processed twice.

## How to use it

Type or paste your text, then click the app you will paste it into. The text is copied immediately, with paste steps for that app and a preview of how it will look. If your app isn't listed, choose “Another app”.

After pasting, choose an Arabic font that includes connected letter forms in your app. All fonts shown on the home page include them, and you can download each one for free from its page.

## Choosing the right font

The fixed text uses connected letter forms directly, so the font you choose in your app must include them. Many modern Arabic fonts don't, because they rely on the app to join letters, and they show boxes or question marks instead.

On Yameen's fonts page, every font that includes the needed forms is marked “Works in apps”. Pick one, download it from its official source, install it, then choose the same name in your app. All fonts shown are free and licensed under the OFL, so you can use them in commercial projects.

## Examples of what the fixer handles

- **A plain sentence:** «مرحباً بكم في يمين» shows connected and in the right order, with tanween removed.
- **Lam-alef:** in «لا إله إلا الله», «لا» and «لإ» appear as single merged shapes, as in handwriting.
- **Arabic with English and numbers:** in «أستخدم Photoshop منذ 2019», the English word and the number stay readable left to right in the right place in the sentence.
- **Punctuation and brackets:** the question mark sits at the end of the sentence and brackets face the right way.
- **Several lines:** each line is processed on its own, so a title stays on one line and a description on another.
- **Persian and Kurdish letters:** such as پ, چ and گ are connected the same way.

## When you don't need it

If your app already shows Arabic correctly when you type it directly, don't use the fixed text: it would look reversed. That applies to browsers, office apps and chat apps, and to design apps with Middle Eastern language options enabled. Try one word first if you aren't sure.

## Privacy

The fixer runs entirely in your browser using open-source libraries. The text is never sent to a server or stored; it only stays in your browser during your session so you can switch tools without typing it again.
