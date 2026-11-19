---
title: 'Arabic text cleaner: remove diacritics and tatweel, unify letters and digits | Yameen'
description: Remove diacritics and tatweel, unify alef and ya forms, convert between Arabic and Western digits, and clean spaces in Arabic text, right in your browser.
h1: Text cleaner
intro: Choose what to clean and the result updates immediately.
faq:
- q: When should I unify alef forms?
  a: When preparing text for search, sorting or databases. Avoid it in published text, because it changes spelling.
- q: Does the cleaner change line order?
  a: No. Lines and empty lines are kept as they are.
- q: Can I clean text before fixing it?
  a: Yes. Your text stays the same when you switch tools, so you can clean it and then open the fixer directly.
- q: What is the difference between Arabic and Western digits?
  a: Western digits are 0 1 2 3; Eastern Arabic digits are ٠ ١ ٢ ٣. Both are used across the Arab world, and designers choose based on audience and font.
---

## Why clean Arabic text?

Arabic text from different sources usually carries details a design or a program doesn't need: partial diacritics, tatweel used to stretch words, different forms of alef and ya, digits in two systems, or extra spaces. These cause problems in search, sorting and text matching, and look untidy in a design.

The cleaner brings the most common cleanups together, and the result updates as you type.

## Options

- **Remove diacritics:** removes fatha, damma, kasra, sukun, shadda, tanween and Quranic marks.
- **Remove tatweel:** removes the kashida character (ـ) used to stretch words.
- **Unify alef:** converts أ, إ, آ and ٱ to ا. Useful for search and sorting.
- **Unify ya:** converts alef maqsura (ى) to ya (ي).
- **Digits:** converts Eastern Arabic (٠١٢) and Persian (۰۱۲) digits to Western digits (012), or Western to Eastern Arabic.
- **Collapse spaces:** turns repeated spaces into one.
- **Trim lines:** removes spaces at the start and end of each line.

## When to use each option

For design, removing tatweel and extra spaces is usually enough, plus removing diacritics if the font or app doesn't render them well. For databases and search, unifying alef, ya and digits helps, because «أحمد» and «احمد» become the same word. In religious and literary text, be careful removing diacritics, because they carry meaning.

## Examples

- «بِسْمِ اللَّهِ» becomes «بسم الله» after removing diacritics.
- «جـــميل» becomes «جميل» after removing tatweel.
- «أحمد إبراهيم آمن» becomes «احمد ابراهيم امن» after unifying alef.
- «على» becomes «علي» after unifying ya, so don't use this option on published text.
- «الرقم ٢٠٢٦» becomes «الرقم 2026» after converting to Western digits.
- «  نص   مع   مسافات  » becomes «نص مع مسافات» after collapsing spaces and trimming.

## Cleaning before fixing for apps

If you will paste the text into an app without Arabic support, clean tatweel and extra spaces first. The fixer connects tatweel to neighbouring letters, but it stretches the word in a way you may not want in a design. Diacritics are removed by the fixer by default, so there's no need to remove them here unless you'll use the text elsewhere.

After cleaning, open the fixer from the menu at the top: the same text will be ready in the box.

## Where does the text go?

The cleaner runs entirely in your browser and never sends the text to a server. The text only stays in your browser during your session, so you can move to the fixer or the fonts page without typing it again.

## Tips before you start

Check the result before using it, especially after unifying ya and alef, because some words change meaning, such as «على» (on) and «علي» (a name). For long text, try the options on a short paragraph first, then paste the whole text. Remember that the text you clean here is the same text shown in the fixer and on the fonts page, so changes here carry over there. Common problems these options solve include hamza written on alef in some places and dropped in others, alef maqsura used instead of ya at the end of words or the reverse, and documents with many spaces between words.
