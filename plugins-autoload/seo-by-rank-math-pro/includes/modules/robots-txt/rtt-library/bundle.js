/*!
  Highlight.js v11.9.0 (git: b7ec4bfafc)
  (c) 2006-2023 undefined and other contributors
  License: BSD-3-Clause
 */
var hljs=function(){"use strict";function e(t){
return t instanceof Map?t.clear=t.delete=t.set=()=>{
throw Error("map is read-only")}:t instanceof Set&&(t.add=t.clear=t.delete=()=>{
throw Error("set is read-only")
}),Object.freeze(t),Object.getOwnPropertyNames(t).forEach((n=>{
const i=t[n],s=typeof i;"object"!==s&&"function"!==s||Object.isFrozen(i)||e(i)
})),t}class t{constructor(e){
void 0===e.data&&(e.data={}),this.data=e.data,this.isMatchIgnored=!1}
ignoreMatch(){this.isMatchIgnored=!0}}function n(e){
return e.replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/"/g,"&quot;").replace(/'/g,"&#x27;")
}function i(e,...t){const n=Object.create(null);for(const t in e)n[t]=e[t]
;return t.forEach((e=>{for(const t in e)n[t]=e[t]})),n}const s=e=>!!e.scope
;class o{constructor(e,t){
this.buffer="",this.classPrefix=t.classPrefix,e.walk(this)}addText(e){
this.buffer+=n(e)}openNode(e){if(!s(e))return;const t=((e,{prefix:t})=>{
if(e.startsWith("language:"))return e.replace("language:","language-")
;if(e.includes(".")){const n=e.split(".")
;return[`${t}${n.shift()}`,...n.map(((e,t)=>`${e}${"_".repeat(t+1)}`))].join(" ")
}return`${t}${e}`})(e.scope,{prefix:this.classPrefix});this.span(t)}
closeNode(e){s(e)&&(this.buffer+="</span>")}value(){return this.buffer}span(e){
this.buffer+=`<span class="${e}">`}}const r=(e={})=>{const t={children:[]}
;return Object.assign(t,e),t};class a{constructor(){
this.rootNode=r(),this.stack=[this.rootNode]}get top(){
return this.stack[this.stack.length-1]}get root(){return this.rootNode}add(e){
this.top.children.push(e)}openNode(e){const t=r({scope:e})
;this.add(t),this.stack.push(t)}closeNode(){
if(this.stack.length>1)return this.stack.pop()}closeAllNodes(){
for(;this.closeNode(););}toJSON(){return JSON.stringify(this.rootNode,null,4)}
walk(e){return this.constructor._walk(e,this.rootNode)}static _walk(e,t){
return"string"==typeof t?e.addText(t):t.children&&(e.openNode(t),
t.children.forEach((t=>this._walk(e,t))),e.closeNode(t)),e}static _collapse(e){
"string"!=typeof e&&e.children&&(e.children.every((e=>"string"==typeof e))?e.children=[e.children.join("")]:e.children.forEach((e=>{
a._collapse(e)})))}}class c extends a{constructor(e){super(),this.options=e}
addText(e){""!==e&&this.add(e)}startScope(e){this.openNode(e)}endScope(){
this.closeNode()}__addSublanguage(e,t){const n=e.root
;t&&(n.scope="language:"+t),this.add(n)}toHTML(){
return new o(this,this.options).value()}finalize(){
return this.closeAllNodes(),!0}}function l(e){
return e?"string"==typeof e?e:e.source:null}function g(e){return h("(?=",e,")")}
function u(e){return h("(?:",e,")*")}function d(e){return h("(?:",e,")?")}
function h(...e){return e.map((e=>l(e))).join("")}function f(...e){const t=(e=>{
const t=e[e.length-1]
;return"object"==typeof t&&t.constructor===Object?(e.splice(e.length-1,1),t):{}
})(e);return"("+(t.capture?"":"?:")+e.map((e=>l(e))).join("|")+")"}
function p(e){return RegExp(e.toString()+"|").exec("").length-1}
const b=/\[(?:[^\\\]]|\\.)*\]|\(\??|\\([1-9][0-9]*)|\\./
;function m(e,{joinWith:t}){let n=0;return e.map((e=>{n+=1;const t=n
;let i=l(e),s="";for(;i.length>0;){const e=b.exec(i);if(!e){s+=i;break}
s+=i.substring(0,e.index),
i=i.substring(e.index+e[0].length),"\\"===e[0][0]&&e[1]?s+="\\"+(Number(e[1])+t):(s+=e[0],
"("===e[0]&&n++)}return s})).map((e=>`(${e})`)).join(t)}
const E="[a-zA-Z]\\w*",x="[a-zA-Z_]\\w*",w="\\b\\d+(\\.\\d+)?",y="(-?)(\\b0[xX][a-fA-F0-9]+|(\\b\\d+(\\.\\d*)?|\\.\\d+)([eE][-+]?\\d+)?)",_="\\b(0b[01]+)",O={
begin:"\\\\[\\s\\S]",relevance:0},v={scope:"string",begin:"'",end:"'",
illegal:"\\n",contains:[O]},k={scope:"string",begin:'"',end:'"',illegal:"\\n",
contains:[O]},N=(e,t,n={})=>{const s=i({scope:"comment",begin:e,end:t,
contains:[]},n);s.contains.push({scope:"doctag",
begin:"[ ]*(?=(TODO|FIXME|NOTE|BUG|OPTIMIZE|HACK|XXX):)",
end:/(TODO|FIXME|NOTE|BUG|OPTIMIZE|HACK|XXX):/,excludeBegin:!0,relevance:0})
;const o=f("I","a","is","so","us","to","at","if","in","it","on",/[A-Za-z]+['](d|ve|re|ll|t|s|n)/,/[A-Za-z]+[-][a-z]+/,/[A-Za-z][a-z]{2,}/)
;return s.contains.push({begin:h(/[ ]+/,"(",o,/[.]?[:]?([.][ ]|[ ])/,"){3}")}),s
},S=N("//","$"),M=N("/\\*","\\*/"),R=N("#","$");var j=Object.freeze({
__proto__:null,APOS_STRING_MODE:v,BACKSLASH_ESCAPE:O,BINARY_NUMBER_MODE:{
scope:"number",begin:_,relevance:0},BINARY_NUMBER_RE:_,COMMENT:N,
C_BLOCK_COMMENT_MODE:M,C_LINE_COMMENT_MODE:S,C_NUMBER_MODE:{scope:"number",
begin:y,relevance:0},C_NUMBER_RE:y,END_SAME_AS_BEGIN:e=>Object.assign(e,{
"on:begin":(e,t)=>{t.data._beginMatch=e[1]},"on:end":(e,t)=>{
t.data._beginMatch!==e[1]&&t.ignoreMatch()}}),HASH_COMMENT_MODE:R,IDENT_RE:E,
MATCH_NOTHING_RE:/\b\B/,METHOD_GUARD:{begin:"\\.\\s*"+x,relevance:0},
NUMBER_MODE:{scope:"number",begin:w,relevance:0},NUMBER_RE:w,
PHRASAL_WORDS_MODE:{
begin:/\b(a|an|the|are|I'm|isn't|don't|doesn't|won't|but|just|should|pretty|simply|enough|gonna|going|wtf|so|such|will|you|your|they|like|more)\b/
},QUOTE_STRING_MODE:k,REGEXP_MODE:{scope:"regexp",begin:/\/(?=[^/\n]*\/)/,
end:/\/[gimuy]*/,contains:[O,{begin:/\[/,end:/\]/,relevance:0,contains:[O]}]},
RE_STARTERS_RE:"!|!=|!==|%|%=|&|&&|&=|\\*|\\*=|\\+|\\+=|,|-|-=|/=|/|:|;|<<|<<=|<=|<|===|==|=|>>>=|>>=|>=|>>>|>>|>|\\?|\\[|\\{|\\(|\\^|\\^=|\\||\\|=|\\|\\||~",
SHEBANG:(e={})=>{const t=/^#![ ]*\//
;return e.binary&&(e.begin=h(t,/.*\b/,e.binary,/\b.*/)),i({scope:"meta",begin:t,
end:/$/,relevance:0,"on:begin":(e,t)=>{0!==e.index&&t.ignoreMatch()}},e)},
TITLE_MODE:{scope:"title",begin:E,relevance:0},UNDERSCORE_IDENT_RE:x,
UNDERSCORE_TITLE_MODE:{scope:"title",begin:x,relevance:0}});function A(e,t){
"."===e.input[e.index-1]&&t.ignoreMatch()}function I(e,t){
void 0!==e.className&&(e.scope=e.className,delete e.className)}function T(e,t){
t&&e.beginKeywords&&(e.begin="\\b("+e.beginKeywords.split(" ").join("|")+")(?!\\.)(?=\\b|\\s)",
e.__beforeBegin=A,e.keywords=e.keywords||e.beginKeywords,delete e.beginKeywords,
void 0===e.relevance&&(e.relevance=0))}function L(e,t){
Array.isArray(e.illegal)&&(e.illegal=f(...e.illegal))}function B(e,t){
if(e.match){
if(e.begin||e.end)throw Error("begin & end are not supported with match")
;e.begin=e.match,delete e.match}}function P(e,t){
void 0===e.relevance&&(e.relevance=1)}const D=(e,t)=>{if(!e.beforeMatch)return
;if(e.starts)throw Error("beforeMatch cannot be used with starts")
;const n=Object.assign({},e);Object.keys(e).forEach((t=>{delete e[t]
})),e.keywords=n.keywords,e.begin=h(n.beforeMatch,g(n.begin)),e.starts={
relevance:0,contains:[Object.assign(n,{endsParent:!0})]
},e.relevance=0,delete n.beforeMatch
},H=["of","and","for","in","not","or","if","then","parent","list","value"],C="keyword"
;function $(e,t,n=C){const i=Object.create(null)
;return"string"==typeof e?s(n,e.split(" ")):Array.isArray(e)?s(n,e):Object.keys(e).forEach((n=>{
Object.assign(i,$(e[n],t,n))})),i;function s(e,n){
t&&(n=n.map((e=>e.toLowerCase()))),n.forEach((t=>{const n=t.split("|")
;i[n[0]]=[e,U(n[0],n[1])]}))}}function U(e,t){
return t?Number(t):(e=>H.includes(e.toLowerCase()))(e)?0:1}const z={},W=e=>{
console.error(e)},X=(e,...t)=>{console.log("WARN: "+e,...t)},G=(e,t)=>{
z[`${e}/${t}`]||(console.log(`Deprecated as of ${e}. ${t}`),z[`${e}/${t}`]=!0)
},K=Error();function F(e,t,{key:n}){let i=0;const s=e[n],o={},r={}
;for(let e=1;e<=t.length;e++)r[e+i]=s[e],o[e+i]=!0,i+=p(t[e-1])
;e[n]=r,e[n]._emit=o,e[n]._multi=!0}function Z(e){(e=>{
e.scope&&"object"==typeof e.scope&&null!==e.scope&&(e.beginScope=e.scope,
delete e.scope)})(e),"string"==typeof e.beginScope&&(e.beginScope={
_wrap:e.beginScope}),"string"==typeof e.endScope&&(e.endScope={_wrap:e.endScope
}),(e=>{if(Array.isArray(e.begin)){
if(e.skip||e.excludeBegin||e.returnBegin)throw W("skip, excludeBegin, returnBegin not compatible with beginScope: {}"),
K
;if("object"!=typeof e.beginScope||null===e.beginScope)throw W("beginScope must be object"),
K;F(e,e.begin,{key:"beginScope"}),e.begin=m(e.begin,{joinWith:""})}})(e),(e=>{
if(Array.isArray(e.end)){
if(e.skip||e.excludeEnd||e.returnEnd)throw W("skip, excludeEnd, returnEnd not compatible with endScope: {}"),
K
;if("object"!=typeof e.endScope||null===e.endScope)throw W("endScope must be object"),
K;F(e,e.end,{key:"endScope"}),e.end=m(e.end,{joinWith:""})}})(e)}function V(e){
function t(t,n){
return RegExp(l(t),"m"+(e.case_insensitive?"i":"")+(e.unicodeRegex?"u":"")+(n?"g":""))
}class n{constructor(){
this.matchIndexes={},this.regexes=[],this.matchAt=1,this.position=0}
addRule(e,t){
t.position=this.position++,this.matchIndexes[this.matchAt]=t,this.regexes.push([t,e]),
this.matchAt+=p(e)+1}compile(){0===this.regexes.length&&(this.exec=()=>null)
;const e=this.regexes.map((e=>e[1]));this.matcherRe=t(m(e,{joinWith:"|"
}),!0),this.lastIndex=0}exec(e){this.matcherRe.lastIndex=this.lastIndex
;const t=this.matcherRe.exec(e);if(!t)return null
;const n=t.findIndex(((e,t)=>t>0&&void 0!==e)),i=this.matchIndexes[n]
;return t.splice(0,n),Object.assign(t,i)}}class s{constructor(){
this.rules=[],this.multiRegexes=[],
this.count=0,this.lastIndex=0,this.regexIndex=0}getMatcher(e){
if(this.multiRegexes[e])return this.multiRegexes[e];const t=new n
;return this.rules.slice(e).forEach((([e,n])=>t.addRule(e,n))),
t.compile(),this.multiRegexes[e]=t,t}resumingScanAtSamePosition(){
return 0!==this.regexIndex}considerAll(){this.regexIndex=0}addRule(e,t){
this.rules.push([e,t]),"begin"===t.type&&this.count++}exec(e){
const t=this.getMatcher(this.regexIndex);t.lastIndex=this.lastIndex
;let n=t.exec(e)
;if(this.resumingScanAtSamePosition())if(n&&n.index===this.lastIndex);else{
const t=this.getMatcher(0);t.lastIndex=this.lastIndex+1,n=t.exec(e)}
return n&&(this.regexIndex+=n.position+1,
this.regexIndex===this.count&&this.considerAll()),n}}
if(e.compilerExtensions||(e.compilerExtensions=[]),
e.contains&&e.contains.includes("self"))throw Error("ERR: contains `self` is not supported at the top-level of a language.  See documentation.")
;return e.classNameAliases=i(e.classNameAliases||{}),function n(o,r){const a=o
;if(o.isCompiled)return a
;[I,B,Z,D].forEach((e=>e(o,r))),e.compilerExtensions.forEach((e=>e(o,r))),
o.__beforeBegin=null,[T,L,P].forEach((e=>e(o,r))),o.isCompiled=!0;let c=null
;return"object"==typeof o.keywords&&o.keywords.$pattern&&(o.keywords=Object.assign({},o.keywords),
c=o.keywords.$pattern,
delete o.keywords.$pattern),c=c||/\w+/,o.keywords&&(o.keywords=$(o.keywords,e.case_insensitive)),
a.keywordPatternRe=t(c,!0),
r&&(o.begin||(o.begin=/\B|\b/),a.beginRe=t(a.begin),o.end||o.endsWithParent||(o.end=/\B|\b/),
o.end&&(a.endRe=t(a.end)),
a.terminatorEnd=l(a.end)||"",o.endsWithParent&&r.terminatorEnd&&(a.terminatorEnd+=(o.end?"|":"")+r.terminatorEnd)),
o.illegal&&(a.illegalRe=t(o.illegal)),
o.contains||(o.contains=[]),o.contains=[].concat(...o.contains.map((e=>(e=>(e.variants&&!e.cachedVariants&&(e.cachedVariants=e.variants.map((t=>i(e,{
variants:null},t)))),e.cachedVariants?e.cachedVariants:q(e)?i(e,{
starts:e.starts?i(e.starts):null
}):Object.isFrozen(e)?i(e):e))("self"===e?o:e)))),o.contains.forEach((e=>{n(e,a)
})),o.starts&&n(o.starts,r),a.matcher=(e=>{const t=new s
;return e.contains.forEach((e=>t.addRule(e.begin,{rule:e,type:"begin"
}))),e.terminatorEnd&&t.addRule(e.terminatorEnd,{type:"end"
}),e.illegal&&t.addRule(e.illegal,{type:"illegal"}),t})(a),a}(e)}function q(e){
return!!e&&(e.endsWithParent||q(e.starts))}class J extends Error{
constructor(e,t){super(e),this.name="HTMLInjectionError",this.html=t}}
const Y=n,Q=i,ee=Symbol("nomatch"),te=n=>{
const i=Object.create(null),s=Object.create(null),o=[];let r=!0
;const a="Could not find the language '{}', did you forget to load/include a language module?",l={
disableAutodetect:!0,name:"Plain text",contains:[]};let p={
ignoreUnescapedHTML:!1,throwUnescapedHTML:!1,noHighlightRe:/^(no-?highlight)$/i,
languageDetectRe:/\blang(?:uage)?-([\w-]+)\b/i,classPrefix:"hljs-",
cssSelector:"pre code",languages:null,__emitter:c};function b(e){
return p.noHighlightRe.test(e)}function m(e,t,n){let i="",s=""
;"object"==typeof t?(i=e,
n=t.ignoreIllegals,s=t.language):(G("10.7.0","highlight(lang, code, ...args) has been deprecated."),
G("10.7.0","Please use highlight(code, options) instead.\nhttps://github.com/highlightjs/highlight.js/issues/2277"),
s=e,i=t),void 0===n&&(n=!0);const o={code:i,language:s};N("before:highlight",o)
;const r=o.result?o.result:E(o.language,o.code,n)
;return r.code=o.code,N("after:highlight",r),r}function E(e,n,s,o){
const c=Object.create(null);function l(){if(!N.keywords)return void M.addText(R)
;let e=0;N.keywordPatternRe.lastIndex=0;let t=N.keywordPatternRe.exec(R),n=""
;for(;t;){n+=R.substring(e,t.index)
;const s=_.case_insensitive?t[0].toLowerCase():t[0],o=(i=s,N.keywords[i]);if(o){
const[e,i]=o
;if(M.addText(n),n="",c[s]=(c[s]||0)+1,c[s]<=7&&(j+=i),e.startsWith("_"))n+=t[0];else{
const n=_.classNameAliases[e]||e;u(t[0],n)}}else n+=t[0]
;e=N.keywordPatternRe.lastIndex,t=N.keywordPatternRe.exec(R)}var i
;n+=R.substring(e),M.addText(n)}function g(){null!=N.subLanguage?(()=>{
if(""===R)return;let e=null;if("string"==typeof N.subLanguage){
if(!i[N.subLanguage])return void M.addText(R)
;e=E(N.subLanguage,R,!0,S[N.subLanguage]),S[N.subLanguage]=e._top
}else e=x(R,N.subLanguage.length?N.subLanguage:null)
;N.relevance>0&&(j+=e.relevance),M.__addSublanguage(e._emitter,e.language)
})():l(),R=""}function u(e,t){
""!==e&&(M.startScope(t),M.addText(e),M.endScope())}function d(e,t){let n=1
;const i=t.length-1;for(;n<=i;){if(!e._emit[n]){n++;continue}
const i=_.classNameAliases[e[n]]||e[n],s=t[n];i?u(s,i):(R=s,l(),R=""),n++}}
function h(e,t){
return e.scope&&"string"==typeof e.scope&&M.openNode(_.classNameAliases[e.scope]||e.scope),
e.beginScope&&(e.beginScope._wrap?(u(R,_.classNameAliases[e.beginScope._wrap]||e.beginScope._wrap),
R=""):e.beginScope._multi&&(d(e.beginScope,t),R="")),N=Object.create(e,{parent:{
value:N}}),N}function f(e,n,i){let s=((e,t)=>{const n=e&&e.exec(t)
;return n&&0===n.index})(e.endRe,i);if(s){if(e["on:end"]){const i=new t(e)
;e["on:end"](n,i),i.isMatchIgnored&&(s=!1)}if(s){
for(;e.endsParent&&e.parent;)e=e.parent;return e}}
if(e.endsWithParent)return f(e.parent,n,i)}function b(e){
return 0===N.matcher.regexIndex?(R+=e[0],1):(T=!0,0)}function m(e){
const t=e[0],i=n.substring(e.index),s=f(N,e,i);if(!s)return ee;const o=N
;N.endScope&&N.endScope._wrap?(g(),
u(t,N.endScope._wrap)):N.endScope&&N.endScope._multi?(g(),
d(N.endScope,e)):o.skip?R+=t:(o.returnEnd||o.excludeEnd||(R+=t),
g(),o.excludeEnd&&(R=t));do{
N.scope&&M.closeNode(),N.skip||N.subLanguage||(j+=N.relevance),N=N.parent
}while(N!==s.parent);return s.starts&&h(s.starts,e),o.returnEnd?0:t.length}
let w={};function y(i,o){const a=o&&o[0];if(R+=i,null==a)return g(),0
;if("begin"===w.type&&"end"===o.type&&w.index===o.index&&""===a){
if(R+=n.slice(o.index,o.index+1),!r){const t=Error(`0 width match regex (${e})`)
;throw t.languageName=e,t.badRule=w.rule,t}return 1}
if(w=o,"begin"===o.type)return(e=>{
const n=e[0],i=e.rule,s=new t(i),o=[i.__beforeBegin,i["on:begin"]]
;for(const t of o)if(t&&(t(e,s),s.isMatchIgnored))return b(n)
;return i.skip?R+=n:(i.excludeBegin&&(R+=n),
g(),i.returnBegin||i.excludeBegin||(R=n)),h(i,e),i.returnBegin?0:n.length})(o)
;if("illegal"===o.type&&!s){
const e=Error('Illegal lexeme "'+a+'" for mode "'+(N.scope||"<unnamed>")+'"')
;throw e.mode=N,e}if("end"===o.type){const e=m(o);if(e!==ee)return e}
if("illegal"===o.type&&""===a)return 1
;if(I>1e5&&I>3*o.index)throw Error("potential infinite loop, way more iterations than matches")
;return R+=a,a.length}const _=O(e)
;if(!_)throw W(a.replace("{}",e)),Error('Unknown language: "'+e+'"')
;const v=V(_);let k="",N=o||v;const S={},M=new p.__emitter(p);(()=>{const e=[]
;for(let t=N;t!==_;t=t.parent)t.scope&&e.unshift(t.scope)
;e.forEach((e=>M.openNode(e)))})();let R="",j=0,A=0,I=0,T=!1;try{
if(_.__emitTokens)_.__emitTokens(n,M);else{for(N.matcher.considerAll();;){
I++,T?T=!1:N.matcher.considerAll(),N.matcher.lastIndex=A
;const e=N.matcher.exec(n);if(!e)break;const t=y(n.substring(A,e.index),e)
;A=e.index+t}y(n.substring(A))}return M.finalize(),k=M.toHTML(),{language:e,
value:k,relevance:j,illegal:!1,_emitter:M,_top:N}}catch(t){
if(t.message&&t.message.includes("Illegal"))return{language:e,value:Y(n),
illegal:!0,relevance:0,_illegalBy:{message:t.message,index:A,
context:n.slice(A-100,A+100),mode:t.mode,resultSoFar:k},_emitter:M};if(r)return{
language:e,value:Y(n),illegal:!1,relevance:0,errorRaised:t,_emitter:M,_top:N}
;throw t}}function x(e,t){t=t||p.languages||Object.keys(i);const n=(e=>{
const t={value:Y(e),illegal:!1,relevance:0,_top:l,_emitter:new p.__emitter(p)}
;return t._emitter.addText(e),t})(e),s=t.filter(O).filter(k).map((t=>E(t,e,!1)))
;s.unshift(n);const o=s.sort(((e,t)=>{
if(e.relevance!==t.relevance)return t.relevance-e.relevance
;if(e.language&&t.language){if(O(e.language).supersetOf===t.language)return 1
;if(O(t.language).supersetOf===e.language)return-1}return 0})),[r,a]=o,c=r
;return c.secondBest=a,c}function w(e){let t=null;const n=(e=>{
let t=e.className+" ";t+=e.parentNode?e.parentNode.className:""
;const n=p.languageDetectRe.exec(t);if(n){const t=O(n[1])
;return t||(X(a.replace("{}",n[1])),
X("Falling back to no-highlight mode for this block.",e)),t?n[1]:"no-highlight"}
return t.split(/\s+/).find((e=>b(e)||O(e)))})(e);if(b(n))return
;if(N("before:highlightElement",{el:e,language:n
}),e.dataset.highlighted)return void console.log("Element previously highlighted. To highlight again, first unset `dataset.highlighted`.",e)
;if(e.children.length>0&&(p.ignoreUnescapedHTML||(console.warn("One of your code blocks includes unescaped HTML. This is a potentially serious security risk."),
console.warn("https://github.com/highlightjs/highlight.js/wiki/security"),
console.warn("The element with unescaped HTML:"),
console.warn(e)),p.throwUnescapedHTML))throw new J("One of your code blocks includes unescaped HTML.",e.innerHTML)
;t=e;const i=t.textContent,o=n?m(i,{language:n,ignoreIllegals:!0}):x(i)
;e.innerHTML=o.value,e.dataset.highlighted="yes",((e,t,n)=>{const i=t&&s[t]||n
;e.classList.add("hljs"),e.classList.add("language-"+i)
})(e,n,o.language),e.result={language:o.language,re:o.relevance,
relevance:o.relevance},o.secondBest&&(e.secondBest={
language:o.secondBest.language,relevance:o.secondBest.relevance
}),N("after:highlightElement",{el:e,result:o,text:i})}let y=!1;function _(){
"loading"!==document.readyState?document.querySelectorAll(p.cssSelector).forEach(w):y=!0
}function O(e){return e=(e||"").toLowerCase(),i[e]||i[s[e]]}
function v(e,{languageName:t}){"string"==typeof e&&(e=[e]),e.forEach((e=>{
s[e.toLowerCase()]=t}))}function k(e){const t=O(e)
;return t&&!t.disableAutodetect}function N(e,t){const n=e;o.forEach((e=>{
e[n]&&e[n](t)}))}
"undefined"!=typeof window&&window.addEventListener&&window.addEventListener("DOMContentLoaded",(()=>{
y&&_()}),!1),Object.assign(n,{highlight:m,highlightAuto:x,highlightAll:_,
highlightElement:w,
highlightBlock:e=>(G("10.7.0","highlightBlock will be removed entirely in v12.0"),
G("10.7.0","Please use highlightElement now."),w(e)),configure:e=>{p=Q(p,e)},
initHighlighting:()=>{
_(),G("10.6.0","initHighlighting() deprecated.  Use highlightAll() now.")},
initHighlightingOnLoad:()=>{
_(),G("10.6.0","initHighlightingOnLoad() deprecated.  Use highlightAll() now.")
},registerLanguage:(e,t)=>{let s=null;try{s=t(n)}catch(t){
if(W("Language definition for '{}' could not be registered.".replace("{}",e)),
!r)throw t;W(t),s=l}
s.name||(s.name=e),i[e]=s,s.rawDefinition=t.bind(null,n),s.aliases&&v(s.aliases,{
languageName:e})},unregisterLanguage:e=>{delete i[e]
;for(const t of Object.keys(s))s[t]===e&&delete s[t]},
listLanguages:()=>Object.keys(i),getLanguage:O,registerAliases:v,
autoDetection:k,inherit:Q,addPlugin:e=>{(e=>{
e["before:highlightBlock"]&&!e["before:highlightElement"]&&(e["before:highlightElement"]=t=>{
e["before:highlightBlock"](Object.assign({block:t.el},t))
}),e["after:highlightBlock"]&&!e["after:highlightElement"]&&(e["after:highlightElement"]=t=>{
e["after:highlightBlock"](Object.assign({block:t.el},t))})})(e),o.push(e)},
removePlugin:e=>{const t=o.indexOf(e);-1!==t&&o.splice(t,1)}}),n.debugMode=()=>{
r=!1},n.safeMode=()=>{r=!0},n.versionString="11.9.0",n.regex={concat:h,
lookahead:g,either:f,optional:d,anyNumberOfTimes:u}
;for(const t in j)"object"==typeof j[t]&&e(j[t]);return Object.assign(n,j),n
},ne=te({});return ne.newInstance=()=>te({}),ne}()
;"object"==typeof exports&&"undefined"!=typeof module&&(module.exports=hljs);/*! `apache` grammar compiled for Highlight.js 11.9.0 */
(()=>{var e=(()=>{"use strict";return e=>{const n={className:"number",
begin:/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}(:\d{1,5})?/};return{
name:"Apache config",aliases:["apacheconf"],case_insensitive:!0,
contains:[e.HASH_COMMENT_MODE,{className:"section",begin:/<\/?/,end:/>/,
contains:[n,{className:"number",begin:/:\d{1,5}/
},e.inherit(e.QUOTE_STRING_MODE,{relevance:0})]},{className:"attribute",
begin:/\w+/,relevance:0,keywords:{
_:["order","deny","allow","setenv","rewriterule","rewriteengine","rewritecond","documentroot","sethandler","errordocument","loadmodule","options","header","listen","serverroot","servername"]
},starts:{end:/$/,relevance:0,keywords:{literal:"on off all deny allow"},
contains:[{className:"meta",begin:/\s\[/,end:/\]$/},{className:"variable",
begin:/[\$%]\{/,end:/\}/,contains:["self",{className:"number",begin:/[$%]\d+/}]
},n,{className:"number",begin:/\b\d+/},e.QUOTE_STRING_MODE]}}],illegal:/\S/}}
})();hljs.registerLanguage("apache",e)})();
(function(w, d) {
    'use strict';

    if(w.hljs) {
        w.hljs.highlightLinesAll = highlightLinesAll;
        w.hljs.highlightLinesElement = highlightLinesElement;
        w.hljs.unhighlightAllLines = unhighlightAllLines; // Add unhighlight all lines function

        /* deprecated */
        w.hljs.initHighlightLinesOnLoad = initHighlightLinesOnLoadWithDeprecated;
        w.hljs.highlightLinesCode = highlightLinesCodeWithDeprecated;
    }

    function highlightLinesAll(options) {
        for(var i = 0; i < options.length; ++i) {
            for(var option of options[i]) {
                --option.start;
                --option.end;
            }
        }
        initHighlightLinesOnLoad(options);
    }

    var initHighlightLinesOnLoadWithDeprecatedCalled = false;
    function initHighlightLinesOnLoadWithDeprecated(options) {
        if(!initHighlightLinesOnLoadWithDeprecatedCalled) {
            console.log('hljs.initHighlightLinesOnLoad is deprecated. Please use hljs.highlightLinesAll')
            initHighlightLinesOnLoadWithDeprecatedCalled = true;
        }
        initHighlightLinesOnLoad(options)
    }

    function initHighlightLinesOnLoad(options) {
        function callHighlightLinesCode() {
            var codes = d.getElementsByClassName('hljs');
            for(var i = 0; i < codes.length; ++i) {
                highlightLinesCode(codes[i], options[i]);
            }
        }

        if(d.readyState !== 'loading') {
            callHighlightLinesCode();
        }
        else {
            w.addEventListener('DOMContentLoaded', function() {
                callHighlightLinesCode();
            });
        }
    }

    function highlightLinesElement(code, options, has_numbers) {
        for(var option of options) {
            --option.start;
            --option.end;
        }
        highlightLinesCode(code, options, has_numbers);
    }

    var highlightLinesCodeWithDeprecatedCalled = false;
    function highlightLinesCodeWithDeprecated(code, options, has_numbers) {
        if(!highlightLinesCodeWithDeprecatedCalled) {
            console.log('hljs.highlightLinesCode is deprecated. Please use hljs.highlightLinesElement')
            highlightLinesCodeWithDeprecatedCalled = true;
        }
        highlightLinesCode(code, options, has_numbers)
    }

    function highlightLinesCode(code, options, has_numbers) {
        function highlightLinesCodeWithoutNumbers() {
            code.innerHTML = code.innerHTML.replace(/([ \S]*\n|[ \S]*$)/gm, function(match) {
                    return '<div class="highlight-line">' + match + '</div>';
                    });

            if(options === undefined) {
                return;
            }

            var paddingLeft = parseInt(window.getComputedStyle(code).paddingLeft);
            var paddingRight = parseInt(window.getComputedStyle(code).paddingRight);

            var lines = code.getElementsByClassName('highlight-line');
            var scroll_width = code.scrollWidth;
            for(var option of options) {
                for(var j = option.start; j <= option.end; ++j) {
                    lines[j].style.backgroundColor = option.color;
                    lines[j].style.minWidth = scroll_width - paddingLeft - paddingRight + 'px';
                    // Add class to highlight-line element
                    lines[j].classList.add('highlighted-line');
                }
            }
        }
        function highlightLinesCodeWithNumbers() {
            var tables = code.getElementsByTagName('table');
            if(tables.length == 0) {
                if(count-- < 0) {
                    clearInterval(interval_id);
                    highlightLinesCodeWithoutNumbers();
                }
                return;
            }

            clearInterval(interval_id);

            var table = tables[0];
            table.style.width = '100%';
            var hljs_ln_numbers = table.getElementsByClassName('hljs-ln-numbers');
            for(var hljs_ln_number of hljs_ln_numbers) {
                hljs_ln_number.style.width = '2em';
            }

            if(options === undefined) {
                return;
            }
            var lines = code.getElementsByTagName('tr');
            for(var option of options) {
                for(var j = option.start; j <= option.end; ++j) {
                    lines[j].style.backgroundColor = option.color;
                    // Add class to tr element
                    lines[j].classList.add('highlighted-line');
                }
            }
        }

        if(hljs.hasOwnProperty('initLineNumbersOnLoad') && has_numbers !== false) {
            var count = 100;
            var interval_id = setInterval(highlightLinesCodeWithNumbers, 100);
            return;
        }

        highlightLinesCodeWithoutNumbers();
    }

    function unhighlightAllLines(code) {
        function unhighlightAllLinesWithoutNumbers() {
            var lines = code.getElementsByClassName('highlight-line');
            for (var i = 0; i < lines.length; ++i) {
                lines[i].style.backgroundColor = ''; // Reset background color
                // Remove class from highlight-line element
                lines[i].classList.remove('highlighted-line');
            }
        }

        function unhighlightAllLinesWithNumbers() {
            var lines = code.getElementsByTagName('tr');
            for (var i = 0; i < lines.length; ++i) {
                lines[i].style.backgroundColor = ''; // Reset background color
                // Remove class from tr element
                lines[i].classList.remove('highlighted-line');
            }
        }

        if (hljs.hasOwnProperty('initLineNumbersOnLoad') && code.getElementsByTagName('table').length > 0) {
            unhighlightAllLinesWithNumbers();
        } else {
            unhighlightAllLinesWithoutNumbers();
        }
    }

}(window, document));

!function(r,o){"use strict";var e,i="hljs-ln",l="hljs-ln-line",h="hljs-ln-code",s="hljs-ln-numbers",c="hljs-ln-n",m="data-line-number",a=/\r\n|\r|\n/g;function u(e){for(var n=e.toString(),t=e.anchorNode;"TD"!==t.nodeName;)t=t.parentNode;for(var r=e.focusNode;"TD"!==r.nodeName;)r=r.parentNode;var o=parseInt(t.dataset.lineNumber),a=parseInt(r.dataset.lineNumber);if(o==a)return n;var i,l=t.textContent,s=r.textContent;for(a<o&&(i=o,o=a,a=i,i=l,l=s,s=i);0!==n.indexOf(l);)l=l.slice(1);for(;-1===n.lastIndexOf(s);)s=s.slice(0,-1);for(var c=l,u=function(e){for(var n=e;"TABLE"!==n.nodeName;)n=n.parentNode;return n}(t),d=o+1;d<a;++d){var f=p('.{0}[{1}="{2}"]',[h,m,d]);c+="\n"+u.querySelector(f).textContent}return c+="\n"+s}function n(e){try{var n=o.querySelectorAll("code.hljs,code.nohighlight");for(var t in n)n.hasOwnProperty(t)&&(n[t].classList.contains("nohljsln")||d(n[t],e))}catch(e){r.console.error("LineNumbers error: ",e)}}function d(e,n){"object"==typeof e&&r.setTimeout(function(){e.innerHTML=f(e,n)},0)}function f(e,n){var t,r,o=(t=e,{singleLine:function(e){return!!e.singleLine&&e.singleLine}(r=(r=n)||{}),startFrom:function(e,n){var t=1;isFinite(n.startFrom)&&(t=n.startFrom);var r=function(e,n){return e.hasAttribute(n)?e.getAttribute(n):null}(e,"data-ln-start-from");return null!==r&&(t=function(e,n){if(!e)return n;var t=Number(e);return isFinite(t)?t:n}(r,1)),t}(t,r)});return function e(n){var t=n.childNodes;for(var r in t){var o;t.hasOwnProperty(r)&&(o=t[r],0<(o.textContent.trim().match(a)||[]).length&&(0<o.childNodes.length?e(o):v(o.parentNode)))}}(e),function(e,n){var t=g(e);""===t[t.length-1].trim()&&t.pop();if(1<t.length||n.singleLine){for(var r="",o=0,a=t.length;o<a;o++)r+=p('<tr><td class="{0} {1}" {3}="{5}"><div class="{2}" {3}="{5}"></div></td><td class="{0} {4}" {3}="{5}">{6}</td></tr>',[l,s,c,m,h,o+n.startFrom,0<t[o].length?t[o]:" "]);return p('<table class="{0}">{1}</table>',[i,r])}return e}(e.innerHTML,o)}function v(e){var n=e.className;if(/hljs-/.test(n)){for(var t=g(e.innerHTML),r=0,o="";r<t.length;r++){o+=p('<span class="{0}">{1}</span>\n',[n,0<t[r].length?t[r]:" "])}e.innerHTML=o.trim()}}function g(e){return 0===e.length?[]:e.split(a)}function p(e,t){return e.replace(/\{(\d+)\}/g,function(e,n){return void 0!==t[n]?t[n]:e})}r.hljs?(r.hljs.initLineNumbersOnLoad=function(e){"interactive"===o.readyState||"complete"===o.readyState?n(e):r.addEventListener("DOMContentLoaded",function(){n(e)})},r.hljs.lineNumbersBlock=d,r.hljs.lineNumbersValue=function(e,n){if("string"!=typeof e)return;var t=document.createElement("code");return t.innerHTML=e,f(t,n)},(e=o.createElement("style")).type="text/css",e.innerHTML=p(".{0}{border-collapse:collapse}.{0} td{padding:0}.{1}:before{content:attr({2})}",[i,c,m]),o.getElementsByTagName("head")[0].appendChild(e)):r.console.error("highlight.js not detected!"),document.addEventListener("copy",function(e){var n,t=window.getSelection();!function(e){for(var n=e;n;){if(n.className&&-1!==n.className.indexOf("hljs-ln-code"))return 1;n=n.parentNode}}(t.anchorNode)||(n=-1!==window.navigator.userAgent.indexOf("Edge")?u(t):t.toString(),e.clipboardData.setData("text/plain",n),e.preventDefault())})}(window,document);
hljs.registerLanguage("robots-txt",(function(e){var s=e.COMMENT("#","$");return{aliases:["robotstxt","robots.txt"],case_insensitive:!0,lexemes:"[a-z-]+",keywords:{section:"user-agent",built_in:"allow disallow",keyword:"crawl-delay sitemap"},contains:[s,e.NUMBER_MODE,{className:"string",begin:"^\\s*(?:user-agent|(?:dis)?allow)\\s*:\\s*",end:/$/,excludeBegin:!0,relevance:10,contains:[s]},{className:"string",begin:"^\\s*sitemap\\s*:\\s*",end:/$/,excludeBegin:!0,contains:[s]}],illegal:"<(?:!DOCTYPE\\s+)?html>"}}));
!function(){"use strict";var e={n:function(t){var n=t&&t.__esModule?function(){return t.default}:function(){return t};return e.d(n,{a:n}),n},d:function(t,n){for(var r in n)e.o(n,r)&&!e.o(t,r)&&Object.defineProperty(t,r,{enumerable:!0,get:n[r]})},o:function(e,t){return Object.prototype.hasOwnProperty.call(e,t)}},t=jQuery,n=e.n(t),r=wp.element;function l(e,t){return function(e){if(Array.isArray(e))return e}(e)||function(e,t){var n=null==e?null:"undefined"!=typeof Symbol&&e[Symbol.iterator]||e["@@iterator"];if(null!=n){var r,l,o,a,i=[],u=!0,c=!1;try{if(o=(n=n.call(e)).next,0===t){if(Object(n)!==n)return;u=!1}else for(;!(u=(r=o.call(n)).done)&&(i.push(r.value),i.length!==t);u=!0);}catch(e){c=!0,l=e}finally{try{if(!u&&null!=n.return&&(a=n.return(),Object(a)!==a))return}finally{if(c)throw l}}return i}}(e,t)||a(e,t)||function(){throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}()}function o(e,t){var n="undefined"!=typeof Symbol&&e[Symbol.iterator]||e["@@iterator"];if(!n){if(Array.isArray(e)||(n=a(e))||t&&e&&"number"==typeof e.length){n&&(e=n);var r=0,l=function(){};return{s:l,n:function(){return r>=e.length?{done:!0}:{done:!1,value:e[r++]}},e:function(e){throw e},f:l}}throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}var o,i=!0,u=!1;return{s:function(){n=n.call(e)},n:function(){var e=n.next();return i=e.done,e},e:function(e){u=!0,o=e},f:function(){try{i||null==n.return||n.return()}finally{if(u)throw o}}}}function a(e,t){if(e){if("string"==typeof e)return i(e,t);var n={}.toString.call(e).slice(8,-1);return"Object"===n&&e.constructor&&(n=e.constructor.name),"Map"===n||"Set"===n?Array.from(e):"Arguments"===n||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)?i(e,t):void 0}}function i(e,t){(null==t||t>e.length)&&(t=e.length);for(var n=0,r=Array(t);n<t;n++)r[n]=e[n];return r}var u=function(e){e.startsWith("http://")||e.startsWith("https://")||(e="https://"+e);var t=e.indexOf("/",8);return-1!==t&&(e=e.substring(0,t)),"/"!==e[e.length-1]&&(e+="/"),e},c=function(e){for(var t=[],n=e.split("\n"),r=null,l=[],o=0;o<n.length;o++){var a=n[o].trim();if(""!==a&&!a.startsWith("#")){var i=a.match(/^User-agent:\s*(.*)/i);if(i)r=i[1].trim(),l.includes(r)||l.push(r);else{var u=a.match(/^(Allow|Disallow):\s*(.*)/i);u&&r&&t.push({userAgent:r,type:u[1].toLowerCase(),path:u[2],line:o+1})}}}return{rules:t,userAgents:l}},s=function(e){var t,n=[],r=new Map,a=o(e);try{for(a.s();!(t=a.n()).done;){var i=t.value,u="".concat(i.userAgent,":").concat(i.path);r.has(u)||r.set(u,[]),r.get(u).push(i)}}catch(e){a.e(e)}finally{a.f()}var c,s=o(r);try{for(s.s();!(c=s.n()).done;){var f=l(c.value,2),m=f[0],d=f[1];if(d.length>1){var p=d.filter(function(e){return"allow"===e.type}),v=d.filter(function(e){return"disallow"===e.type});p.length>0&&v.length>0&&n.push({key:m,ruleSet:d})}}}catch(e){s.e(e)}finally{s.f()}return n},f=function(e){var t,n=["user-agent","allow","disallow","crawl-delay","sitemap"],r=e.split("\n"),l=[],a=o(r);try{for(a.s();!(t=a.n()).done;){var i=t.value,u=i.trim().toLowerCase();if(u&&!u.startsWith("#")){var c=u.split(":")[0];n.includes(c)||l.push({line:i.trim(),lineNumber:r.indexOf(i)+1})}}}catch(e){a.e(e)}finally{a.f()}return l},m=function(e,t,n,r){var l=new URL(e,r).pathname,a=!0,i=null,u=-1,c=t;n&&(c=t.filter(function(e){return e.userAgent===n||"*"===e.userAgent}));var s,f=o(c);try{for(f.s();!(s=f.n()).done;){var m=s.value;d(l,m.path)&&(m.path.length>u?(u=m.path.length,a="allow"===m.type,i=m):m.path.length===u&&"allow"===m.type&&(a=!0,i=m))}}catch(e){f.e(e)}finally{f.f()}return{allowed:a,line:i?i.line:null,userAgent:i?i.userAgent:null}},d=function(e,t){return new RegExp("^"+t.replace(/\*/g,".*").replace(/\?/g,"\\?").replace(/\$/g,"\\$")+(t.endsWith("$")?"$":"")).test(e)},p=function(e){var t=arguments.length>1&&void 0!==arguments[1]?arguments[1]:"#ffcccc";window.hljs.highlightLinesAll([[{start:e,end:e,color:t}]])},v=function(e){var t=n()(e)[0];t&&t.scrollIntoView({behavior:"smooth",block:"center"})};function h(e,t){var n="undefined"!=typeof Symbol&&e[Symbol.iterator]||e["@@iterator"];if(!n){if(Array.isArray(e)||(n=function(e,t){if(e){if("string"==typeof e)return y(e,t);var n={}.toString.call(e).slice(8,-1);return"Object"===n&&e.constructor&&(n=e.constructor.name),"Map"===n||"Set"===n?Array.from(e):"Arguments"===n||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)?y(e,t):void 0}}(e))||t&&e&&"number"==typeof e.length){n&&(e=n);var r=0,l=function(){};return{s:l,n:function(){return r>=e.length?{done:!0}:{done:!1,value:e[r++]}},e:function(e){throw e},f:l}}throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}var o,a=!0,i=!1;return{s:function(){n=n.call(e)},n:function(){var e=n.next();return a=e.done,e},e:function(e){i=!0,o=e},f:function(){try{a||null==n.return||n.return()}finally{if(i)throw o}}}}function y(e,t){(null==t||t>e.length)&&(t=e.length);for(var n=0,r=Array(t);n<t;n++)r[n]=e[n];return r}var b=function(e){var t=e.state,n=t.content,l=t.editedContent,o=t.unknownRules,a=t.contradictions,i=t.testResult,u=l!==n?l:n,c=(0,r.useRef)(null);return(0,r.useEffect)(function(){c.current&&u&&setTimeout(function(){delete c.current.dataset.highlighted,hljs.highlightElement(c.current),hljs.lineNumbersBlock(c.current)},0)},[u]),(0,r.useEffect)(function(){if(c.current){if(c.current.querySelectorAll(".highlighted-line").forEach(function(e){e.classList.remove("highlighted-line"),e.removeAttribute("style")}),o.length){var e,t=h(o);try{var n=function(){var t=e.value;setTimeout(function(){p(t.lineNumber)},0)};for(t.s();!(e=t.n()).done;)n()}catch(e){t.e(e)}finally{t.f()}}if(a.length){var r,l=h(a);try{for(l.s();!(r=l.n()).done;){var u,s=h(r.value.ruleSet);try{var f=function(){var e=u.value;setTimeout(function(){p(e.line)},0)};for(s.s();!(u=s.n()).done;)f()}catch(e){s.e(e)}finally{s.f()}}}catch(e){l.e(e)}finally{l.f()}}var m=(null==i?void 0:i.line)||"";m&&p(m,"#ccd4ff"),setTimeout(function(){v(m?c.current.querySelector('.hljs-ln-line[data-line-number="'+m+'"]'):c.current.querySelector(".highlighted-line"))},150)}},[o,a,i]),wp.element.createElement("pre",{ref:c,id:"robots-txt-content",className:"language-robots-txt"},u)};function g(e,t){return function(e){if(Array.isArray(e))return e}(e)||function(e,t){var n=null==e?null:"undefined"!=typeof Symbol&&e[Symbol.iterator]||e["@@iterator"];if(null!=n){var r,l,o,a,i=[],u=!0,c=!1;try{if(o=(n=n.call(e)).next,0===t){if(Object(n)!==n)return;u=!1}else for(;!(u=(r=o.call(n)).done)&&(i.push(r.value),i.length!==t);u=!0);}catch(e){c=!0,l=e}finally{try{if(!u&&null!=n.return&&(a=n.return(),Object(a)!==a))return}finally{if(c)throw l}}return i}}(e,t)||function(e,t){if(e){if("string"==typeof e)return w(e,t);var n={}.toString.call(e).slice(8,-1);return"Object"===n&&e.constructor&&(n=e.constructor.name),"Map"===n||"Set"===n?Array.from(e):"Arguments"===n||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)?w(e,t):void 0}}(e,t)||function(){throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}()}function w(e,t){(null==t||t>e.length)&&(t=e.length);for(var n=0,r=Array(t);n<t;n++)r[n]=e[n];return r}var E=function(e){var t=e.items,n=e.title,l=e.item,o=g((0,r.useState)(!0),2),a=o[0],i=o[1];return wp.element.createElement("div",{className:"rule ".concat(t.length>0?"rule--warning":"rule--ok")},wp.element.createElement("div",{className:"rule__title",role:"button",onClick:function(){t.length>0&&i(!a)}},t.length>0?wp.element.createElement("span",{className:"dashicons dashicons-no-alt"}):wp.element.createElement("span",{className:"dashicons dashicons-saved"}),"function"==typeof n?n(e):n,t.length>0?wp.element.createElement("span",{className:a?"arrow-up":"arrow-down"}):null),a&&t.length>0&&wp.element.createElement("div",{className:"rule__items"},t.map(function(e,t){return wp.element.createElement("div",{className:"rule__item",key:t},l&&"function"==typeof l?l(e,t):e)})))},S=["onClick","children","variant","className"];function A(){return A=Object.assign?Object.assign.bind():function(e){for(var t=1;t<arguments.length;t++){var n=arguments[t];for(var r in n)({}).hasOwnProperty.call(n,r)&&(e[r]=n[r])}return e},A.apply(null,arguments)}var k=function(e){var t=e.onClick,n=e.children,r=e.variant,l=e.className,o=void 0===l?"":l,a=function(e,t){if(null==e)return{};var n,r,l=function(e,t){if(null==e)return{};var n={};for(var r in e)if({}.hasOwnProperty.call(e,r)){if(-1!==t.indexOf(r))continue;n[r]=e[r]}return n}(e,t);if(Object.getOwnPropertySymbols){var o=Object.getOwnPropertySymbols(e);for(r=0;r<o.length;r++)n=o[r],-1===t.indexOf(n)&&{}.propertyIsEnumerable.call(e,n)&&(l[n]=e[n])}return l}(e,S);return wp.element.createElement("button",A({type:"button"},a,{className:"button ".concat(o," ").concat(r?"button--".concat(r):""),onClick:t}),n)},C=function(e){var t=e.number;return wp.element.createElement("a",{href:"#",onClick:function(e){e.preventDefault(),v('.hljs-ln-line[data-line-number="'+t+'"]')}},t)},O=function(e){var t=e.className,n=e.placeholder,r=e.value,l=e.disabled,o=e.onChange,a=e.onKeyDown;return wp.element.createElement("input",{type:"text",className:t,placeholder:n,value:r,disabled:l,onChange:o,onKeyDown:a})};function j(e){return function(e){if(Array.isArray(e))return R(e)}(e)||function(e){if("undefined"!=typeof Symbol&&null!=e[Symbol.iterator]||null!=e["@@iterator"])return Array.from(e)}(e)||x(e)||function(){throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}()}function N(e,t){return function(e){if(Array.isArray(e))return e}(e)||function(e,t){var n=null==e?null:"undefined"!=typeof Symbol&&e[Symbol.iterator]||e["@@iterator"];if(null!=n){var r,l,o,a,i=[],u=!0,c=!1;try{if(o=(n=n.call(e)).next,0===t){if(Object(n)!==n)return;u=!1}else for(;!(u=(r=o.call(n)).done)&&(i.push(r.value),i.length!==t);u=!0);}catch(e){c=!0,l=e}finally{try{if(!u&&null!=n.return&&(a=n.return(),Object(a)!==a))return}finally{if(c)throw l}}return i}}(e,t)||x(e,t)||function(){throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}()}function x(e,t){if(e){if("string"==typeof e)return R(e,t);var n={}.toString.call(e).slice(8,-1);return"Object"===n&&e.constructor&&(n=e.constructor.name),"Map"===n||"Set"===n?Array.from(e):"Arguments"===n||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)?R(e,t):void 0}}function R(e,t){(null==t||t>e.length)&&(t=e.length);for(var n=0,r=Array(t);n<t;n++)r[n]=e[n];return r}var U=function(e){var t=e.disabled,n=e.value,l=e.userAgents,o=e.onChange,a=e.isFooter,i=e.haveContent,u=N((0,r.useState)(!1),2),c=u[0],s=u[1],f=(0,r.useRef)(null);l=["*","Googlebot","Bingbot","Yandex","DuckDuckBot","Baiduspider","Slurp","Sogou","Exabot","ia_archiver","AhrefsBot","MJ12bot","SemrushBot","SEOkicks-Robot","BLEXBot","DotBot","rogerbot","SeznamBot","Screaming Frog","SiteCheckerBot","UptimeRobot","Wotbox"].concat(j(l)),l=j(new Set(l));var m=N((0,r.useState)([]),2),d=m[0],p=m[1],v=d.length?d:l;return(0,r.useEffect)(function(){if(f.current){var e=function(e){var t=e.target;t&&f.current&&!f.current.contains(t)&&s(!1)};return document.addEventListener("mousedown",e),function(){document.removeEventListener("mousedown",e)}}},[]),wp.element.createElement("div",{className:"select-container ".concat(c?"dropdown-open":""),ref:f},wp.element.createElement("input",{disabled:a?!i:t,type:"text",id:"user-agent",placeholder:"Select User Agent",list:"user-agents",autoComplete:"off",value:n,onChange:function(e){var t,n=null==e||null===(t=e.target)||void 0===t?void 0:t.value;p(l.filter(function(e){return e.toLowerCase().includes(n.toLowerCase())})),o(n)},onFocus:function(){return s(!0)},readOnly:!0}),wp.element.createElement("div",{role:"listbox",tabIndex:"0",id:"user-agent-dropdown"},v.map(function(e,t){return wp.element.createElement("div",{role:"option",tabIndex:"-1","aria-selected":e===n,key:d.length?"filtered-"+t:"agent-"+t,className:"user-agent-option",onClick:function(){s(!1),o(e)},onKeyDown:function(){s(!1),o(e)}},e)})))};function T(e,t){return function(e){if(Array.isArray(e))return e}(e)||function(e,t){var n=null==e?null:"undefined"!=typeof Symbol&&e[Symbol.iterator]||e["@@iterator"];if(null!=n){var r,l,o,a,i=[],u=!0,c=!1;try{if(o=(n=n.call(e)).next,0===t){if(Object(n)!==n)return;u=!1}else for(;!(u=(r=o.call(n)).done)&&(i.push(r.value),i.length!==t);u=!0);}catch(e){c=!0,l=e}finally{try{if(!u&&null!=n.return&&(a=n.return(),Object(a)!==a))return}finally{if(c)throw l}}return i}}(e,t)||function(e,t){if(e){if("string"==typeof e)return I(e,t);var n={}.toString.call(e).slice(8,-1);return"Object"===n&&e.constructor&&(n=e.constructor.name),"Map"===n||"Set"===n?Array.from(e):"Arguments"===n||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)?I(e,t):void 0}}(e,t)||function(){throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}()}function I(e,t){(null==t||t>e.length)&&(t=e.length);for(var n=0,r=Array(t);n<t;n++)r[n]=e[n];return r}var L=function(e){var t=e.state,n=e.haveContent,l=e.updateState,o=e.fetchRobotsTxt,a=e.testUrl,i=e.testResult,u=void 0===i?{}:i,c=e.isFooter,s=void 0!==c&&c,f=e.isLocal,m=void 0!==f&&f,d=t.siteUrl,p=t.isChecking,v=t.checkUrl,h=t.userAgent,y=t.userAgents,b=T((0,r.useState)(""),2),g=b[0],w=b[1],E=Object.keys(u).length>1;return(0,r.useEffect)(function(){if(E){w(u.allowed?"is-allowed":"is-disallowed");var e=setTimeout(function(){w("")},3e3);return function(){clearTimeout(e)}}},[u]),wp.element.createElement("div",{className:"search-robots ".concat(p?"rtt-tool--loading":"")},wp.element.createElement("span",{className:"input-container"},wp.element.createElement(O,{placeholder:"Enter URL",className:"site-url ".concat(s||m?"disabled":""),value:d,disabled:p||m?"disabled":"",onChange:function(e){var t;if(!m){var n=(null==e||null===(t=e.target)||void 0===t||null===(t=t.value)||void 0===t?void 0:t.trim())||"";l({siteUrl:n})}},onKeyDown:function(e){if(!m&&"Enter"===e.key){var t,n=(null==e||null===(t=e.target)||void 0===t||null===(t=t.value)||void 0===t?void 0:t.trim())||"";n?(l({siteUrl:n}),o(e)):window.alert(ERRORS.NO_URL)}}}),s&&!m&&wp.element.createElement(O,{className:"check-url",type:"text",placeholder:"Check URL",value:v,onChange:function(e){return l({checkUrl:e.target.value})},onKeyDown:function(e){"Enter"===e.key&&l({checkUrl:e.target.value},function(){a()})},disabled:!n})),wp.element.createElement(U,{isFooter:s,haveContent:n,disabled:!1,value:h,userAgents:y,onChange:function(e){l({userAgent:e,testResult:{}})}}),s?wp.element.createElement(k,{className:"test-button ".concat(E?g:""),onClick:a,disabled:!n}):wp.element.createElement(k,{onClick:o},"Test"))};function D(e){return D="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e},D(e)}function P(e,t){return function(e){if(Array.isArray(e))return e}(e)||function(e,t){var n=null==e?null:"undefined"!=typeof Symbol&&e[Symbol.iterator]||e["@@iterator"];if(null!=n){var r,l,o,a,i=[],u=!0,c=!1;try{if(o=(n=n.call(e)).next,0===t){if(Object(n)!==n)return;u=!1}else for(;!(u=(r=o.call(n)).done)&&(i.push(r.value),i.length!==t);u=!0);}catch(e){c=!0,l=e}finally{try{if(!u&&null!=n.return&&(a=n.return(),Object(a)!==a))return}finally{if(c)throw l}}return i}}(e,t)||function(e,t){if(e){if("string"==typeof e)return _(e,t);var n={}.toString.call(e).slice(8,-1);return"Object"===n&&e.constructor&&(n=e.constructor.name),"Map"===n||"Set"===n?Array.from(e):"Arguments"===n||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)?_(e,t):void 0}}(e,t)||function(){throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}()}function _(e,t){(null==t||t>e.length)&&(t=e.length);for(var n=0,r=Array(t);n<t;n++)r[n]=e[n];return r}function F(e,t){var n=Object.keys(e);if(Object.getOwnPropertySymbols){var r=Object.getOwnPropertySymbols(e);t&&(r=r.filter(function(t){return Object.getOwnPropertyDescriptor(e,t).enumerable})),n.push.apply(n,r)}return n}function B(e){for(var t=1;t<arguments.length;t++){var n=null!=arguments[t]?arguments[t]:{};t%2?F(Object(n),!0).forEach(function(t){M(e,t,n[t])}):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(n)):F(Object(n)).forEach(function(t){Object.defineProperty(e,t,Object.getOwnPropertyDescriptor(n,t))})}return e}function M(e,t,n){return(t=function(e){var t=function(e,t){if("object"!=D(e)||!e)return e;var n=e[Symbol.toPrimitive];if(void 0!==n){var r=n.call(e,t||"default");if("object"!=D(r))return r;throw new TypeError("@@toPrimitive must return a primitive value.")}return("string"===t?String:Number)(e)}(e,"string");return"symbol"==D(t)?t:t+""}(t))in e?Object.defineProperty(e,t,{value:n,enumerable:!0,configurable:!0,writable:!0}):e[t]=n,e}var $="Please enter a valid URL",q=function(e){var t=e.ajaxUrl,l=e.ajaxNonce,o=e.siteUrl,a=e.editorDisabled,i=void 0!==a&&a,d=e.editorName,p=void 0===d?"":d,v=e.isLocal,h=void 0!==v&&v,y=e.robotsTxtContent,g=void 0===y?"":y,w=e.onChange,S=void 0===w?null:w,A=B({},{isChecking:!1,originalContent:"",content:"",editedContent:"",siteUrl:o||"",unknownRules:[],contradictions:[],robotsTxtRules:[],lastDate:"",responseCode:"",fileSize:"",mode:"live",userAgent:"",userAgents:[],checkUrl:"",testResult:{}});if(g){var O=c(g);A.originalContent=g,A.content=g,A.editedContent=g,A.unknownRules=f(g),A.robotsTxtRules=O.rules,A.userAgents=O.userAgents,A.contradictions=s(O.rules)}var j=P((0,r.useState)(A),2),N=j[0],x=j[1],R=(N.isChecking,N.originalContent),U=N.content,T=N.editedContent,I=N.siteUrl,D=N.unknownRules,_=N.contradictions,F=N.robotsTxtRules,M=N.lastDate,q=N.responseCode,z=N.fileSize,K=N.mode,V=N.userAgent,W=N.checkUrl,J=N.testResult,Y=(N.userAgents,function(e,t){x(B(B({},N),e)),t&&t()}),G=function(){return T!==U},H=function(){return U&&U.length},Q=function(){return"live"===K},X=function(){return"editor"===K},Z=null!=J&&J.allowed?"is-allowed":"is-disallowed",ee=function(e){if(e&&e.preventDefault(),I){var r=u(I);Y({isChecking:!0,siteUrl:r}),n().ajax({url:t,type:"POST",dataType:"json",data:{action:"rank_math_rtt_check",security:l,site_url:r},success:function(e){if(e.success){var t=e.data.content,n=e.data.timestamp,l=e.data.code,o=e.data.size,a=c(t);Y({siteUrl:r,isChecking:!1,originalContent:t,content:t,editedContent:t,unknownRules:f(t),robotsTxtRules:a.rules,userAgents:a.userAgents,contradictions:s(a.rules),lastDate:new Date(1e3*n).toLocaleString("en-US",{year:"numeric",month:"2-digit",day:"2-digit",hour:"2-digit",minute:"2-digit",hour12:!0}),responseCode:l,fileSize:o+" bytes"}),setTimeout(function(){var e;null===(e=document.querySelector(".test-results"))||void 0===e||e.scrollIntoView({behavior:"smooth"})},100)}else Y({isChecking:!1,content:""}),window.alert(e.data.message)},error:function(e){var t;Y({isChecking:!1}),window.alert((null==e||null===(t=e.responseJSON)||void 0===t||null===(t=t.data)||void 0===t?void 0:t.message)||"An error occurred")}})}else window.alert($)},te=function(e){i&&"editor"===e||Y({mode:e})},ne=function(){var e=T,t=c(e);Y({mode:"live",editedContent:e,unknownRules:f(e),robotsTxtRules:t.rules,userAgents:t.userAgents,contradictions:s(t.rules),testResult:{},checkUrl:""})},re={state:N,updateState:Y,fetchRobotsTxt:ee,testUrl:function(){Y({testResult:m(W,F,V,u(I))})},haveContent:H(),isLocal:h};return(0,r.useEffect)(function(){!function(e){var t=document.querySelector(".robots-txt-editor"),n=document.querySelector(".numbers");if(t&&n&&e()){var r=0,l=function(){var e=t.value.split("\n").length;e!==r&&(n.innerHTML=Array.from({length:e},function(e,t){return"<span>".concat(t+1,"</span>")}).join(""),r=e)},o=function(){n.style.transform="translateY(-".concat(t.scrollTop,"px)")};l(),o(),t.addEventListener("input",l),t.addEventListener("scroll",o)}}(H)},[Q()]),(0,r.useEffect)(function(){g||o&&""!==o.trim()&&!h&&ee(),i&&"editor"===K&&te("live")},[]),wp.element.createElement("div",{id:"rtt-tool"},!h&&wp.element.createElement(L,re),H()&&wp.element.createElement("section",{className:"test-results"},wp.element.createElement("div",{className:"container"},!h&&wp.element.createElement(React.Fragment,null,wp.element.createElement("h4",{className:"section-title"},"Test Results"),wp.element.createElement("p",{className:"section-text"},"Edit your robots.txt and check for errors."," ",wp.element.createElement("a",{href:"https://rankmath.com/kb/how-to-edit-robots-txt-with-rank-math/",target:"_blank",rel:"noreferrer"},"Learn more"),".")),wp.element.createElement("div",{className:"tester"},wp.element.createElement("header",{className:"tester-header"},wp.element.createElement("div",{className:"title"},!h&&wp.element.createElement("button",{className:"refresh-robots-txt",title:"Refresh robots.txt",onClick:ee,disabled:!H()||h},wp.element.createElement("i",{className:"dashicons dashicons-update"})),wp.element.createElement("button",{className:"download-robots-txt",title:"Download robots.txt",onClick:function(e){e.preventDefault(),function(){var e=arguments.length>0&&void 0!==arguments[0]?arguments[0]:"",t=arguments.length>1&&void 0!==arguments[1]?arguments[1]:"robots.txt";if(e){var n=new Blob([e],{type:"text/plain"}),r=URL.createObjectURL(n),l=document.createElement("a");l.href=r,l.download=t,l.click(),l.remove()}}(T)},disabled:!H()},wp.element.createElement("i",{className:"dashicons dashicons-download"})),wp.element.createElement("h1",null,G()?"Edited Version":wp.element.createElement("a",{href:u(I)+"robots.txt",target:"_blank",title:"Latest Version",rel:"noreferrer",className:"".concat(!H()||h?"disabled":"")},"Latest Version")),!G()&&H()&&!h&&wp.element.createElement(React.Fragment,null,wp.element.createElement("span",null,"•"),wp.element.createElement("p",null,M),wp.element.createElement("span",null,"•"),wp.element.createElement("p",{className:"response-status ".concat(200===q?"ok":"warning")},200===q?"OK (200)":q),wp.element.createElement("span",null,"•"),wp.element.createElement("p",{className:"file-size"},z," "))),!i&&wp.element.createElement("div",{className:"options"},wp.element.createElement("span",null,wp.element.createElement("input",{type:"radio",id:"is-live",name:"model",value:"live",checked:Q()?"checked":"",onChange:function(){te("live"),ne()}}),wp.element.createElement("label",{htmlFor:"is-live"},"Robots.txt tester")),wp.element.createElement("span",null,wp.element.createElement("input",{type:"radio",id:"is-editor",name:"model",value:"editor",checked:X()?"checked":"",onChange:function(){return te("editor")}}),wp.element.createElement("label",{htmlFor:"is-editor"},"Editor")))),wp.element.createElement("main",{className:"tester-main ".concat(Object.keys(J).length>1?Z:"")},Q()?wp.element.createElement(b,{state:N}):wp.element.createElement(React.Fragment,null,wp.element.createElement("div",{className:"line-numbers"},wp.element.createElement("div",{className:"numbers"})),wp.element.createElement("textarea",{value:T,className:"robots-txt-editor",onChange:function(e){var t=e.target.value,n=c(t);Y({editedContent:t,robotsTxtRules:n.rules,userAgents:n.userAgents}),null!==S&&S(t)},disabled:i,name:p}))),wp.element.createElement("footer",{className:"tester-footer ".concat(X()?"editor-footer":"live-footer")},Q()?wp.element.createElement(React.Fragment,null,H()&&wp.element.createElement("div",{className:"results-summary"},wp.element.createElement(E,{items:D,title:function(e){var t=e.items;return t.length>0?"".concat(t.length," unknown rules found."):"No unknown rules found."},item:function(e){return wp.element.createElement(React.Fragment,null,"Line ",wp.element.createElement(C,{number:e.lineNumber}),":"," ",e.line)}}),wp.element.createElement(E,{items:_,title:function(e){var t=e.items;return t.length>0?"".concat(t.length," contradicting or unclear rules found."):"No contradicting or unclear rules found."},item:function(e,t){var n=e.key,l=e.ruleSet;return wp.element.createElement(React.Fragment,null,t+1,". Conflicting rules for ",n," -"," ",l.map(function(e,t){var n=e.type,l=e.line;return wp.element.createElement(r.Fragment,{key:t},n," (line ",wp.element.createElement(C,{number:l}),")")}).reduce(function(e,t){return[e,", ",t]}))}})),wp.element.createElement(L,B(B({},re),{},{testResult:J,isFooter:!0}))):wp.element.createElement("div",{className:"button-group"},wp.element.createElement(k,{variant:"secondary",onClick:function(e){Y({unknownRules:[],contradictions:[],content:R,editedContent:R,mode:"live"}),null!==S&&S(R)},disabled:!H()},"Revert Changes"),wp.element.createElement(k,{onClick:ne,disabled:!H()},"Validate Code")))))))};window.RttTool=q}();