import{n as e}from"./pdf-BrDbpMKA.js";import{a as t,i as n,s as r}from"./pdf-words-CsM_yEOh.js";function i(e){return e.split(/\n\s*\n/).map(e=>e.split(`
`).map(e=>e.trim()).filter(Boolean).join(` `)).join(`

`)}function a(a,o,{join:s=!0}={}){if(!a.trim())return{text:``,parts:[],fixed:0,reversed:!1};let c=e(a,{reverse:`auto`}),l=c.text;return o&&(l=n(l,o)),s&&(l=i(l)),o?{...t(l,o,{aggressive:r(l)>=1}),reversed:c.reversedApplied}:{text:l,parts:[{text:l}],fixed:0,reversed:c.reversedApplied}}export{a as fixGarbledText,i as joinLines};