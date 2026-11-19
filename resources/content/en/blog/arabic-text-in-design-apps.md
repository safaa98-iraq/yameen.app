---
title: Why Arabic letters look disconnected in design apps, and how to fix it | Yameen
description: A simple explanation of why Arabic shows disconnected and reversed in Photoshop, Unity and others, and three ways to fix it.
h1: Why do Arabic letters look disconnected in design apps?
intro: A simple explanation of the problem and three ways to fix it.
date: '2026-11-19'
---

## The problem in one sentence

Computers store Arabic letters separately and in writing order, and leave it to the app to join them and display them from the right. An app that doesn't do this shows the letters as stored: separated and from the left.

## What an app needs to display Arabic

Two steps. The first is shaping: choosing each letter's form for its position, so the ain in «عمل» differs from the one in «سعر» and in «سمع». The second is direction: laying the line out right to left while keeping numbers and English words left to right inside it.

Browsers and office apps always do both. Some design, video and game tools do too, some only after you enable a setting, and some never do. TODO(verify): specific examples by version.

## Fix 1: enable Arabic support in the app

If your app has a setting for Middle Eastern languages, enable it and type directly. This is the best option, because the text stays editable in the app. Look in the Type or Text preferences for an option mentioning the Middle East or Arabic.

## Fix 2: prepare the text before pasting

If the setting isn't available, or you work on a machine or template whose settings you can't change, prepare the text before pasting. That is what Yameen does: it picks each letter's connected form and orders the line visually, so the app shows it correctly without any Arabic support. The downside is that editing the letters inside the app breaks the ordering, so edit in Yameen and copy again.

## Fix 3: a library inside the app

For games and apps that show dynamic text, such as player names or text from the internet, preparing text in advance isn't enough. The answer is a shaping library running inside the game that prepares text at display time.

## One more thing: the font

Prepared text uses connected letter forms directly, so the font must include them. If you see boxes instead of letters, change the font. Yameen's fonts page lists free fonts marked “Works in apps”.
