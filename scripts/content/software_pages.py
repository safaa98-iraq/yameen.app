#!/usr/bin/env python3
"""
Generates the DRAFT content of the /fix/{software} pages (Arabic and English).
Shared sections plus software-specific paragraphs. Edit the generated Markdown
directly afterwards; re-running this script overwrites it.
Claims about specific software versions are marked TODO(verify).
"""
import yaml
from pathlib import Path

ROOT = Path(__file__).resolve().parents[2] / "resources" / "content"

APPS = {
 "photoshop": dict(
  name="Photoshop",
  ar=dict(kind="برنامج تصميم الصور", use="البوسترات ومنشورات التواصل الاجتماعي والإعلانات",
   steps=["اختر أداة النص (T) وانقر على مساحة العمل.", "الصق النص بـ Ctrl + V (أو Cmd + V على Mac).", "اختر من قائمة الخطوط خطاً عربياً من الخطوط المعروضة في يمين.", "اضبط محاذاة الفقرة إلى اليمين من لوحة Paragraph."],
   note="إذا فعّلت محرك النصوص الخاص بلغات الشرق الأوسط من إعدادات Type، فالصق النص الأصلي دون تصحيح. TODO(verify): اسم الخيار ومكانه في الإصدار الحالي.",
   tips=["استخدم مربع نص فقرة (اسحب بأداة النص) بدلاً من نص النقطة إذا كان النص طويلاً، ثم أدخل فواصل الأسطر في يمين لا في Photoshop.", "عند تغيير حجم الخط أو لونه لا تحتاج إلى تصحيح جديد، لكن أي تعديل على الحروف نفسها يتطلب نسخ النص من يمين مرة أخرى."],
   noneed="يتضمن Photoshop محرك نصوص يدعم العربية، لكنه غير مفعّل افتراضياً في بعض الإصدارات والإعدادات. إذا كان مفعّلاً عندك فاكتب النص مباشرة في البرنامج. أما إذا كنت تعمل على ملف مشترك أو جهاز لا تملك تغيير إعداداته، فالنص المصحح من يمين يعمل دون أي إعداد. TODO(verify): الإصدارات التي تفعّل المحرك افتراضياً."),
  en=dict(kind="image editor", use="posters, social media posts and ads",
   steps=["Pick the Type tool (T) and click on the canvas.", "Paste with Ctrl + V (Cmd + V on Mac).", "Choose one of the Arabic fonts listed on Yameen.", "Set paragraph alignment to right in the Paragraph panel."],
   note="If you enabled the Middle Eastern text engine in the Type preferences, paste the original text instead. TODO(verify): option name and location in the current version.",
   tips=["Use a paragraph text box (drag with the Type tool) for long text, and add line breaks in Yameen rather than in Photoshop.", "Changing the size or colour needs no new fix, but editing the letters means copying the text from Yameen again."],
   noneed="Photoshop includes a text engine that supports Arabic, but it is not enabled by default in every version or setup. If it is enabled for you, type directly in the app. On shared files or machines where you can't change settings, text fixed by Yameen works without any setup. TODO(verify): versions that enable it by default.")),
 "after-effects": dict(
  name="After Effects",
  ar=dict(kind="برنامج الموشن جرافيك", use="العناوين المتحركة والمقدمات والإعلانات المتحركة",
   steps=["أنشئ طبقة نص جديدة (Layer ثم New ثم Text).", "الصق النص المصحح داخل الطبقة.", "اختر الخط من لوحة Character.", "اضبط المحاذاة إلى اليمين من لوحة Paragraph."],
   note="بعض الإصدارات الحديثة تدعم العربية عند تفعيل محرك نصوص الشرق الأوسط. TODO(verify): الإصدار الذي أضيف فيه الدعم.",
   tips=["الحركات التي تعتمد على ترتيب الحروف (مثل ظهور الحروف واحداً تلو الآخر) ستبدأ من يسار الكلمة لأن النص مرتب بصرياً. اعكس اتجاه الحركة من إعدادات Range Selector إذا أردت أن تبدأ من اليمين.", "إذا استخدمت عبارات (Expressions) تغير النص أثناء العرض فلن يُصحح النص الناتج تلقائياً."],
   noneed="إذا كان محرك نصوص الشرق الأوسط مفعّلاً في إعدادات البرنامج فاكتب النص مباشرة. يمين مفيد عندما تعمل على قالب جاهز أو جهاز لا يمكنك تغيير إعداداته. TODO(verify): اسم الإعداد في الإصدار الحالي."),
  en=dict(kind="motion graphics app", use="animated titles, intros and motion ads",
   steps=["Create a text layer (Layer, New, Text).", "Paste the fixed text into the layer.", "Choose the font in the Character panel.", "Set alignment to right in the Paragraph panel."],
   note="Some recent versions support Arabic when the Middle Eastern text engine is enabled. TODO(verify): the version that added support.",
   tips=["Animations that depend on character order, such as letters appearing one by one, will start from the left of the word because the text is visually ordered. Reverse the direction in the Range Selector if you want it to start from the right.", "Expressions that change the text at render time are not fixed automatically."],
   noneed="If the Middle Eastern text engine is enabled in the preferences, type directly in the app. Yameen helps when you work on a ready-made template or a machine whose settings you can't change. TODO(verify): setting name in the current version.")),
 "premiere": dict(
  name="Premiere Pro",
  ar=dict(kind="برنامج المونتاج", use="العناوين والترجمات والأسماء على الشاشة",
   steps=["اختر أداة النص وانقر على شاشة البرنامج (Program Monitor).", "الصق النص المصحح.", "اختر الخط من لوحة Essential Graphics.", "اضبط المحاذاة إلى اليمين."],
   note="TODO(verify): مدى دعم العربية في أدوات النص الحالية في Premiere Pro.",
   tips=["لملفات الترجمة الطويلة (SRT) استخدم أداة معالجة الملفات في يمين عند توفرها بدلاً من نسخ كل سطر وحده.", "احفظ أنماط النص (Text Styles) بعد اختيار الخط، حتى تطبق الإعداد نفسه على كل العناوين."],
   noneed="إذا ظهر النص العربي سليماً عند كتابته مباشرة في نسختك من البرنامج فلا تحتاج إلى التصحيح، وقد يسبب التصحيح عندها ظهور النص معكوساً. جرّب كلمة واحدة أولاً. TODO(verify): سلوك الإصدار الحالي."),
  en=dict(kind="video editor", use="titles, captions and lower thirds",
   steps=["Pick the Type tool and click on the Program Monitor.", "Paste the fixed text.", "Choose the font in the Essential Graphics panel.", "Set alignment to right."],
   note="TODO(verify): current Arabic support in Premiere Pro text tools.",
   tips=["For long subtitle files (SRT), use Yameen's file tool when available instead of copying each line.", "Save a Text Style after choosing the font so every title uses the same settings."],
   noneed="If Arabic already shows correctly when typed directly in your version, you don't need the fix; fixing it then would make it look reversed. Try one word first. TODO(verify): behaviour of the current version.")),
 "illustrator": dict(
  name="Illustrator",
  ar=dict(kind="برنامج التصميم المتجه", use="الشعارات والمطبوعات والهويات البصرية",
   steps=["اختر أداة النص (T) وانقر على لوح الرسم.", "الصق النص المصحح.", "اختر الخط من لوحة Character.", "حوّل النص إلى مسارات (Create Outlines) إذا كنت سترسل الملف لجهاز آخر."],
   note="إذا فعّلت خيارات لغات الشرق الأوسط في إعدادات Type، فالصق النص الأصلي. TODO(verify): اسم الخيار في الإصدار الحالي.",
   tips=["في الشعارات حوّل النص إلى مسارات بعد اعتماده، فيظهر سليماً على أي جهاز ولو لم يكن الخط مثبتاً عليه.", "تباعد الأحرف (Tracking) يفصل الحروف الموصولة في بعض الخطوط، فاستخدمه بحذر مع النص العربي."],
   noneed="عند تفعيل خيارات الشرق الأوسط في Illustrator يدعم البرنامج العربية مباشرة. يمين مفيد عند العمل على ملفات قديمة أو أجهزة لا تملك تغيير إعداداتها. TODO(verify): الإصدارات المعنية."),
  en=dict(kind="vector design app", use="logos, print and brand identities",
   steps=["Pick the Type tool (T) and click on the artboard.", "Paste the fixed text.", "Choose the font in the Character panel.", "Convert to outlines (Create Outlines) if the file will be opened on another machine."],
   note="If Middle Eastern options are enabled in the Type preferences, paste the original text. TODO(verify): option name in the current version.",
   tips=["For logos, convert the text to outlines once approved so it shows correctly anywhere, even without the font installed.", "Tracking separates connected letters in some fonts, so use it carefully with Arabic."],
   noneed="With the Middle Eastern options enabled, Illustrator supports Arabic directly. Yameen helps with older files or machines whose settings you can't change. TODO(verify): versions concerned.")),
 "unity": dict(
  name="Unity",
  ar=dict(kind="محرك الألعاب", use="القوائم والحوارات ونصوص الواجهة في الألعاب",
   steps=["أنشئ Font Asset في TextMeshPro يتضمن نطاق Arabic Presentation Forms-B (FE70-FEFF).", "الصق النص المصحح في حقل Text للمكوّن.", "أبقِ خيار Right-to-Left في TextMeshPro معطلاً، لأن النص مرتب مسبقاً.", "اضبط المحاذاة إلى اليمين."],
   note="النص الثابت فقط يناسب هذه الطريقة. النصوص التي تتغير أثناء اللعب تحتاج إلى تصحيح داخل اللعبة نفسها.",
   tips=["عند إنشاء Font Asset أضف نطاقي الحروف العربية الأساسية (0600-06FF) وأشكال العرض (FE70-FEFF)، وإلا ظهرت مربعات.", "التفاف الأسطر التلقائي في TextMeshPro يقسم السطر من اليسار لأن النص مرتب بصرياً، فاكتب كل سطر وحده في يمين وأدخل الفواصل بنفسك."],
   noneed="إذا كانت لعبتك تحتوي على نصوص متغيرة كثيرة (أسماء لاعبين، نصوص من الخادم) فالأنسب استخدام مكتبة تشكيل داخل اللعبة. يمين يناسب النصوص الثابتة مثل القوائم والعناوين."),
  en=dict(kind="game engine", use="menus, dialogue and UI text in games",
   steps=["Create a TextMeshPro Font Asset that includes Arabic Presentation Forms-B (FE70-FEFF).", "Paste the fixed text into the component’s Text field.", "Keep TextMeshPro’s Right-to-Left option off, because the text is already ordered.", "Set alignment to right."],
   note="This works for static text. Text that changes at runtime has to be fixed inside the game.",
   tips=["When creating the Font Asset, include the basic Arabic range (0600-06FF) and the presentation forms (FE70-FEFF), or you will see boxes.", "TextMeshPro word wrapping breaks lines from the left because the text is visually ordered, so put each line on its own in Yameen."],
   noneed="If your game has a lot of dynamic text (player names, server text), a shaping library inside the game is the better fit. Yameen suits static text such as menus and titles.")),
 "blender": dict(
  name="Blender",
  ar=dict(kind="برنامج التصميم ثلاثي الأبعاد", use="العناوين ثلاثية الأبعاد والشعارات المجسمة",
   steps=["أضف كائن نص (Add ثم Text) وادخل وضع التحرير بالضغط على Tab.", "احذف النص الافتراضي والصق النص المصحح بـ Ctrl + V.", "حمّل ملف الخط من خصائص الكائن (Font) بدلاً من الخط الافتراضي.", "اضبط المحاذاة إلى اليمين من خيارات Paragraph."],
   note="TODO(verify): اختصار اللصق في وضع تحرير النص في الإصدار الحالي.",
   tips=["الخط الافتراضي في Blender لا يحتوي على حروف عربية، فحمّل ملف خط عربي من صفحة الخط في يمين (رابط المصدر الرسمي).", "بعد إضافة العمق (Extrude) والحواف (Bevel) حوّل النص إلى شبكة (Convert to Mesh) إذا أردت تعديل الحروف يدوياً."],
   noneed="لا يدعم Blender تشكيل الحروف العربية في كائنات النص، لذلك يحتاج كل نص عربي تقريباً إلى التصحيح قبل لصقه. TODO(verify): التغييرات في الإصدارات الأحدث."),
  en=dict(kind="3D app", use="3D titles and extruded logos",
   steps=["Add a text object (Add, Text) and enter Edit Mode with Tab.", "Delete the default text and paste the fixed text with Ctrl + V.", "Load the font file in the object’s Font properties instead of the default font.", "Set alignment to right in the Paragraph options."],
   note="TODO(verify): paste shortcut in text Edit Mode in the current version.",
   tips=["Blender’s default font has no Arabic letters, so load an Arabic font file from its page on Yameen (official source link).", "After adding Extrude and Bevel, convert the text to a mesh if you want to edit letters by hand."],
   noneed="Blender doesn't shape Arabic in text objects, so almost every Arabic text needs fixing before pasting. TODO(verify): changes in newer versions.")),
 "cinema-4d": dict(
  name="Cinema 4D",
  ar=dict(kind="برنامج التصميم والحركة ثلاثي الأبعاد", use="العناوين المجسمة والإعلانات ثلاثية الأبعاد",
   steps=["أضف كائن نص (Text Spline أو MoText).", "الصق النص المصحح في حقل Text في مدير الخصائص.", "اختر الخط من حقل Font.", "اضبط المحاذاة إلى اليمين."],
   note="TODO(verify): أسماء الكائنات والحقول في الإصدار الحالي.",
   tips=["في MoText تتحرك الحروف بترتيبها في النص، فتبدأ التأثيرات من يسار الكلمة. اعكس ترتيب التأثير إذا أردت أن يبدأ من اليمين.", "تأكد أن الخط مثبت على الجهاز قبل فتح الملف، وإلا استبدله البرنامج بخط لا يحتوي على حروف عربية."],
   noneed="TODO(verify): مدى دعم Cinema 4D للعربية في الإصدار الحالي. إذا ظهر النص سليماً عند كتابته مباشرة فلا تحتاج إلى التصحيح."),
  en=dict(kind="3D motion app", use="3D titles and motion ads",
   steps=["Add a text object (Text Spline or MoText).", "Paste the fixed text into the Text field in the Attribute Manager.", "Choose the font in the Font field.", "Set alignment to right."],
   note="TODO(verify): object and field names in the current version.",
   tips=["In MoText, letters animate in text order, so effects start from the left of the word. Reverse the effect order if you want it to start from the right.", "Make sure the font is installed before opening the file, or the app will swap it for a font without Arabic letters."],
   noneed="TODO(verify): Arabic support in the current version of Cinema 4D. If Arabic already shows correctly when typed directly, you don't need the fix.")),
}

