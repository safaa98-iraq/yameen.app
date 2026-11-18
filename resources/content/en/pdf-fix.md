---
title: Fix Arabic text copied from PDF | Yameen
description: Repair Arabic text that comes out reversed or with odd characters after copying from a PDF.
h1: Fix Arabic text copied from PDF
intro: Paste the text as you copied it from the PDF. Yameen restores the original letters and reverses the order when needed.
faq:
- q: Why is text copied from a PDF reversed?
  a: Some PDFs store letters in the order they appear on screen rather than the order they were written, so copied text comes out reversed.
- q: Why doesn’t search work on the copied text?
  a: The letters arrive as special connected forms instead of the base letters. The fix converts them back.
---

## What happens when you copy from a PDF?

Many PDFs store Arabic letters in their displayed shapes, sometimes in visual left-to-right order. Copied text then contains special forms that search and spell check don't recognise, and lines may arrive reversed.

## How the fix works

Yameen converts the special letter forms back to base letters, then checks whether the order looks reversed. When it is confident it reverses the text automatically; otherwise it offers a "Reverse order" button.