def ar_page(slug, a):
    n, d = a["name"], a["ar"]
    fm = {
        "title": f"تصحيح النص العربي في {n}: حروف موصولة وبالاتجاه الصحيح | يمين",
        "description": f"اكتب النص العربي وانسخه جاهزاً للصق في {n}. يمين يصل الحروف ويرتبها حتى لا تظهر مقطعة أو معكوسة، مع خطوات اللصق واختيار الخط.",
        "h1": f"تصحيح النص العربي لبرنامج {n}",
        "intro": f"اكتب النص هنا، ثم انسخ النتيجة والصقها في {n}. تظهر الحروف موصولة وبالترتيب الصحيح.",
        "paste_steps": d["steps"], "paste_note": d["note"],
        "faq": [
            {"q": f"لماذا تظهر الحروف العربية مقطعة في {n}؟", "a": "لأن البرنامج لا يطبق قواعد وصل الحروف العربية ولا اتجاه الكتابة من اليمين إلى اليسار في إعداده الافتراضي، فيعرض كل حرف بشكله المنفصل وبترتيب معكوس."},
            {"q": "هل يجب أن أستخدم خطاً معيناً؟", "a": "استخدم خطاً يحتوي على أشكال الحروف العربية الموصولة. الخطوط الموسومة «يعمل في البرامج» في صفحة الخطوط في يمين تحتوي عليها."},
            {"q": "هل يمكنني تعديل النص بعد لصقه؟", "a": "يمكنك تغيير الحجم واللون والخط، أما تعديل الحروف داخل البرنامج فيكسر الترتيب. عدّل النص في يمين ثم انسخه من جديد."},
            {"q": "لماذا يظهر النص معكوساً عندما ألصقه في المتصفح أو واتساب؟", "a": f"لأنه مُعدّ للبرامج التي لا تدعم العربية مثل {n}. في الأماكن التي تدعم العربية الصق النص العادي مباشرة."},
            {"q": "هل يُرسل نصي إلى الخادم؟", "a": "لا. يتم التصحيح داخل المتصفح بالكامل، ولا يُحفظ النص في أي مكان."},
        ],
    }
    tips = "\n".join(f"- {t}" for t in d["tips"])
    body = f"""
## لماذا تظهر الحروف العربية مقطعة في {n}؟

يُخزَّن النص العربي في الحاسوب بترتيبه المنطقي: الحرف الأول ثم الثاني ثم الثالث، دون تحديد شكل كل حرف. البرامج التي تدعم العربية تقوم بخطوتين عند عرض النص: تختار لكل حرف شكله المناسب لموضعه في الكلمة (في أولها أو وسطها أو آخرها أو منفرداً)، ثم ترتب السطر من اليمين إلى اليسار.

{n} هو {d["kind"]} الذي يستخدمه كثير من المصممين في {d["use"]}، لكنه في إعداده الافتراضي قد لا يقوم بهاتين الخطوتين للنص العربي. النتيجة أن كل حرف يظهر منفصلاً عن جاره، وأن الكلمة تظهر مقلوبة من اليسار إلى اليمين، فتصبح «مرحبا» حروفاً متفرقة معكوسة.

## كيف يصحح يمين النص؟

يحوّل يمين النص إلى صيغة يفهمها {n} كما هي، دون أن يحتاج البرنامج إلى دعم العربية:

1. **وصل الحروف:** يستبدل كل حرف بشكله الموصول المناسب لموضعه في الكلمة، ويدمج اللام مع الألف في «لا» و«لأ» و«لإ» و«لآ».
2. **ترتيب السطر:** يرتب الحروف بصرياً من اليسار إلى اليمين، فيعرضها البرنامج بالترتيب الصحيح، مع إبقاء الكلمات الإنجليزية والأرقام في اتجاهها الطبيعي.
3. **إزالة التشكيل:** يحذف الحركات افتراضياً، لأن أغلب البرامج غير الداعمة للعربية لا تضعها في مكانها الصحيح. يمكنك إيقاف هذا الخيار من الخيارات المتقدمة.

يتم كل ذلك داخل متصفحك، ولا يُرسل النص إلى أي خادم.

## خطوات الاستخدام مع {n}

اكتب نصك في المربع أعلى الصفحة، واختر الخط الذي ستستخدمه، ثم اضغط «انسخ للبرنامج». بعد ذلك اتبع خطوات اللصق الموضحة في المربع أسفل الأداة. يعالج يمين كل سطر وحده، فإذا كان نصك من عدة أسطر فاكتبه بالأسطر نفسها التي تريدها في التصميم.

{tips}

## اختيار الخط المناسب

لا يكفي أن يكون الخط عربياً، بل يجب أن يحتوي على أشكال الحروف الموصولة (Arabic Presentation Forms)، لأن النص المصحح يستخدم هذه الأشكال مباشرة. إذا ظهرت مربعات أو علامات استفهام بدل الحروف في {n}، فالخط المختار لا يحتوي عليها.

في صفحة الخطوط في يمين تجد عشرات الخطوط العربية المجانية، وتظهر عبارة «يعمل في البرامج» على كل خط يحتوي على الأشكال المطلوبة. نزّل الخط من مصدره الرسمي وثبته على جهازك، ثم اختر الاسم نفسه داخل {n}.

## مشكلات شائعة وحلولها

- **النص يظهر معكوساً في {n}:** ربما يدعم البرنامج العربية في إعداداتك، فالصق النص الأصلي بدلاً من المصحح.
- **مربعات بدل الحروف:** اختر خطاً موسوماً بـ«يعمل في البرامج».
- **الأسطر مرتبة بشكل خاطئ:** لا تقسم السطر داخل البرنامج، بل أدخل فواصل الأسطر في يمين ثم انسخ النص من جديد.
- **الحركات في غير مكانها:** اترك خيار «إزالة التشكيل» مفعلاً.

## متى لا تحتاج إلى يمين؟

{d["noneed"]}
"""
    return fm, body

def en_page(slug, a):
    n, d = a["name"], a["en"]
    fm = {
        "title": f"Fix Arabic text in {n}: connected letters, right direction | Yameen",
        "description": f"Type Arabic and copy it ready to paste into {n}. Yameen connects the letters and orders them so they don't appear disconnected or reversed, with paste steps and font tips.",
        "h1": f"Fix Arabic text for {n}",
        "intro": f"Type your text here, then copy the result and paste it into {n}. Letters appear connected and in the right order.",
        "paste_steps": d["steps"], "paste_note": d["note"],
        "faq": [
            {"q": f"Why do Arabic letters appear disconnected in {n}?", "a": "Because the app doesn't apply Arabic letter joining or right-to-left ordering in its default setup, so each letter is shown in its standalone form and in reverse order."},
            {"q": "Do I need a specific font?", "a": "Use a font that includes connected Arabic letter forms. Fonts marked “Works in apps” on Yameen’s fonts page include them."},
            {"q": "Can I edit the text after pasting?", "a": "You can change size, colour and font. Editing the letters inside the app breaks the ordering, so edit in Yameen and copy again."},
            {"q": "Why does the text look reversed when I paste it in a browser or WhatsApp?", "a": f"It is prepared for apps without Arabic support, like {n}. Where Arabic is supported, paste the normal text."},
            {"q": "Is my text sent to a server?", "a": "No. Fixing happens entirely in your browser and the text isn't stored anywhere."},
        ],
    }
    tips = "\n".join(f"- {t}" for t in d["tips"])
    body = f"""
## Why do Arabic letters appear disconnected in {n}?

Computers store Arabic in logical order: first letter, second letter, third letter, without saying which shape each letter takes. Apps that support Arabic do two things when they display it: they choose each letter's shape for its position in the word (start, middle, end or standalone), then lay the line out from right to left.

{n} is a {d["kind"]} that many designers use for {d["use"]}, but in its default setup it may not do either step for Arabic. Each letter then appears separated from its neighbour and the word runs backwards from left to right, so «مرحبا» becomes scattered, reversed letters.

## How Yameen fixes the text

Yameen converts the text into a form {n} displays as is, without needing Arabic support:

1. **Connect letters:** each letter is replaced with its connected form for its position in the word, and lam is merged with alef in «لا», «لأ», «لإ» and «لآ».
2. **Order the line:** letters are ordered visually from left to right so the app shows them in the right order, while English words and numbers keep their normal direction.
3. **Remove diacritics:** diacritics are removed by default, because most apps without Arabic support can't place them correctly. You can turn this off in the advanced options.

All of this happens in your browser; the text is never sent to a server.

## Using it with {n}

Type your text in the box at the top, choose the font you will use, then press “Copy for your app”. Follow the paste steps shown under the tool. Yameen processes each line on its own, so type multi-line text with the same line breaks you want in the design.

{tips}

## Choosing the right font

An Arabic font is not enough: it must include connected letter forms (Arabic Presentation Forms), because the fixed text uses those forms directly. If {n} shows boxes or question marks instead of letters, the chosen font doesn't include them.

Yameen’s fonts page lists dozens of free Arabic fonts, and every font that includes the needed forms is marked “Works in apps”. Download the font from its official source, install it, then pick the same name in {n}.

## Common problems

- **The text looks reversed in {n}:** the app may support Arabic in your setup, so paste the original text instead of the fixed one.
- **Boxes instead of letters:** choose a font marked “Works in apps”.
- **Lines in the wrong order:** don't break lines inside the app; add line breaks in Yameen and copy again.
- **Diacritics in the wrong place:** keep “Remove diacritics” on.

## When you don't need Yameen

{d["noneed"]}
"""
    return fm, body

def write(locale, slug, fm, body):
    path = ROOT / locale / f"fix-{slug}.md"
    path.write_text("---\n" + yaml.safe_dump(fm, allow_unicode=True, sort_keys=False, width=1000) + "---\n\n" + body.strip() + "\n", encoding="utf-8")

for slug, a in APPS.items():
    write("ar", slug, *ar_page(slug, a))
    write("en", slug, *en_page(slug, a))
print("ok")
