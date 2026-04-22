var __defProp = Object.defineProperty;
var __defNormalProp = (obj, key, value) => key in obj ? __defProp(obj, key, { enumerable: true, configurable: true, writable: true, value }) : obj[key] = value;
var __publicField = (obj, key, value) => __defNormalProp(obj, typeof key !== "symbol" ? key + "" : key, value);
import { effectScope, computed, watch, isRef, getCurrentInstance, ref, shallowRef, defineComponent, h, Fragment, inject, onMounted, onUnmounted, createVNode, Text, readonly, openBlock, createElementBlock, normalizeClass, renderList, unref, createElementVNode, toDisplayString as toDisplayString$1, createCommentVNode, normalizeStyle, withModifiers, createBlock, reactive, resolveComponent, createStaticVNode, withCtx, createTextVNode, TransitionGroup, resolveDynamicComponent, normalizeProps, guardReactiveProps, onBeforeUpdate, nextTick, provide, watchEffect, createSlots, markRaw, compile as compile$1, createApp } from "vue";
/*!
  * shared v10.0.8
  * (c) 2025 kazuya kawaguchi
  * Released under the MIT License.
  */
function warn$1(msg, err) {
  if (typeof console !== "undefined") {
    console.warn(`[intlify] ` + msg);
  }
}
const hasWarned$1 = {};
function warnOnce$1(msg) {
  if (!hasWarned$1[msg]) {
    hasWarned$1[msg] = true;
    warn$1(msg);
  }
}
const inBrowser$1 = typeof window !== "undefined";
const RE_ARGS$1 = /\{([0-9a-zA-Z]+)\}/g;
function format$2(message, ...args) {
  if (args.length === 1 && isObject$1(args[0])) {
    args = args[0];
  }
  if (!args || !args.hasOwnProperty) {
    args = {};
  }
  return message.replace(RE_ARGS$1, (match, identifier) => {
    return args.hasOwnProperty(identifier) ? args[identifier] : "";
  });
}
const makeSymbol = (name, shareable = false) => !shareable ? Symbol(name) : Symbol.for(name);
const isNumber$1 = (val) => typeof val === "number" && isFinite(val);
const isRegExp$1 = (val) => toTypeString$1(val) === "[object RegExp]";
const isEmptyObject$1 = (val) => isPlainObject$9(val) && Object.keys(val).length === 0;
const assign$1 = Object.assign;
const _create$1 = Object.create;
const create$1 = (obj = null) => _create$1(obj);
let _globalThis$1;
const getGlobalThis$1 = () => {
  return _globalThis$1 || (_globalThis$1 = typeof globalThis !== "undefined" ? globalThis : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : typeof global !== "undefined" ? global : create$1());
};
const hasOwnProperty$1 = Object.prototype.hasOwnProperty;
function hasOwn$1(obj, key) {
  return hasOwnProperty$1.call(obj, key);
}
const isArray$1 = Array.isArray;
const isFunction$1 = (val) => typeof val === "function";
const isString$1 = (val) => typeof val === "string";
const isBoolean$1 = (val) => typeof val === "boolean";
const isObject$1 = (val) => val !== null && typeof val === "object";
const objectToString$1 = Object.prototype.toString;
const toTypeString$1 = (value) => objectToString$1.call(value);
const isPlainObject$9 = (val) => toTypeString$1(val) === "[object Object]";
function createEmitter() {
  const events = /* @__PURE__ */ new Map();
  const emitter = {
    events,
    on(event, handler) {
      const handlers = events.get(event);
      const added = handlers && handlers.push(handler);
      if (!added) {
        events.set(event, [handler]);
      }
    },
    off(event, handler) {
      const handlers = events.get(event);
      if (handlers) {
        handlers.splice(handlers.indexOf(handler) >>> 0, 1);
      }
    },
    emit(event, payload) {
      (events.get(event) || []).slice().map((handler) => handler(payload));
      (events.get("*") || []).slice().map((handler) => handler(event, payload));
    }
  };
  return emitter;
}
const isNotObjectOrIsArray = (val) => !isObject$1(val) || isArray$1(val);
function deepCopy(src, des) {
  if (isNotObjectOrIsArray(src) || isNotObjectOrIsArray(des)) {
    throw new Error("Invalid value");
  }
  const stack = [{ src, des }];
  while (stack.length) {
    const { src: src2, des: des2 } = stack.pop();
    Object.keys(src2).forEach((key) => {
      if (key === "__proto__") {
        return;
      }
      if (isObject$1(src2[key]) && !isObject$1(des2[key])) {
        des2[key] = Array.isArray(src2[key]) ? [] : create$1();
      }
      if (isNotObjectOrIsArray(des2[key]) || isNotObjectOrIsArray(src2[key])) {
        des2[key] = src2[key];
      } else {
        stack.push({ src: src2[key], des: des2[key] });
      }
    });
  }
}
/*!
  * shared v10.0.8
  * (c) 2025 kazuya kawaguchi
  * Released under the MIT License.
  */
function warn(msg, err) {
  if (typeof console !== "undefined") {
    console.warn(`[intlify] ` + msg);
    if (err) {
      console.warn(err.stack);
    }
  }
}
const hasWarned = {};
function warnOnce(msg) {
  if (!hasWarned[msg]) {
    hasWarned[msg] = true;
    warn(msg);
  }
}
const inBrowser = typeof window !== "undefined";
let mark;
let measure;
{
  const perf2 = inBrowser && window.performance;
  if (perf2 && perf2.mark && perf2.measure && perf2.clearMarks && // @ts-ignore browser compat
  perf2.clearMeasures) {
    mark = (tag) => {
      perf2.mark(tag);
    };
    measure = (name, startTag, endTag) => {
      perf2.measure(name, startTag, endTag);
      perf2.clearMarks(startTag);
      perf2.clearMarks(endTag);
    };
  }
}
const RE_ARGS = /\{([0-9a-zA-Z]+)\}/g;
function format$1(message, ...args) {
  if (args.length === 1 && isObject(args[0])) {
    args = args[0];
  }
  if (!args || !args.hasOwnProperty) {
    args = {};
  }
  return message.replace(RE_ARGS, (match, identifier) => {
    return args.hasOwnProperty(identifier) ? args[identifier] : "";
  });
}
const generateFormatCacheKey = (locale, key, source) => friendlyJSONstringify({ l: locale, k: key, s: source });
const friendlyJSONstringify = (json) => JSON.stringify(json).replace(/\u2028/g, "\\u2028").replace(/\u2029/g, "\\u2029").replace(/\u0027/g, "\\u0027");
const isNumber = (val) => typeof val === "number" && isFinite(val);
const isDate = (val) => toTypeString(val) === "[object Date]";
const isRegExp = (val) => toTypeString(val) === "[object RegExp]";
const isEmptyObject = (val) => isPlainObject$8(val) && Object.keys(val).length === 0;
const assign = Object.assign;
const _create = Object.create;
const create = (obj = null) => _create(obj);
let _globalThis;
const getGlobalThis = () => {
  return _globalThis || (_globalThis = typeof globalThis !== "undefined" ? globalThis : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : typeof global !== "undefined" ? global : create());
};
function escapeHtml(rawText) {
  return rawText.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&apos;").replace(/\//g, "&#x2F;").replace(/=/g, "&#x3D;");
}
function escapeAttributeValue(value) {
  return value.replace(/&(?![a-zA-Z0-9#]{2,6};)/g, "&amp;").replace(/"/g, "&quot;").replace(/'/g, "&apos;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
}
function sanitizeTranslatedHtml(html) {
  html = html.replace(/(\w+)\s*=\s*"([^"]*)"/g, (_, attrName, attrValue) => `${attrName}="${escapeAttributeValue(attrValue)}"`);
  html = html.replace(/(\w+)\s*=\s*'([^']*)'/g, (_, attrName, attrValue) => `${attrName}='${escapeAttributeValue(attrValue)}'`);
  const eventHandlerPattern = /\s*on\w+\s*=\s*["']?[^"'>]+["']?/gi;
  if (eventHandlerPattern.test(html)) {
    {
      warn("Potentially dangerous event handlers detected in translation. Consider removing onclick, onerror, etc. from your translation messages.");
    }
    html = html.replace(/(\s+)(on)(\w+\s*=)/gi, "$1&#111;n$3");
  }
  const javascriptUrlPattern = [
    // In href, src, action, formaction attributes
    /(\s+(?:href|src|action|formaction)\s*=\s*["']?)\s*javascript:/gi,
    // In style attributes within url()
    /(style\s*=\s*["'][^"']*url\s*\(\s*)javascript:/gi
  ];
  javascriptUrlPattern.forEach((pattern) => {
    html = html.replace(pattern, "$1javascript&#58;");
  });
  return html;
}
const hasOwnProperty = Object.prototype.hasOwnProperty;
function hasOwn(obj, key) {
  return hasOwnProperty.call(obj, key);
}
const isArray = Array.isArray;
const isFunction = (val) => typeof val === "function";
const isString = (val) => typeof val === "string";
const isBoolean = (val) => typeof val === "boolean";
const isObject = (val) => val !== null && typeof val === "object";
const isPromise = (val) => {
  return isObject(val) && isFunction(val.then) && isFunction(val.catch);
};
const objectToString = Object.prototype.toString;
const toTypeString = (value) => objectToString.call(value);
const isPlainObject$8 = (val) => toTypeString(val) === "[object Object]";
const toDisplayString = (val) => {
  return val == null ? "" : isArray(val) || isPlainObject$8(val) && val.toString === objectToString ? JSON.stringify(val, null, 2) : String(val);
};
function join(items, separator = "") {
  return items.reduce((str, item, index) => index === 0 ? str + item : str + separator + item, "");
}
const RANGE = 2;
function generateCodeFrame(source, start = 0, end = source.length) {
  const lines = source.split(/\r?\n/);
  let count = 0;
  const res = [];
  for (let i = 0; i < lines.length; i++) {
    count += lines[i].length + 1;
    if (count >= start) {
      for (let j = i - RANGE; j <= i + RANGE || end > count; j++) {
        if (j < 0 || j >= lines.length)
          continue;
        const line = j + 1;
        res.push(`${line}${" ".repeat(3 - String(line).length)}|  ${lines[j]}`);
        const lineLength = lines[j].length;
        if (j === i) {
          const pad = start - (count - lineLength) + 1;
          const length = Math.max(1, end > count ? lineLength - pad : end - start);
          res.push(`   |  ` + " ".repeat(pad) + "^".repeat(length));
        } else if (j > i) {
          if (end > count) {
            const length = Math.max(Math.min(end - count, lineLength), 1);
            res.push(`   |  ` + "^".repeat(length));
          }
          count += lineLength + 1;
        }
      }
      break;
    }
  }
  return res.join("\n");
}
/*!
  * message-compiler v10.0.8
  * (c) 2025 kazuya kawaguchi
  * Released under the MIT License.
  */
function createPosition(line, column, offset) {
  return { line, column, offset };
}
function createLocation(start, end, source) {
  const loc = { start, end };
  return loc;
}
const CompileErrorCodes = {
  // tokenizer error codes
  EXPECTED_TOKEN: 1,
  INVALID_TOKEN_IN_PLACEHOLDER: 2,
  UNTERMINATED_SINGLE_QUOTE_IN_PLACEHOLDER: 3,
  UNKNOWN_ESCAPE_SEQUENCE: 4,
  INVALID_UNICODE_ESCAPE_SEQUENCE: 5,
  UNBALANCED_CLOSING_BRACE: 6,
  UNTERMINATED_CLOSING_BRACE: 7,
  EMPTY_PLACEHOLDER: 8,
  NOT_ALLOW_NEST_PLACEHOLDER: 9,
  INVALID_LINKED_FORMAT: 10,
  // parser error codes
  MUST_HAVE_MESSAGES_IN_PLURAL: 11,
  UNEXPECTED_EMPTY_LINKED_MODIFIER: 12,
  UNEXPECTED_EMPTY_LINKED_KEY: 13,
  UNEXPECTED_LEXICAL_ANALYSIS: 14,
  // generator error codes
  UNHANDLED_CODEGEN_NODE_TYPE: 15,
  // minifier error codes
  UNHANDLED_MINIFIER_NODE_TYPE: 16
};
const COMPILE_ERROR_CODES_EXTEND_POINT = 17;
const errorMessages$2 = {
  // tokenizer error messages
  [CompileErrorCodes.EXPECTED_TOKEN]: `Expected token: '{0}'`,
  [CompileErrorCodes.INVALID_TOKEN_IN_PLACEHOLDER]: `Invalid token in placeholder: '{0}'`,
  [CompileErrorCodes.UNTERMINATED_SINGLE_QUOTE_IN_PLACEHOLDER]: `Unterminated single quote in placeholder`,
  [CompileErrorCodes.UNKNOWN_ESCAPE_SEQUENCE]: `Unknown escape sequence: \\{0}`,
  [CompileErrorCodes.INVALID_UNICODE_ESCAPE_SEQUENCE]: `Invalid unicode escape sequence: {0}`,
  [CompileErrorCodes.UNBALANCED_CLOSING_BRACE]: `Unbalanced closing brace`,
  [CompileErrorCodes.UNTERMINATED_CLOSING_BRACE]: `Unterminated closing brace`,
  [CompileErrorCodes.EMPTY_PLACEHOLDER]: `Empty placeholder`,
  [CompileErrorCodes.NOT_ALLOW_NEST_PLACEHOLDER]: `Not allowed nest placeholder`,
  [CompileErrorCodes.INVALID_LINKED_FORMAT]: `Invalid linked format`,
  // parser error messages
  [CompileErrorCodes.MUST_HAVE_MESSAGES_IN_PLURAL]: `Plural must have messages`,
  [CompileErrorCodes.UNEXPECTED_EMPTY_LINKED_MODIFIER]: `Unexpected empty linked modifier`,
  [CompileErrorCodes.UNEXPECTED_EMPTY_LINKED_KEY]: `Unexpected empty linked key`,
  [CompileErrorCodes.UNEXPECTED_LEXICAL_ANALYSIS]: `Unexpected lexical analysis in token: '{0}'`,
  // generator error messages
  [CompileErrorCodes.UNHANDLED_CODEGEN_NODE_TYPE]: `unhandled codegen node type: '{0}'`,
  // minimizer error messages
  [CompileErrorCodes.UNHANDLED_MINIFIER_NODE_TYPE]: `unhandled mimifier node type: '{0}'`
};
function createCompileError(code, loc, options = {}) {
  const { domain, messages, args } = options;
  const msg = format$1((messages || errorMessages$2)[code] || "", ...args || []);
  const error = new SyntaxError(String(msg));
  error.code = code;
  if (loc) {
    error.location = loc;
  }
  error.domain = domain;
  return error;
}
function defaultOnError(error) {
  throw error;
}
const RE_HTML_TAG = /<\/?[\w\s="/.':;#-\/]+>/;
const detectHtmlTag = (source) => RE_HTML_TAG.test(source);
const CHAR_SP = " ";
const CHAR_CR = "\r";
const CHAR_LF = "\n";
const CHAR_LS = String.fromCharCode(8232);
const CHAR_PS = String.fromCharCode(8233);
function createScanner(str) {
  const _buf = str;
  let _index = 0;
  let _line = 1;
  let _column = 1;
  let _peekOffset = 0;
  const isCRLF = (index2) => _buf[index2] === CHAR_CR && _buf[index2 + 1] === CHAR_LF;
  const isLF = (index2) => _buf[index2] === CHAR_LF;
  const isPS = (index2) => _buf[index2] === CHAR_PS;
  const isLS = (index2) => _buf[index2] === CHAR_LS;
  const isLineEnd = (index2) => isCRLF(index2) || isLF(index2) || isPS(index2) || isLS(index2);
  const index = () => _index;
  const line = () => _line;
  const column = () => _column;
  const peekOffset = () => _peekOffset;
  const charAt = (offset) => isCRLF(offset) || isPS(offset) || isLS(offset) ? CHAR_LF : _buf[offset];
  const currentChar = () => charAt(_index);
  const currentPeek = () => charAt(_index + _peekOffset);
  function next() {
    _peekOffset = 0;
    if (isLineEnd(_index)) {
      _line++;
      _column = 0;
    }
    if (isCRLF(_index)) {
      _index++;
    }
    _index++;
    _column++;
    return _buf[_index];
  }
  function peek() {
    if (isCRLF(_index + _peekOffset)) {
      _peekOffset++;
    }
    _peekOffset++;
    return _buf[_index + _peekOffset];
  }
  function reset() {
    _index = 0;
    _line = 1;
    _column = 1;
    _peekOffset = 0;
  }
  function resetPeek(offset = 0) {
    _peekOffset = offset;
  }
  function skipToPeek() {
    const target = _index + _peekOffset;
    while (target !== _index) {
      next();
    }
    _peekOffset = 0;
  }
  return {
    index,
    line,
    column,
    peekOffset,
    charAt,
    currentChar,
    currentPeek,
    next,
    peek,
    reset,
    resetPeek,
    skipToPeek
  };
}
const EOF = void 0;
const DOT = ".";
const LITERAL_DELIMITER = "'";
const ERROR_DOMAIN$3 = "tokenizer";
function createTokenizer(source, options = {}) {
  const location = options.location !== false;
  const _scnr = createScanner(source);
  const currentOffset = () => _scnr.index();
  const currentPosition = () => createPosition(_scnr.line(), _scnr.column(), _scnr.index());
  const _initLoc = currentPosition();
  const _initOffset = currentOffset();
  const _context = {
    currentType: 13,
    offset: _initOffset,
    startLoc: _initLoc,
    endLoc: _initLoc,
    lastType: 13,
    lastOffset: _initOffset,
    lastStartLoc: _initLoc,
    lastEndLoc: _initLoc,
    braceNest: 0,
    inLinked: false,
    text: ""
  };
  const context = () => _context;
  const { onError } = options;
  function emitError(code, pos, offset, ...args) {
    const ctx = context();
    pos.column += offset;
    pos.offset += offset;
    if (onError) {
      const loc = location ? createLocation(ctx.startLoc, pos) : null;
      const err = createCompileError(code, loc, {
        domain: ERROR_DOMAIN$3,
        args
      });
      onError(err);
    }
  }
  function getToken(context2, type, value) {
    context2.endLoc = currentPosition();
    context2.currentType = type;
    const token = { type };
    if (location) {
      token.loc = createLocation(context2.startLoc, context2.endLoc);
    }
    if (value != null) {
      token.value = value;
    }
    return token;
  }
  const getEndToken = (context2) => getToken(
    context2,
    13
    /* TokenTypes.EOF */
  );
  function eat(scnr, ch) {
    if (scnr.currentChar() === ch) {
      scnr.next();
      return ch;
    } else {
      emitError(CompileErrorCodes.EXPECTED_TOKEN, currentPosition(), 0, ch);
      return "";
    }
  }
  function peekSpaces(scnr) {
    let buf = "";
    while (scnr.currentPeek() === CHAR_SP || scnr.currentPeek() === CHAR_LF) {
      buf += scnr.currentPeek();
      scnr.peek();
    }
    return buf;
  }
  function skipSpaces(scnr) {
    const buf = peekSpaces(scnr);
    scnr.skipToPeek();
    return buf;
  }
  function isIdentifierStart(ch) {
    if (ch === EOF) {
      return false;
    }
    const cc = ch.charCodeAt(0);
    return cc >= 97 && cc <= 122 || // a-z
    cc >= 65 && cc <= 90 || // A-Z
    cc === 95;
  }
  function isNumberStart(ch) {
    if (ch === EOF) {
      return false;
    }
    const cc = ch.charCodeAt(0);
    return cc >= 48 && cc <= 57;
  }
  function isNamedIdentifierStart(scnr, context2) {
    const { currentType } = context2;
    if (currentType !== 2) {
      return false;
    }
    peekSpaces(scnr);
    const ret = isIdentifierStart(scnr.currentPeek());
    scnr.resetPeek();
    return ret;
  }
  function isListIdentifierStart(scnr, context2) {
    const { currentType } = context2;
    if (currentType !== 2) {
      return false;
    }
    peekSpaces(scnr);
    const ch = scnr.currentPeek() === "-" ? scnr.peek() : scnr.currentPeek();
    const ret = isNumberStart(ch);
    scnr.resetPeek();
    return ret;
  }
  function isLiteralStart(scnr, context2) {
    const { currentType } = context2;
    if (currentType !== 2) {
      return false;
    }
    peekSpaces(scnr);
    const ret = scnr.currentPeek() === LITERAL_DELIMITER;
    scnr.resetPeek();
    return ret;
  }
  function isLinkedDotStart(scnr, context2) {
    const { currentType } = context2;
    if (currentType !== 7) {
      return false;
    }
    peekSpaces(scnr);
    const ret = scnr.currentPeek() === ".";
    scnr.resetPeek();
    return ret;
  }
  function isLinkedModifierStart(scnr, context2) {
    const { currentType } = context2;
    if (currentType !== 8) {
      return false;
    }
    peekSpaces(scnr);
    const ret = isIdentifierStart(scnr.currentPeek());
    scnr.resetPeek();
    return ret;
  }
  function isLinkedDelimiterStart(scnr, context2) {
    const { currentType } = context2;
    if (!(currentType === 7 || currentType === 11)) {
      return false;
    }
    peekSpaces(scnr);
    const ret = scnr.currentPeek() === ":";
    scnr.resetPeek();
    return ret;
  }
  function isLinkedReferStart(scnr, context2) {
    const { currentType } = context2;
    if (currentType !== 9) {
      return false;
    }
    const fn = () => {
      const ch = scnr.currentPeek();
      if (ch === "{") {
        return isIdentifierStart(scnr.peek());
      } else if (ch === "@" || ch === "|" || ch === ":" || ch === "." || ch === CHAR_SP || !ch) {
        return false;
      } else if (ch === CHAR_LF) {
        scnr.peek();
        return fn();
      } else {
        return isTextStart(scnr, false);
      }
    };
    const ret = fn();
    scnr.resetPeek();
    return ret;
  }
  function isPluralStart(scnr) {
    peekSpaces(scnr);
    const ret = scnr.currentPeek() === "|";
    scnr.resetPeek();
    return ret;
  }
  function isTextStart(scnr, reset = true) {
    const fn = (hasSpace = false, prev = "") => {
      const ch = scnr.currentPeek();
      if (ch === "{") {
        return hasSpace;
      } else if (ch === "@" || !ch) {
        return hasSpace;
      } else if (ch === "|") {
        return !(prev === CHAR_SP || prev === CHAR_LF);
      } else if (ch === CHAR_SP) {
        scnr.peek();
        return fn(true, CHAR_SP);
      } else if (ch === CHAR_LF) {
        scnr.peek();
        return fn(true, CHAR_LF);
      } else {
        return true;
      }
    };
    const ret = fn();
    reset && scnr.resetPeek();
    return ret;
  }
  function takeChar(scnr, fn) {
    const ch = scnr.currentChar();
    if (ch === EOF) {
      return EOF;
    }
    if (fn(ch)) {
      scnr.next();
      return ch;
    }
    return null;
  }
  function isIdentifier(ch) {
    const cc = ch.charCodeAt(0);
    return cc >= 97 && cc <= 122 || // a-z
    cc >= 65 && cc <= 90 || // A-Z
    cc >= 48 && cc <= 57 || // 0-9
    cc === 95 || // _
    cc === 36;
  }
  function takeIdentifierChar(scnr) {
    return takeChar(scnr, isIdentifier);
  }
  function isNamedIdentifier(ch) {
    const cc = ch.charCodeAt(0);
    return cc >= 97 && cc <= 122 || // a-z
    cc >= 65 && cc <= 90 || // A-Z
    cc >= 48 && cc <= 57 || // 0-9
    cc === 95 || // _
    cc === 36 || // $
    cc === 45;
  }
  function takeNamedIdentifierChar(scnr) {
    return takeChar(scnr, isNamedIdentifier);
  }
  function isDigit(ch) {
    const cc = ch.charCodeAt(0);
    return cc >= 48 && cc <= 57;
  }
  function takeDigit(scnr) {
    return takeChar(scnr, isDigit);
  }
  function isHexDigit(ch) {
    const cc = ch.charCodeAt(0);
    return cc >= 48 && cc <= 57 || // 0-9
    cc >= 65 && cc <= 70 || // A-F
    cc >= 97 && cc <= 102;
  }
  function takeHexDigit(scnr) {
    return takeChar(scnr, isHexDigit);
  }
  function getDigits(scnr) {
    let ch = "";
    let num = "";
    while (ch = takeDigit(scnr)) {
      num += ch;
    }
    return num;
  }
  function readText(scnr) {
    let buf = "";
    while (true) {
      const ch = scnr.currentChar();
      if (ch === "{" || ch === "}" || ch === "@" || ch === "|" || !ch) {
        break;
      } else if (ch === CHAR_SP || ch === CHAR_LF) {
        if (isTextStart(scnr)) {
          buf += ch;
          scnr.next();
        } else if (isPluralStart(scnr)) {
          break;
        } else {
          buf += ch;
          scnr.next();
        }
      } else {
        buf += ch;
        scnr.next();
      }
    }
    return buf;
  }
  function readNamedIdentifier(scnr) {
    skipSpaces(scnr);
    let ch = "";
    let name = "";
    while (ch = takeNamedIdentifierChar(scnr)) {
      name += ch;
    }
    if (scnr.currentChar() === EOF) {
      emitError(CompileErrorCodes.UNTERMINATED_CLOSING_BRACE, currentPosition(), 0);
    }
    return name;
  }
  function readListIdentifier(scnr) {
    skipSpaces(scnr);
    let value = "";
    if (scnr.currentChar() === "-") {
      scnr.next();
      value += `-${getDigits(scnr)}`;
    } else {
      value += getDigits(scnr);
    }
    if (scnr.currentChar() === EOF) {
      emitError(CompileErrorCodes.UNTERMINATED_CLOSING_BRACE, currentPosition(), 0);
    }
    return value;
  }
  function isLiteral2(ch) {
    return ch !== LITERAL_DELIMITER && ch !== CHAR_LF;
  }
  function readLiteral(scnr) {
    skipSpaces(scnr);
    eat(scnr, `'`);
    let ch = "";
    let literal = "";
    while (ch = takeChar(scnr, isLiteral2)) {
      if (ch === "\\") {
        literal += readEscapeSequence(scnr);
      } else {
        literal += ch;
      }
    }
    const current = scnr.currentChar();
    if (current === CHAR_LF || current === EOF) {
      emitError(CompileErrorCodes.UNTERMINATED_SINGLE_QUOTE_IN_PLACEHOLDER, currentPosition(), 0);
      if (current === CHAR_LF) {
        scnr.next();
        eat(scnr, `'`);
      }
      return literal;
    }
    eat(scnr, `'`);
    return literal;
  }
  function readEscapeSequence(scnr) {
    const ch = scnr.currentChar();
    switch (ch) {
      case "\\":
      case `'`:
        scnr.next();
        return `\\${ch}`;
      case "u":
        return readUnicodeEscapeSequence(scnr, ch, 4);
      case "U":
        return readUnicodeEscapeSequence(scnr, ch, 6);
      default:
        emitError(CompileErrorCodes.UNKNOWN_ESCAPE_SEQUENCE, currentPosition(), 0, ch);
        return "";
    }
  }
  function readUnicodeEscapeSequence(scnr, unicode, digits) {
    eat(scnr, unicode);
    let sequence = "";
    for (let i = 0; i < digits; i++) {
      const ch = takeHexDigit(scnr);
      if (!ch) {
        emitError(CompileErrorCodes.INVALID_UNICODE_ESCAPE_SEQUENCE, currentPosition(), 0, `\\${unicode}${sequence}${scnr.currentChar()}`);
        break;
      }
      sequence += ch;
    }
    return `\\${unicode}${sequence}`;
  }
  function isInvalidIdentifier(ch) {
    return ch !== "{" && ch !== "}" && ch !== CHAR_SP && ch !== CHAR_LF;
  }
  function readInvalidIdentifier(scnr) {
    skipSpaces(scnr);
    let ch = "";
    let identifiers = "";
    while (ch = takeChar(scnr, isInvalidIdentifier)) {
      identifiers += ch;
    }
    return identifiers;
  }
  function readLinkedModifier(scnr) {
    let ch = "";
    let name = "";
    while (ch = takeIdentifierChar(scnr)) {
      name += ch;
    }
    return name;
  }
  function readLinkedRefer(scnr) {
    const fn = (buf) => {
      const ch = scnr.currentChar();
      if (ch === "{" || ch === "@" || ch === "|" || ch === "(" || ch === ")" || !ch) {
        return buf;
      } else if (ch === CHAR_SP) {
        return buf;
      } else if (ch === CHAR_LF || ch === DOT) {
        buf += ch;
        scnr.next();
        return fn(buf);
      } else {
        buf += ch;
        scnr.next();
        return fn(buf);
      }
    };
    return fn("");
  }
  function readPlural(scnr) {
    skipSpaces(scnr);
    const plural = eat(
      scnr,
      "|"
      /* TokenChars.Pipe */
    );
    skipSpaces(scnr);
    return plural;
  }
  function readTokenInPlaceholder(scnr, context2) {
    let token = null;
    const ch = scnr.currentChar();
    switch (ch) {
      case "{":
        if (context2.braceNest >= 1) {
          emitError(CompileErrorCodes.NOT_ALLOW_NEST_PLACEHOLDER, currentPosition(), 0);
        }
        scnr.next();
        token = getToken(
          context2,
          2,
          "{"
          /* TokenChars.BraceLeft */
        );
        skipSpaces(scnr);
        context2.braceNest++;
        return token;
      case "}":
        if (context2.braceNest > 0 && context2.currentType === 2) {
          emitError(CompileErrorCodes.EMPTY_PLACEHOLDER, currentPosition(), 0);
        }
        scnr.next();
        token = getToken(
          context2,
          3,
          "}"
          /* TokenChars.BraceRight */
        );
        context2.braceNest--;
        context2.braceNest > 0 && skipSpaces(scnr);
        if (context2.inLinked && context2.braceNest === 0) {
          context2.inLinked = false;
        }
        return token;
      case "@":
        if (context2.braceNest > 0) {
          emitError(CompileErrorCodes.UNTERMINATED_CLOSING_BRACE, currentPosition(), 0);
        }
        token = readTokenInLinked(scnr, context2) || getEndToken(context2);
        context2.braceNest = 0;
        return token;
      default: {
        let validNamedIdentifier = true;
        let validListIdentifier = true;
        let validLiteral = true;
        if (isPluralStart(scnr)) {
          if (context2.braceNest > 0) {
            emitError(CompileErrorCodes.UNTERMINATED_CLOSING_BRACE, currentPosition(), 0);
          }
          token = getToken(context2, 1, readPlural(scnr));
          context2.braceNest = 0;
          context2.inLinked = false;
          return token;
        }
        if (context2.braceNest > 0 && (context2.currentType === 4 || context2.currentType === 5 || context2.currentType === 6)) {
          emitError(CompileErrorCodes.UNTERMINATED_CLOSING_BRACE, currentPosition(), 0);
          context2.braceNest = 0;
          return readToken(scnr, context2);
        }
        if (validNamedIdentifier = isNamedIdentifierStart(scnr, context2)) {
          token = getToken(context2, 4, readNamedIdentifier(scnr));
          skipSpaces(scnr);
          return token;
        }
        if (validListIdentifier = isListIdentifierStart(scnr, context2)) {
          token = getToken(context2, 5, readListIdentifier(scnr));
          skipSpaces(scnr);
          return token;
        }
        if (validLiteral = isLiteralStart(scnr, context2)) {
          token = getToken(context2, 6, readLiteral(scnr));
          skipSpaces(scnr);
          return token;
        }
        if (!validNamedIdentifier && !validListIdentifier && !validLiteral) {
          token = getToken(context2, 12, readInvalidIdentifier(scnr));
          emitError(CompileErrorCodes.INVALID_TOKEN_IN_PLACEHOLDER, currentPosition(), 0, token.value);
          skipSpaces(scnr);
          return token;
        }
        break;
      }
    }
    return token;
  }
  function readTokenInLinked(scnr, context2) {
    const { currentType } = context2;
    let token = null;
    const ch = scnr.currentChar();
    if ((currentType === 7 || currentType === 8 || currentType === 11 || currentType === 9) && (ch === CHAR_LF || ch === CHAR_SP)) {
      emitError(CompileErrorCodes.INVALID_LINKED_FORMAT, currentPosition(), 0);
    }
    switch (ch) {
      case "@":
        scnr.next();
        token = getToken(
          context2,
          7,
          "@"
          /* TokenChars.LinkedAlias */
        );
        context2.inLinked = true;
        return token;
      case ".":
        skipSpaces(scnr);
        scnr.next();
        return getToken(
          context2,
          8,
          "."
          /* TokenChars.LinkedDot */
        );
      case ":":
        skipSpaces(scnr);
        scnr.next();
        return getToken(
          context2,
          9,
          ":"
          /* TokenChars.LinkedDelimiter */
        );
      default:
        if (isPluralStart(scnr)) {
          token = getToken(context2, 1, readPlural(scnr));
          context2.braceNest = 0;
          context2.inLinked = false;
          return token;
        }
        if (isLinkedDotStart(scnr, context2) || isLinkedDelimiterStart(scnr, context2)) {
          skipSpaces(scnr);
          return readTokenInLinked(scnr, context2);
        }
        if (isLinkedModifierStart(scnr, context2)) {
          skipSpaces(scnr);
          return getToken(context2, 11, readLinkedModifier(scnr));
        }
        if (isLinkedReferStart(scnr, context2)) {
          skipSpaces(scnr);
          if (ch === "{") {
            return readTokenInPlaceholder(scnr, context2) || token;
          } else {
            return getToken(context2, 10, readLinkedRefer(scnr));
          }
        }
        if (currentType === 7) {
          emitError(CompileErrorCodes.INVALID_LINKED_FORMAT, currentPosition(), 0);
        }
        context2.braceNest = 0;
        context2.inLinked = false;
        return readToken(scnr, context2);
    }
  }
  function readToken(scnr, context2) {
    let token = {
      type: 13
      /* TokenTypes.EOF */
    };
    if (context2.braceNest > 0) {
      return readTokenInPlaceholder(scnr, context2) || getEndToken(context2);
    }
    if (context2.inLinked) {
      return readTokenInLinked(scnr, context2) || getEndToken(context2);
    }
    const ch = scnr.currentChar();
    switch (ch) {
      case "{":
        return readTokenInPlaceholder(scnr, context2) || getEndToken(context2);
      case "}":
        emitError(CompileErrorCodes.UNBALANCED_CLOSING_BRACE, currentPosition(), 0);
        scnr.next();
        return getToken(
          context2,
          3,
          "}"
          /* TokenChars.BraceRight */
        );
      case "@":
        return readTokenInLinked(scnr, context2) || getEndToken(context2);
      default: {
        if (isPluralStart(scnr)) {
          token = getToken(context2, 1, readPlural(scnr));
          context2.braceNest = 0;
          context2.inLinked = false;
          return token;
        }
        if (isTextStart(scnr)) {
          return getToken(context2, 0, readText(scnr));
        }
        break;
      }
    }
    return token;
  }
  function nextToken() {
    const { currentType, offset, startLoc, endLoc } = _context;
    _context.lastType = currentType;
    _context.lastOffset = offset;
    _context.lastStartLoc = startLoc;
    _context.lastEndLoc = endLoc;
    _context.offset = currentOffset();
    _context.startLoc = currentPosition();
    if (_scnr.currentChar() === EOF) {
      return getToken(
        _context,
        13
        /* TokenTypes.EOF */
      );
    }
    return readToken(_scnr, _context);
  }
  return {
    nextToken,
    currentOffset,
    currentPosition,
    context
  };
}
const ERROR_DOMAIN$2 = "parser";
const KNOWN_ESCAPES = /(?:\\\\|\\'|\\u([0-9a-fA-F]{4})|\\U([0-9a-fA-F]{6}))/g;
function fromEscapeSequence(match, codePoint4, codePoint6) {
  switch (match) {
    case `\\\\`:
      return `\\`;
    // eslint-disable-next-line no-useless-escape
    case `\\'`:
      return `'`;
    default: {
      const codePoint = parseInt(codePoint4 || codePoint6, 16);
      if (codePoint <= 55295 || codePoint >= 57344) {
        return String.fromCodePoint(codePoint);
      }
      return "�";
    }
  }
}
function createParser(options = {}) {
  const location = options.location !== false;
  const { onError } = options;
  function emitError(tokenzer, code, start, offset, ...args) {
    const end = tokenzer.currentPosition();
    end.offset += offset;
    end.column += offset;
    if (onError) {
      const loc = location ? createLocation(start, end) : null;
      const err = createCompileError(code, loc, {
        domain: ERROR_DOMAIN$2,
        args
      });
      onError(err);
    }
  }
  function startNode(type, offset, loc) {
    const node = { type };
    if (location) {
      node.start = offset;
      node.end = offset;
      node.loc = { start: loc, end: loc };
    }
    return node;
  }
  function endNode(node, offset, pos, type) {
    if (location) {
      node.end = offset;
      if (node.loc) {
        node.loc.end = pos;
      }
    }
  }
  function parseText(tokenizer, value) {
    const context = tokenizer.context();
    const node = startNode(3, context.offset, context.startLoc);
    node.value = value;
    endNode(node, tokenizer.currentOffset(), tokenizer.currentPosition());
    return node;
  }
  function parseList(tokenizer, index) {
    const context = tokenizer.context();
    const { lastOffset: offset, lastStartLoc: loc } = context;
    const node = startNode(5, offset, loc);
    node.index = parseInt(index, 10);
    tokenizer.nextToken();
    endNode(node, tokenizer.currentOffset(), tokenizer.currentPosition());
    return node;
  }
  function parseNamed(tokenizer, key) {
    const context = tokenizer.context();
    const { lastOffset: offset, lastStartLoc: loc } = context;
    const node = startNode(4, offset, loc);
    node.key = key;
    tokenizer.nextToken();
    endNode(node, tokenizer.currentOffset(), tokenizer.currentPosition());
    return node;
  }
  function parseLiteral(tokenizer, value) {
    const context = tokenizer.context();
    const { lastOffset: offset, lastStartLoc: loc } = context;
    const node = startNode(9, offset, loc);
    node.value = value.replace(KNOWN_ESCAPES, fromEscapeSequence);
    tokenizer.nextToken();
    endNode(node, tokenizer.currentOffset(), tokenizer.currentPosition());
    return node;
  }
  function parseLinkedModifier(tokenizer) {
    const token = tokenizer.nextToken();
    const context = tokenizer.context();
    const { lastOffset: offset, lastStartLoc: loc } = context;
    const node = startNode(8, offset, loc);
    if (token.type !== 11) {
      emitError(tokenizer, CompileErrorCodes.UNEXPECTED_EMPTY_LINKED_MODIFIER, context.lastStartLoc, 0);
      node.value = "";
      endNode(node, offset, loc);
      return {
        nextConsumeToken: token,
        node
      };
    }
    if (token.value == null) {
      emitError(tokenizer, CompileErrorCodes.UNEXPECTED_LEXICAL_ANALYSIS, context.lastStartLoc, 0, getTokenCaption(token));
    }
    node.value = token.value || "";
    endNode(node, tokenizer.currentOffset(), tokenizer.currentPosition());
    return {
      node
    };
  }
  function parseLinkedKey(tokenizer, value) {
    const context = tokenizer.context();
    const node = startNode(7, context.offset, context.startLoc);
    node.value = value;
    endNode(node, tokenizer.currentOffset(), tokenizer.currentPosition());
    return node;
  }
  function parseLinked(tokenizer) {
    const context = tokenizer.context();
    const linkedNode = startNode(6, context.offset, context.startLoc);
    let token = tokenizer.nextToken();
    if (token.type === 8) {
      const parsed = parseLinkedModifier(tokenizer);
      linkedNode.modifier = parsed.node;
      token = parsed.nextConsumeToken || tokenizer.nextToken();
    }
    if (token.type !== 9) {
      emitError(tokenizer, CompileErrorCodes.UNEXPECTED_LEXICAL_ANALYSIS, context.lastStartLoc, 0, getTokenCaption(token));
    }
    token = tokenizer.nextToken();
    if (token.type === 2) {
      token = tokenizer.nextToken();
    }
    switch (token.type) {
      case 10:
        if (token.value == null) {
          emitError(tokenizer, CompileErrorCodes.UNEXPECTED_LEXICAL_ANALYSIS, context.lastStartLoc, 0, getTokenCaption(token));
        }
        linkedNode.key = parseLinkedKey(tokenizer, token.value || "");
        break;
      case 4:
        if (token.value == null) {
          emitError(tokenizer, CompileErrorCodes.UNEXPECTED_LEXICAL_ANALYSIS, context.lastStartLoc, 0, getTokenCaption(token));
        }
        linkedNode.key = parseNamed(tokenizer, token.value || "");
        break;
      case 5:
        if (token.value == null) {
          emitError(tokenizer, CompileErrorCodes.UNEXPECTED_LEXICAL_ANALYSIS, context.lastStartLoc, 0, getTokenCaption(token));
        }
        linkedNode.key = parseList(tokenizer, token.value || "");
        break;
      case 6:
        if (token.value == null) {
          emitError(tokenizer, CompileErrorCodes.UNEXPECTED_LEXICAL_ANALYSIS, context.lastStartLoc, 0, getTokenCaption(token));
        }
        linkedNode.key = parseLiteral(tokenizer, token.value || "");
        break;
      default: {
        emitError(tokenizer, CompileErrorCodes.UNEXPECTED_EMPTY_LINKED_KEY, context.lastStartLoc, 0);
        const nextContext = tokenizer.context();
        const emptyLinkedKeyNode = startNode(7, nextContext.offset, nextContext.startLoc);
        emptyLinkedKeyNode.value = "";
        endNode(emptyLinkedKeyNode, nextContext.offset, nextContext.startLoc);
        linkedNode.key = emptyLinkedKeyNode;
        endNode(linkedNode, nextContext.offset, nextContext.startLoc);
        return {
          nextConsumeToken: token,
          node: linkedNode
        };
      }
    }
    endNode(linkedNode, tokenizer.currentOffset(), tokenizer.currentPosition());
    return {
      node: linkedNode
    };
  }
  function parseMessage(tokenizer) {
    const context = tokenizer.context();
    const startOffset = context.currentType === 1 ? tokenizer.currentOffset() : context.offset;
    const startLoc = context.currentType === 1 ? context.endLoc : context.startLoc;
    const node = startNode(2, startOffset, startLoc);
    node.items = [];
    let nextToken = null;
    do {
      const token = nextToken || tokenizer.nextToken();
      nextToken = null;
      switch (token.type) {
        case 0:
          if (token.value == null) {
            emitError(tokenizer, CompileErrorCodes.UNEXPECTED_LEXICAL_ANALYSIS, context.lastStartLoc, 0, getTokenCaption(token));
          }
          node.items.push(parseText(tokenizer, token.value || ""));
          break;
        case 5:
          if (token.value == null) {
            emitError(tokenizer, CompileErrorCodes.UNEXPECTED_LEXICAL_ANALYSIS, context.lastStartLoc, 0, getTokenCaption(token));
          }
          node.items.push(parseList(tokenizer, token.value || ""));
          break;
        case 4:
          if (token.value == null) {
            emitError(tokenizer, CompileErrorCodes.UNEXPECTED_LEXICAL_ANALYSIS, context.lastStartLoc, 0, getTokenCaption(token));
          }
          node.items.push(parseNamed(tokenizer, token.value || ""));
          break;
        case 6:
          if (token.value == null) {
            emitError(tokenizer, CompileErrorCodes.UNEXPECTED_LEXICAL_ANALYSIS, context.lastStartLoc, 0, getTokenCaption(token));
          }
          node.items.push(parseLiteral(tokenizer, token.value || ""));
          break;
        case 7: {
          const parsed = parseLinked(tokenizer);
          node.items.push(parsed.node);
          nextToken = parsed.nextConsumeToken || null;
          break;
        }
      }
    } while (context.currentType !== 13 && context.currentType !== 1);
    const endOffset = context.currentType === 1 ? context.lastOffset : tokenizer.currentOffset();
    const endLoc = context.currentType === 1 ? context.lastEndLoc : tokenizer.currentPosition();
    endNode(node, endOffset, endLoc);
    return node;
  }
  function parsePlural(tokenizer, offset, loc, msgNode) {
    const context = tokenizer.context();
    let hasEmptyMessage = msgNode.items.length === 0;
    const node = startNode(1, offset, loc);
    node.cases = [];
    node.cases.push(msgNode);
    do {
      const msg = parseMessage(tokenizer);
      if (!hasEmptyMessage) {
        hasEmptyMessage = msg.items.length === 0;
      }
      node.cases.push(msg);
    } while (context.currentType !== 13);
    if (hasEmptyMessage) {
      emitError(tokenizer, CompileErrorCodes.MUST_HAVE_MESSAGES_IN_PLURAL, loc, 0);
    }
    endNode(node, tokenizer.currentOffset(), tokenizer.currentPosition());
    return node;
  }
  function parseResource(tokenizer) {
    const context = tokenizer.context();
    const { offset, startLoc } = context;
    const msgNode = parseMessage(tokenizer);
    if (context.currentType === 13) {
      return msgNode;
    } else {
      return parsePlural(tokenizer, offset, startLoc, msgNode);
    }
  }
  function parse2(source) {
    const tokenizer = createTokenizer(source, assign({}, options));
    const context = tokenizer.context();
    const node = startNode(0, context.offset, context.startLoc);
    if (location && node.loc) {
      node.loc.source = source;
    }
    node.body = parseResource(tokenizer);
    if (options.onCacheKey) {
      node.cacheKey = options.onCacheKey(source);
    }
    if (context.currentType !== 13) {
      emitError(tokenizer, CompileErrorCodes.UNEXPECTED_LEXICAL_ANALYSIS, context.lastStartLoc, 0, source[context.offset] || "");
    }
    endNode(node, tokenizer.currentOffset(), tokenizer.currentPosition());
    return node;
  }
  return { parse: parse2 };
}
function getTokenCaption(token) {
  if (token.type === 13) {
    return "EOF";
  }
  const name = (token.value || "").replace(/\r?\n/gu, "\\n");
  return name.length > 10 ? name.slice(0, 9) + "…" : name;
}
function createTransformer(ast, options = {}) {
  const _context = {
    ast,
    helpers: /* @__PURE__ */ new Set()
  };
  const context = () => _context;
  const helper = (name) => {
    _context.helpers.add(name);
    return name;
  };
  return { context, helper };
}
function traverseNodes(nodes, transformer) {
  for (let i = 0; i < nodes.length; i++) {
    traverseNode(nodes[i], transformer);
  }
}
function traverseNode(node, transformer) {
  switch (node.type) {
    case 1:
      traverseNodes(node.cases, transformer);
      transformer.helper(
        "plural"
        /* HelperNameMap.PLURAL */
      );
      break;
    case 2:
      traverseNodes(node.items, transformer);
      break;
    case 6: {
      const linked = node;
      traverseNode(linked.key, transformer);
      transformer.helper(
        "linked"
        /* HelperNameMap.LINKED */
      );
      transformer.helper(
        "type"
        /* HelperNameMap.TYPE */
      );
      break;
    }
    case 5:
      transformer.helper(
        "interpolate"
        /* HelperNameMap.INTERPOLATE */
      );
      transformer.helper(
        "list"
        /* HelperNameMap.LIST */
      );
      break;
    case 4:
      transformer.helper(
        "interpolate"
        /* HelperNameMap.INTERPOLATE */
      );
      transformer.helper(
        "named"
        /* HelperNameMap.NAMED */
      );
      break;
  }
}
function transform(ast, options = {}) {
  const transformer = createTransformer(ast);
  transformer.helper(
    "normalize"
    /* HelperNameMap.NORMALIZE */
  );
  ast.body && traverseNode(ast.body, transformer);
  const context = transformer.context();
  ast.helpers = Array.from(context.helpers);
}
function optimize(ast) {
  const body = ast.body;
  if (body.type === 2) {
    optimizeMessageNode(body);
  } else {
    body.cases.forEach((c) => optimizeMessageNode(c));
  }
  return ast;
}
function optimizeMessageNode(message) {
  if (message.items.length === 1) {
    const item = message.items[0];
    if (item.type === 3 || item.type === 9) {
      message.static = item.value;
      delete item.value;
    }
  } else {
    const values = [];
    for (let i = 0; i < message.items.length; i++) {
      const item = message.items[i];
      if (!(item.type === 3 || item.type === 9)) {
        break;
      }
      if (item.value == null) {
        break;
      }
      values.push(item.value);
    }
    if (values.length === message.items.length) {
      message.static = join(values);
      for (let i = 0; i < message.items.length; i++) {
        const item = message.items[i];
        if (item.type === 3 || item.type === 9) {
          delete item.value;
        }
      }
    }
  }
}
const ERROR_DOMAIN$1 = "minifier";
function minify(node) {
  node.t = node.type;
  switch (node.type) {
    case 0: {
      const resource = node;
      minify(resource.body);
      resource.b = resource.body;
      delete resource.body;
      break;
    }
    case 1: {
      const plural = node;
      const cases = plural.cases;
      for (let i = 0; i < cases.length; i++) {
        minify(cases[i]);
      }
      plural.c = cases;
      delete plural.cases;
      break;
    }
    case 2: {
      const message = node;
      const items = message.items;
      for (let i = 0; i < items.length; i++) {
        minify(items[i]);
      }
      message.i = items;
      delete message.items;
      if (message.static) {
        message.s = message.static;
        delete message.static;
      }
      break;
    }
    case 3:
    case 9:
    case 8:
    case 7: {
      const valueNode = node;
      if (valueNode.value) {
        valueNode.v = valueNode.value;
        delete valueNode.value;
      }
      break;
    }
    case 6: {
      const linked = node;
      minify(linked.key);
      linked.k = linked.key;
      delete linked.key;
      if (linked.modifier) {
        minify(linked.modifier);
        linked.m = linked.modifier;
        delete linked.modifier;
      }
      break;
    }
    case 5: {
      const list = node;
      list.i = list.index;
      delete list.index;
      break;
    }
    case 4: {
      const named = node;
      named.k = named.key;
      delete named.key;
      break;
    }
    default: {
      throw createCompileError(CompileErrorCodes.UNHANDLED_MINIFIER_NODE_TYPE, null, {
        domain: ERROR_DOMAIN$1,
        args: [node.type]
      });
    }
  }
  delete node.type;
}
const ERROR_DOMAIN = "parser";
function createCodeGenerator(ast, options) {
  const { filename, breakLineCode, needIndent: _needIndent } = options;
  const location = options.location !== false;
  const _context = {
    filename,
    code: "",
    column: 1,
    line: 1,
    offset: 0,
    map: void 0,
    breakLineCode,
    needIndent: _needIndent,
    indentLevel: 0
  };
  if (location && ast.loc) {
    _context.source = ast.loc.source;
  }
  const context = () => _context;
  function push(code, node) {
    _context.code += code;
  }
  function _newline(n, withBreakLine = true) {
    const _breakLineCode = withBreakLine ? breakLineCode : "";
    push(_needIndent ? _breakLineCode + `  `.repeat(n) : _breakLineCode);
  }
  function indent(withNewLine = true) {
    const level = ++_context.indentLevel;
    withNewLine && _newline(level);
  }
  function deindent(withNewLine = true) {
    const level = --_context.indentLevel;
    withNewLine && _newline(level);
  }
  function newline() {
    _newline(_context.indentLevel);
  }
  const helper = (key) => `_${key}`;
  const needIndent = () => _context.needIndent;
  return {
    context,
    push,
    indent,
    deindent,
    newline,
    helper,
    needIndent
  };
}
function generateLinkedNode(generator, node) {
  const { helper } = generator;
  generator.push(`${helper(
    "linked"
    /* HelperNameMap.LINKED */
  )}(`);
  generateNode(generator, node.key);
  if (node.modifier) {
    generator.push(`, `);
    generateNode(generator, node.modifier);
    generator.push(`, _type`);
  } else {
    generator.push(`, undefined, _type`);
  }
  generator.push(`)`);
}
function generateMessageNode(generator, node) {
  const { helper, needIndent } = generator;
  generator.push(`${helper(
    "normalize"
    /* HelperNameMap.NORMALIZE */
  )}([`);
  generator.indent(needIndent());
  const length = node.items.length;
  for (let i = 0; i < length; i++) {
    generateNode(generator, node.items[i]);
    if (i === length - 1) {
      break;
    }
    generator.push(", ");
  }
  generator.deindent(needIndent());
  generator.push("])");
}
function generatePluralNode(generator, node) {
  const { helper, needIndent } = generator;
  if (node.cases.length > 1) {
    generator.push(`${helper(
      "plural"
      /* HelperNameMap.PLURAL */
    )}([`);
    generator.indent(needIndent());
    const length = node.cases.length;
    for (let i = 0; i < length; i++) {
      generateNode(generator, node.cases[i]);
      if (i === length - 1) {
        break;
      }
      generator.push(", ");
    }
    generator.deindent(needIndent());
    generator.push(`])`);
  }
}
function generateResource(generator, node) {
  if (node.body) {
    generateNode(generator, node.body);
  } else {
    generator.push("null");
  }
}
function generateNode(generator, node) {
  const { helper } = generator;
  switch (node.type) {
    case 0:
      generateResource(generator, node);
      break;
    case 1:
      generatePluralNode(generator, node);
      break;
    case 2:
      generateMessageNode(generator, node);
      break;
    case 6:
      generateLinkedNode(generator, node);
      break;
    case 8:
      generator.push(JSON.stringify(node.value), node);
      break;
    case 7:
      generator.push(JSON.stringify(node.value), node);
      break;
    case 5:
      generator.push(`${helper(
        "interpolate"
        /* HelperNameMap.INTERPOLATE */
      )}(${helper(
        "list"
        /* HelperNameMap.LIST */
      )}(${node.index}))`, node);
      break;
    case 4:
      generator.push(`${helper(
        "interpolate"
        /* HelperNameMap.INTERPOLATE */
      )}(${helper(
        "named"
        /* HelperNameMap.NAMED */
      )}(${JSON.stringify(node.key)}))`, node);
      break;
    case 9:
      generator.push(JSON.stringify(node.value), node);
      break;
    case 3:
      generator.push(JSON.stringify(node.value), node);
      break;
    default: {
      throw createCompileError(CompileErrorCodes.UNHANDLED_CODEGEN_NODE_TYPE, null, {
        domain: ERROR_DOMAIN,
        args: [node.type]
      });
    }
  }
}
const generate = (ast, options = {}) => {
  const mode = isString(options.mode) ? options.mode : "normal";
  const filename = isString(options.filename) ? options.filename : "message.intl";
  !!options.sourceMap;
  const breakLineCode = options.breakLineCode != null ? options.breakLineCode : mode === "arrow" ? ";" : "\n";
  const needIndent = options.needIndent ? options.needIndent : mode !== "arrow";
  const helpers = ast.helpers || [];
  const generator = createCodeGenerator(ast, {
    filename,
    breakLineCode,
    needIndent
  });
  generator.push(mode === "normal" ? `function __msg__ (ctx) {` : `(ctx) => {`);
  generator.indent(needIndent);
  if (helpers.length > 0) {
    generator.push(`const { ${join(helpers.map((s) => `${s}: _${s}`), ", ")} } = ctx`);
    generator.newline();
  }
  generator.push(`return `);
  generateNode(generator, ast);
  generator.deindent(needIndent);
  generator.push(`}`);
  delete ast.helpers;
  const { code, map } = generator.context();
  return {
    ast,
    code,
    map: map ? map.toJSON() : void 0
    // eslint-disable-line @typescript-eslint/no-explicit-any
  };
};
function baseCompile$1(source, options = {}) {
  const assignedOptions = assign({}, options);
  const jit = !!assignedOptions.jit;
  const enalbeMinify = !!assignedOptions.minify;
  const enambeOptimize = assignedOptions.optimize == null ? true : assignedOptions.optimize;
  const parser = createParser(assignedOptions);
  const ast = parser.parse(source);
  if (!jit) {
    transform(ast, assignedOptions);
    return generate(ast, assignedOptions);
  } else {
    enambeOptimize && optimize(ast);
    enalbeMinify && minify(ast);
    return { ast, code: "" };
  }
}
/*!
  * core-base v10.0.8
  * (c) 2025 kazuya kawaguchi
  * Released under the MIT License.
  */
function initFeatureFlags$1() {
  if (typeof __INTLIFY_PROD_DEVTOOLS__ !== "boolean") {
    getGlobalThis().__INTLIFY_PROD_DEVTOOLS__ = false;
  }
  if (typeof __INTLIFY_DROP_MESSAGE_COMPILER__ !== "boolean") {
    getGlobalThis().__INTLIFY_DROP_MESSAGE_COMPILER__ = false;
  }
}
function isMessageAST(val) {
  return isObject(val) && resolveType(val) === 0 && (hasOwn(val, "b") || hasOwn(val, "body"));
}
const PROPS_BODY = ["b", "body"];
function resolveBody(node) {
  return resolveProps(node, PROPS_BODY);
}
const PROPS_CASES = ["c", "cases"];
function resolveCases(node) {
  return resolveProps(node, PROPS_CASES, []);
}
const PROPS_STATIC = ["s", "static"];
function resolveStatic(node) {
  return resolveProps(node, PROPS_STATIC);
}
const PROPS_ITEMS = ["i", "items"];
function resolveItems(node) {
  return resolveProps(node, PROPS_ITEMS, []);
}
const PROPS_TYPE = ["t", "type"];
function resolveType(node) {
  return resolveProps(node, PROPS_TYPE);
}
const PROPS_VALUE = ["v", "value"];
function resolveValue$1(node, type) {
  const resolved = resolveProps(node, PROPS_VALUE);
  if (resolved != null) {
    return resolved;
  } else {
    throw createUnhandleNodeError(type);
  }
}
const PROPS_MODIFIER = ["m", "modifier"];
function resolveLinkedModifier(node) {
  return resolveProps(node, PROPS_MODIFIER);
}
const PROPS_KEY = ["k", "key"];
function resolveLinkedKey(node) {
  const resolved = resolveProps(node, PROPS_KEY);
  if (resolved) {
    return resolved;
  } else {
    throw createUnhandleNodeError(
      6
      /* NodeTypes.Linked */
    );
  }
}
function resolveProps(node, props, defaultValue) {
  for (let i = 0; i < props.length; i++) {
    const prop = props[i];
    if (hasOwn(node, prop) && node[prop] != null) {
      return node[prop];
    }
  }
  return defaultValue;
}
const AST_NODE_PROPS_KEYS = [
  ...PROPS_BODY,
  ...PROPS_CASES,
  ...PROPS_STATIC,
  ...PROPS_ITEMS,
  ...PROPS_KEY,
  ...PROPS_MODIFIER,
  ...PROPS_VALUE,
  ...PROPS_TYPE
];
function createUnhandleNodeError(type) {
  return new Error(`unhandled node type: ${type}`);
}
function format(ast) {
  const msg = (ctx) => formatParts(ctx, ast);
  return msg;
}
function formatParts(ctx, ast) {
  const body = resolveBody(ast);
  if (body == null) {
    throw createUnhandleNodeError(
      0
      /* NodeTypes.Resource */
    );
  }
  const type = resolveType(body);
  if (type === 1) {
    const plural = body;
    const cases = resolveCases(plural);
    return ctx.plural(cases.reduce((messages, c) => [
      ...messages,
      formatMessageParts(ctx, c)
    ], []));
  } else {
    return formatMessageParts(ctx, body);
  }
}
function formatMessageParts(ctx, node) {
  const static_ = resolveStatic(node);
  if (static_ != null) {
    return ctx.type === "text" ? static_ : ctx.normalize([static_]);
  } else {
    const messages = resolveItems(node).reduce((acm, c) => [...acm, formatMessagePart(ctx, c)], []);
    return ctx.normalize(messages);
  }
}
function formatMessagePart(ctx, node) {
  const type = resolveType(node);
  switch (type) {
    case 3: {
      return resolveValue$1(node, type);
    }
    case 9: {
      return resolveValue$1(node, type);
    }
    case 4: {
      const named = node;
      if (hasOwn(named, "k") && named.k) {
        return ctx.interpolate(ctx.named(named.k));
      }
      if (hasOwn(named, "key") && named.key) {
        return ctx.interpolate(ctx.named(named.key));
      }
      throw createUnhandleNodeError(type);
    }
    case 5: {
      const list = node;
      if (hasOwn(list, "i") && isNumber(list.i)) {
        return ctx.interpolate(ctx.list(list.i));
      }
      if (hasOwn(list, "index") && isNumber(list.index)) {
        return ctx.interpolate(ctx.list(list.index));
      }
      throw createUnhandleNodeError(type);
    }
    case 6: {
      const linked = node;
      const modifier = resolveLinkedModifier(linked);
      const key = resolveLinkedKey(linked);
      return ctx.linked(formatMessagePart(ctx, key), modifier ? formatMessagePart(ctx, modifier) : void 0, ctx.type);
    }
    case 7: {
      return resolveValue$1(node, type);
    }
    case 8: {
      return resolveValue$1(node, type);
    }
    default:
      throw new Error(`unhandled node on format message part: ${type}`);
  }
}
const WARN_MESSAGE = `Detected HTML in '{source}' message. Recommend not using HTML messages to avoid XSS.`;
function checkHtmlMessage(source, warnHtmlMessage) {
  if (warnHtmlMessage && detectHtmlTag(source)) {
    warn(format$1(WARN_MESSAGE, { source }));
  }
}
const defaultOnCacheKey = (message) => message;
let compileCache = create();
function baseCompile(message, options = {}) {
  let detectError = false;
  const onError = options.onError || defaultOnError;
  options.onError = (err) => {
    detectError = true;
    onError(err);
  };
  return { ...baseCompile$1(message, options), detectError };
}
// @__NO_SIDE_EFFECTS__
function compile(message, context) {
  if (!__INTLIFY_DROP_MESSAGE_COMPILER__ && isString(message)) {
    const warnHtmlMessage = isBoolean(context.warnHtmlMessage) ? context.warnHtmlMessage : true;
    checkHtmlMessage(message, warnHtmlMessage);
    const onCacheKey = context.onCacheKey || defaultOnCacheKey;
    const cacheKey = onCacheKey(message);
    const cached = compileCache[cacheKey];
    if (cached) {
      return cached;
    }
    const { ast, detectError } = baseCompile(message, {
      ...context,
      location: true,
      jit: true
    });
    const msg = format(ast);
    return !detectError ? compileCache[cacheKey] = msg : msg;
  } else {
    if (!isMessageAST(message)) {
      warn(`the message that is resolve with key '${context.key}' is not supported for jit compilation`);
      return (() => message);
    }
    const cacheKey = message.cacheKey;
    if (cacheKey) {
      const cached = compileCache[cacheKey];
      if (cached) {
        return cached;
      }
      return compileCache[cacheKey] = format(message);
    } else {
      return format(message);
    }
  }
}
let devtools = null;
function setDevToolsHook(hook) {
  devtools = hook;
}
function initI18nDevTools(i18n2, version, meta) {
  devtools && devtools.emit("i18n:init", {
    timestamp: Date.now(),
    i18n: i18n2,
    version,
    meta
  });
}
const translateDevTools = /* @__PURE__ */ createDevToolsHook("function:translate");
function createDevToolsHook(hook) {
  return (payloads) => devtools && devtools.emit(hook, payloads);
}
const CoreErrorCodes = {
  INVALID_ARGUMENT: COMPILE_ERROR_CODES_EXTEND_POINT,
  // 17
  INVALID_DATE_ARGUMENT: 18,
  INVALID_ISO_DATE_ARGUMENT: 19,
  NOT_SUPPORT_NON_STRING_MESSAGE: 20,
  NOT_SUPPORT_LOCALE_PROMISE_VALUE: 21,
  NOT_SUPPORT_LOCALE_ASYNC_FUNCTION: 22,
  NOT_SUPPORT_LOCALE_TYPE: 23
};
const CORE_ERROR_CODES_EXTEND_POINT = 24;
function createCoreError(code) {
  return createCompileError(code, null, { messages: errorMessages$1 });
}
const errorMessages$1 = {
  [CoreErrorCodes.INVALID_ARGUMENT]: "Invalid arguments",
  [CoreErrorCodes.INVALID_DATE_ARGUMENT]: "The date provided is an invalid Date object.Make sure your Date represents a valid date.",
  [CoreErrorCodes.INVALID_ISO_DATE_ARGUMENT]: "The argument provided is not a valid ISO date string",
  [CoreErrorCodes.NOT_SUPPORT_NON_STRING_MESSAGE]: "Not support non-string message",
  [CoreErrorCodes.NOT_SUPPORT_LOCALE_PROMISE_VALUE]: "cannot support promise value",
  [CoreErrorCodes.NOT_SUPPORT_LOCALE_ASYNC_FUNCTION]: "cannot support async function",
  [CoreErrorCodes.NOT_SUPPORT_LOCALE_TYPE]: "cannot support locale type"
};
function getLocale$1(context, options) {
  return options.locale != null ? resolveLocale(options.locale) : resolveLocale(context.locale);
}
let _resolveLocale;
function resolveLocale(locale) {
  if (isString(locale)) {
    return locale;
  } else {
    if (isFunction(locale)) {
      if (locale.resolvedOnce && _resolveLocale != null) {
        return _resolveLocale;
      } else if (locale.constructor.name === "Function") {
        const resolve = locale();
        if (isPromise(resolve)) {
          throw createCoreError(CoreErrorCodes.NOT_SUPPORT_LOCALE_PROMISE_VALUE);
        }
        return _resolveLocale = resolve;
      } else {
        throw createCoreError(CoreErrorCodes.NOT_SUPPORT_LOCALE_ASYNC_FUNCTION);
      }
    } else {
      throw createCoreError(CoreErrorCodes.NOT_SUPPORT_LOCALE_TYPE);
    }
  }
}
function fallbackWithSimple(ctx, fallback, start) {
  return [.../* @__PURE__ */ new Set([
    start,
    ...isArray(fallback) ? fallback : isObject(fallback) ? Object.keys(fallback) : isString(fallback) ? [fallback] : [start]
  ])];
}
function fallbackWithLocaleChain(ctx, fallback, start) {
  const startLocale = isString(start) ? start : DEFAULT_LOCALE;
  const context = ctx;
  if (!context.__localeChainCache) {
    context.__localeChainCache = /* @__PURE__ */ new Map();
  }
  let chain = context.__localeChainCache.get(startLocale);
  if (!chain) {
    chain = [];
    let block = [start];
    while (isArray(block)) {
      block = appendBlockToChain(chain, block, fallback);
    }
    const defaults = isArray(fallback) || !isPlainObject$8(fallback) ? fallback : fallback["default"] ? fallback["default"] : null;
    block = isString(defaults) ? [defaults] : defaults;
    if (isArray(block)) {
      appendBlockToChain(chain, block, false);
    }
    context.__localeChainCache.set(startLocale, chain);
  }
  return chain;
}
function appendBlockToChain(chain, block, blocks) {
  let follow = true;
  for (let i = 0; i < block.length && isBoolean(follow); i++) {
    const locale = block[i];
    if (isString(locale)) {
      follow = appendLocaleToChain(chain, block[i], blocks);
    }
  }
  return follow;
}
function appendLocaleToChain(chain, locale, blocks) {
  let follow;
  const tokens = locale.split("-");
  do {
    const target = tokens.join("-");
    follow = appendItemToChain(chain, target, blocks);
    tokens.splice(-1, 1);
  } while (tokens.length && follow === true);
  return follow;
}
function appendItemToChain(chain, target, blocks) {
  let follow = false;
  if (!chain.includes(target)) {
    follow = true;
    if (target) {
      follow = target[target.length - 1] !== "!";
      const locale = target.replace(/!/g, "");
      chain.push(locale);
      if ((isArray(blocks) || isPlainObject$8(blocks)) && blocks[locale]) {
        follow = blocks[locale];
      }
    }
  }
  return follow;
}
const pathStateMachine = [];
pathStateMachine[
  0
  /* States.BEFORE_PATH */
] = {
  [
    "w"
    /* PathCharTypes.WORKSPACE */
  ]: [
    0
    /* States.BEFORE_PATH */
  ],
  [
    "i"
    /* PathCharTypes.IDENT */
  ]: [
    3,
    0
    /* Actions.APPEND */
  ],
  [
    "["
    /* PathCharTypes.LEFT_BRACKET */
  ]: [
    4
    /* States.IN_SUB_PATH */
  ],
  [
    "o"
    /* PathCharTypes.END_OF_FAIL */
  ]: [
    7
    /* States.AFTER_PATH */
  ]
};
pathStateMachine[
  1
  /* States.IN_PATH */
] = {
  [
    "w"
    /* PathCharTypes.WORKSPACE */
  ]: [
    1
    /* States.IN_PATH */
  ],
  [
    "."
    /* PathCharTypes.DOT */
  ]: [
    2
    /* States.BEFORE_IDENT */
  ],
  [
    "["
    /* PathCharTypes.LEFT_BRACKET */
  ]: [
    4
    /* States.IN_SUB_PATH */
  ],
  [
    "o"
    /* PathCharTypes.END_OF_FAIL */
  ]: [
    7
    /* States.AFTER_PATH */
  ]
};
pathStateMachine[
  2
  /* States.BEFORE_IDENT */
] = {
  [
    "w"
    /* PathCharTypes.WORKSPACE */
  ]: [
    2
    /* States.BEFORE_IDENT */
  ],
  [
    "i"
    /* PathCharTypes.IDENT */
  ]: [
    3,
    0
    /* Actions.APPEND */
  ],
  [
    "0"
    /* PathCharTypes.ZERO */
  ]: [
    3,
    0
    /* Actions.APPEND */
  ]
};
pathStateMachine[
  3
  /* States.IN_IDENT */
] = {
  [
    "i"
    /* PathCharTypes.IDENT */
  ]: [
    3,
    0
    /* Actions.APPEND */
  ],
  [
    "0"
    /* PathCharTypes.ZERO */
  ]: [
    3,
    0
    /* Actions.APPEND */
  ],
  [
    "w"
    /* PathCharTypes.WORKSPACE */
  ]: [
    1,
    1
    /* Actions.PUSH */
  ],
  [
    "."
    /* PathCharTypes.DOT */
  ]: [
    2,
    1
    /* Actions.PUSH */
  ],
  [
    "["
    /* PathCharTypes.LEFT_BRACKET */
  ]: [
    4,
    1
    /* Actions.PUSH */
  ],
  [
    "o"
    /* PathCharTypes.END_OF_FAIL */
  ]: [
    7,
    1
    /* Actions.PUSH */
  ]
};
pathStateMachine[
  4
  /* States.IN_SUB_PATH */
] = {
  [
    "'"
    /* PathCharTypes.SINGLE_QUOTE */
  ]: [
    5,
    0
    /* Actions.APPEND */
  ],
  [
    '"'
    /* PathCharTypes.DOUBLE_QUOTE */
  ]: [
    6,
    0
    /* Actions.APPEND */
  ],
  [
    "["
    /* PathCharTypes.LEFT_BRACKET */
  ]: [
    4,
    2
    /* Actions.INC_SUB_PATH_DEPTH */
  ],
  [
    "]"
    /* PathCharTypes.RIGHT_BRACKET */
  ]: [
    1,
    3
    /* Actions.PUSH_SUB_PATH */
  ],
  [
    "o"
    /* PathCharTypes.END_OF_FAIL */
  ]: 8,
  [
    "l"
    /* PathCharTypes.ELSE */
  ]: [
    4,
    0
    /* Actions.APPEND */
  ]
};
pathStateMachine[
  5
  /* States.IN_SINGLE_QUOTE */
] = {
  [
    "'"
    /* PathCharTypes.SINGLE_QUOTE */
  ]: [
    4,
    0
    /* Actions.APPEND */
  ],
  [
    "o"
    /* PathCharTypes.END_OF_FAIL */
  ]: 8,
  [
    "l"
    /* PathCharTypes.ELSE */
  ]: [
    5,
    0
    /* Actions.APPEND */
  ]
};
pathStateMachine[
  6
  /* States.IN_DOUBLE_QUOTE */
] = {
  [
    '"'
    /* PathCharTypes.DOUBLE_QUOTE */
  ]: [
    4,
    0
    /* Actions.APPEND */
  ],
  [
    "o"
    /* PathCharTypes.END_OF_FAIL */
  ]: 8,
  [
    "l"
    /* PathCharTypes.ELSE */
  ]: [
    6,
    0
    /* Actions.APPEND */
  ]
};
const literalValueRE = /^\s?(?:true|false|-?[\d.]+|'[^']*'|"[^"]*")\s?$/;
function isLiteral(exp) {
  return literalValueRE.test(exp);
}
function stripQuotes(str) {
  const a = str.charCodeAt(0);
  const b = str.charCodeAt(str.length - 1);
  return a === b && (a === 34 || a === 39) ? str.slice(1, -1) : str;
}
function getPathCharType(ch) {
  if (ch === void 0 || ch === null) {
    return "o";
  }
  const code = ch.charCodeAt(0);
  switch (code) {
    case 91:
    // [
    case 93:
    // ]
    case 46:
    // .
    case 34:
    // "
    case 39:
      return ch;
    case 95:
    // _
    case 36:
    // $
    case 45:
      return "i";
    case 9:
    // Tab (HT)
    case 10:
    // Newline (LF)
    case 13:
    // Return (CR)
    case 160:
    // No-break space (NBSP)
    case 65279:
    // Byte Order Mark (BOM)
    case 8232:
    // Line Separator (LS)
    case 8233:
      return "w";
  }
  return "i";
}
function formatSubPath(path) {
  const trimmed = path.trim();
  if (path.charAt(0) === "0" && isNaN(parseInt(path))) {
    return false;
  }
  return isLiteral(trimmed) ? stripQuotes(trimmed) : "*" + trimmed;
}
function parse(path) {
  const keys = [];
  let index = -1;
  let mode = 0;
  let subPathDepth = 0;
  let c;
  let key;
  let newChar;
  let type;
  let transition;
  let action;
  let typeMap;
  const actions = [];
  actions[
    0
    /* Actions.APPEND */
  ] = () => {
    if (key === void 0) {
      key = newChar;
    } else {
      key += newChar;
    }
  };
  actions[
    1
    /* Actions.PUSH */
  ] = () => {
    if (key !== void 0) {
      keys.push(key);
      key = void 0;
    }
  };
  actions[
    2
    /* Actions.INC_SUB_PATH_DEPTH */
  ] = () => {
    actions[
      0
      /* Actions.APPEND */
    ]();
    subPathDepth++;
  };
  actions[
    3
    /* Actions.PUSH_SUB_PATH */
  ] = () => {
    if (subPathDepth > 0) {
      subPathDepth--;
      mode = 4;
      actions[
        0
        /* Actions.APPEND */
      ]();
    } else {
      subPathDepth = 0;
      if (key === void 0) {
        return false;
      }
      key = formatSubPath(key);
      if (key === false) {
        return false;
      } else {
        actions[
          1
          /* Actions.PUSH */
        ]();
      }
    }
  };
  function maybeUnescapeQuote() {
    const nextChar = path[index + 1];
    if (mode === 5 && nextChar === "'" || mode === 6 && nextChar === '"') {
      index++;
      newChar = "\\" + nextChar;
      actions[
        0
        /* Actions.APPEND */
      ]();
      return true;
    }
  }
  while (mode !== null) {
    index++;
    c = path[index];
    if (c === "\\" && maybeUnescapeQuote()) {
      continue;
    }
    type = getPathCharType(c);
    typeMap = pathStateMachine[mode];
    transition = typeMap[type] || typeMap[
      "l"
      /* PathCharTypes.ELSE */
    ] || 8;
    if (transition === 8) {
      return;
    }
    mode = transition[0];
    if (transition[1] !== void 0) {
      action = actions[transition[1]];
      if (action) {
        newChar = c;
        if (action() === false) {
          return;
        }
      }
    }
    if (mode === 7) {
      return keys;
    }
  }
}
const cache = /* @__PURE__ */ new Map();
function resolveWithKeyValue(obj, path) {
  return isObject(obj) ? obj[path] : null;
}
function resolveValue(obj, path) {
  if (!isObject(obj)) {
    return null;
  }
  let hit = cache.get(path);
  if (!hit) {
    hit = parse(path);
    if (hit) {
      cache.set(path, hit);
    }
  }
  if (!hit) {
    return null;
  }
  const len = hit.length;
  let last = obj;
  let i = 0;
  while (i < len) {
    const key = hit[i];
    if (AST_NODE_PROPS_KEYS.includes(key) && isMessageAST(last)) {
      return null;
    }
    const val = last[key];
    if (val === void 0) {
      return null;
    }
    if (isFunction(last)) {
      return null;
    }
    last = val;
    i++;
  }
  return last;
}
const CoreWarnCodes = {
  NOT_FOUND_KEY: 1,
  FALLBACK_TO_TRANSLATE: 2,
  CANNOT_FORMAT_NUMBER: 3,
  FALLBACK_TO_NUMBER_FORMAT: 4,
  CANNOT_FORMAT_DATE: 5,
  FALLBACK_TO_DATE_FORMAT: 6,
  EXPERIMENTAL_CUSTOM_MESSAGE_COMPILER: 7
};
const CORE_WARN_CODES_EXTEND_POINT = 8;
const warnMessages$1 = {
  [CoreWarnCodes.NOT_FOUND_KEY]: `Not found '{key}' key in '{locale}' locale messages.`,
  [CoreWarnCodes.FALLBACK_TO_TRANSLATE]: `Fall back to translate '{key}' key with '{target}' locale.`,
  [CoreWarnCodes.CANNOT_FORMAT_NUMBER]: `Cannot format a number value due to not supported Intl.NumberFormat.`,
  [CoreWarnCodes.FALLBACK_TO_NUMBER_FORMAT]: `Fall back to number format '{key}' key with '{target}' locale.`,
  [CoreWarnCodes.CANNOT_FORMAT_DATE]: `Cannot format a date value due to not supported Intl.DateTimeFormat.`,
  [CoreWarnCodes.FALLBACK_TO_DATE_FORMAT]: `Fall back to datetime format '{key}' key with '{target}' locale.`,
  [CoreWarnCodes.EXPERIMENTAL_CUSTOM_MESSAGE_COMPILER]: `This project is using Custom Message Compiler, which is an experimental feature. It may receive breaking changes or be removed in the future.`
};
function getWarnMessage$1(code, ...args) {
  return format$1(warnMessages$1[code], ...args);
}
const VERSION$1 = "10.0.8";
const NOT_REOSLVED = -1;
const DEFAULT_LOCALE = "en-US";
const MISSING_RESOLVE_VALUE = "";
const capitalize = (str) => `${str.charAt(0).toLocaleUpperCase()}${str.substr(1)}`;
function getDefaultLinkedModifiers() {
  return {
    upper: (val, type) => {
      return type === "text" && isString(val) ? val.toUpperCase() : type === "vnode" && isObject(val) && "__v_isVNode" in val ? val.children.toUpperCase() : val;
    },
    lower: (val, type) => {
      return type === "text" && isString(val) ? val.toLowerCase() : type === "vnode" && isObject(val) && "__v_isVNode" in val ? val.children.toLowerCase() : val;
    },
    capitalize: (val, type) => {
      return type === "text" && isString(val) ? capitalize(val) : type === "vnode" && isObject(val) && "__v_isVNode" in val ? capitalize(val.children) : val;
    }
  };
}
let _compiler;
function registerMessageCompiler(compiler) {
  _compiler = compiler;
}
let _resolver;
function registerMessageResolver(resolver) {
  _resolver = resolver;
}
let _fallbacker;
function registerLocaleFallbacker(fallbacker) {
  _fallbacker = fallbacker;
}
let _additionalMeta = null;
const setAdditionalMeta = /* @__NO_SIDE_EFFECTS__ */ (meta) => {
  _additionalMeta = meta;
};
const getAdditionalMeta = /* @__NO_SIDE_EFFECTS__ */ () => _additionalMeta;
let _fallbackContext = null;
const setFallbackContext = (context) => {
  _fallbackContext = context;
};
const getFallbackContext = () => _fallbackContext;
let _cid = 0;
function createCoreContext(options = {}) {
  const onWarn = isFunction(options.onWarn) ? options.onWarn : warn;
  const version = isString(options.version) ? options.version : VERSION$1;
  const locale = isString(options.locale) || isFunction(options.locale) ? options.locale : DEFAULT_LOCALE;
  const _locale = isFunction(locale) ? DEFAULT_LOCALE : locale;
  const fallbackLocale = isArray(options.fallbackLocale) || isPlainObject$8(options.fallbackLocale) || isString(options.fallbackLocale) || options.fallbackLocale === false ? options.fallbackLocale : _locale;
  const messages = isPlainObject$8(options.messages) ? options.messages : createResources(_locale);
  const datetimeFormats = isPlainObject$8(options.datetimeFormats) ? options.datetimeFormats : createResources(_locale);
  const numberFormats = isPlainObject$8(options.numberFormats) ? options.numberFormats : createResources(_locale);
  const modifiers = assign(create(), options.modifiers, getDefaultLinkedModifiers());
  const pluralRules = options.pluralRules || create();
  const missing = isFunction(options.missing) ? options.missing : null;
  const missingWarn = isBoolean(options.missingWarn) || isRegExp(options.missingWarn) ? options.missingWarn : true;
  const fallbackWarn = isBoolean(options.fallbackWarn) || isRegExp(options.fallbackWarn) ? options.fallbackWarn : true;
  const fallbackFormat = !!options.fallbackFormat;
  const unresolving = !!options.unresolving;
  const postTranslation = isFunction(options.postTranslation) ? options.postTranslation : null;
  const processor = isPlainObject$8(options.processor) ? options.processor : null;
  const warnHtmlMessage = isBoolean(options.warnHtmlMessage) ? options.warnHtmlMessage : true;
  const escapeParameter = !!options.escapeParameter;
  const messageCompiler = isFunction(options.messageCompiler) ? options.messageCompiler : _compiler;
  if (isFunction(options.messageCompiler)) {
    warnOnce(getWarnMessage$1(CoreWarnCodes.EXPERIMENTAL_CUSTOM_MESSAGE_COMPILER));
  }
  const messageResolver = isFunction(options.messageResolver) ? options.messageResolver : _resolver || resolveWithKeyValue;
  const localeFallbacker = isFunction(options.localeFallbacker) ? options.localeFallbacker : _fallbacker || fallbackWithSimple;
  const fallbackContext = isObject(options.fallbackContext) ? options.fallbackContext : void 0;
  const internalOptions = options;
  const __datetimeFormatters = isObject(internalOptions.__datetimeFormatters) ? internalOptions.__datetimeFormatters : /* @__PURE__ */ new Map();
  const __numberFormatters = isObject(internalOptions.__numberFormatters) ? internalOptions.__numberFormatters : /* @__PURE__ */ new Map();
  const __meta = isObject(internalOptions.__meta) ? internalOptions.__meta : {};
  _cid++;
  const context = {
    version,
    cid: _cid,
    locale,
    fallbackLocale,
    messages,
    modifiers,
    pluralRules,
    missing,
    missingWarn,
    fallbackWarn,
    fallbackFormat,
    unresolving,
    postTranslation,
    processor,
    warnHtmlMessage,
    escapeParameter,
    messageCompiler,
    messageResolver,
    localeFallbacker,
    fallbackContext,
    onWarn,
    __meta
  };
  {
    context.datetimeFormats = datetimeFormats;
    context.numberFormats = numberFormats;
    context.__datetimeFormatters = __datetimeFormatters;
    context.__numberFormatters = __numberFormatters;
  }
  {
    context.__v_emitter = internalOptions.__v_emitter != null ? internalOptions.__v_emitter : void 0;
  }
  {
    initI18nDevTools(context, version, __meta);
  }
  return context;
}
const createResources = (locale) => ({ [locale]: create() });
function isTranslateFallbackWarn(fallback, key) {
  return fallback instanceof RegExp ? fallback.test(key) : fallback;
}
function isTranslateMissingWarn(missing, key) {
  return missing instanceof RegExp ? missing.test(key) : missing;
}
function handleMissing(context, key, locale, missingWarn, type) {
  const { missing, onWarn } = context;
  {
    const emitter = context.__v_emitter;
    if (emitter) {
      emitter.emit("missing", {
        locale,
        key,
        type,
        groupId: `${type}:${key}`
      });
    }
  }
  if (missing !== null) {
    const ret = missing(context, locale, key, type);
    return isString(ret) ? ret : key;
  } else {
    if (isTranslateMissingWarn(missingWarn, key)) {
      onWarn(getWarnMessage$1(CoreWarnCodes.NOT_FOUND_KEY, { key, locale }));
    }
    return key;
  }
}
function updateFallbackLocale(ctx, locale, fallback) {
  const context = ctx;
  context.__localeChainCache = /* @__PURE__ */ new Map();
  ctx.localeFallbacker(ctx, fallback, locale);
}
function isAlmostSameLocale(locale, compareLocale) {
  if (locale === compareLocale)
    return false;
  return locale.split("-")[0] === compareLocale.split("-")[0];
}
function isImplicitFallback(targetLocale, locales) {
  const index = locales.indexOf(targetLocale);
  if (index === -1) {
    return false;
  }
  for (let i = index + 1; i < locales.length; i++) {
    if (isAlmostSameLocale(targetLocale, locales[i])) {
      return true;
    }
  }
  return false;
}
const intlDefined = typeof Intl !== "undefined";
const Availabilities = {
  dateTimeFormat: intlDefined && typeof Intl.DateTimeFormat !== "undefined",
  numberFormat: intlDefined && typeof Intl.NumberFormat !== "undefined"
};
function datetime(context, ...args) {
  const { datetimeFormats, unresolving, fallbackLocale, onWarn, localeFallbacker } = context;
  const { __datetimeFormatters } = context;
  if (!Availabilities.dateTimeFormat) {
    onWarn(getWarnMessage$1(CoreWarnCodes.CANNOT_FORMAT_DATE));
    return MISSING_RESOLVE_VALUE;
  }
  const [key, value, options, overrides] = parseDateTimeArgs(...args);
  const missingWarn = isBoolean(options.missingWarn) ? options.missingWarn : context.missingWarn;
  const fallbackWarn = isBoolean(options.fallbackWarn) ? options.fallbackWarn : context.fallbackWarn;
  const part = !!options.part;
  const locale = getLocale$1(context, options);
  const locales = localeFallbacker(
    context,
    // eslint-disable-line @typescript-eslint/no-explicit-any
    fallbackLocale,
    locale
  );
  if (!isString(key) || key === "") {
    return new Intl.DateTimeFormat(locale, overrides).format(value);
  }
  let datetimeFormat = {};
  let targetLocale;
  let format2 = null;
  let from = locale;
  let to = null;
  const type = "datetime format";
  for (let i = 0; i < locales.length; i++) {
    targetLocale = to = locales[i];
    if (locale !== targetLocale && isTranslateFallbackWarn(fallbackWarn, key)) {
      onWarn(getWarnMessage$1(CoreWarnCodes.FALLBACK_TO_DATE_FORMAT, {
        key,
        target: targetLocale
      }));
    }
    if (locale !== targetLocale) {
      const emitter = context.__v_emitter;
      if (emitter) {
        emitter.emit("fallback", {
          type,
          key,
          from,
          to,
          groupId: `${type}:${key}`
        });
      }
    }
    datetimeFormat = datetimeFormats[targetLocale] || {};
    format2 = datetimeFormat[key];
    if (isPlainObject$8(format2))
      break;
    handleMissing(context, key, targetLocale, missingWarn, type);
    from = to;
  }
  if (!isPlainObject$8(format2) || !isString(targetLocale)) {
    return unresolving ? NOT_REOSLVED : key;
  }
  let id = `${targetLocale}__${key}`;
  if (!isEmptyObject(overrides)) {
    id = `${id}__${JSON.stringify(overrides)}`;
  }
  let formatter = __datetimeFormatters.get(id);
  if (!formatter) {
    formatter = new Intl.DateTimeFormat(targetLocale, assign({}, format2, overrides));
    __datetimeFormatters.set(id, formatter);
  }
  return !part ? formatter.format(value) : formatter.formatToParts(value);
}
const DATETIME_FORMAT_OPTIONS_KEYS = [
  "localeMatcher",
  "weekday",
  "era",
  "year",
  "month",
  "day",
  "hour",
  "minute",
  "second",
  "timeZoneName",
  "formatMatcher",
  "hour12",
  "timeZone",
  "dateStyle",
  "timeStyle",
  "calendar",
  "dayPeriod",
  "numberingSystem",
  "hourCycle",
  "fractionalSecondDigits"
];
function parseDateTimeArgs(...args) {
  const [arg1, arg2, arg3, arg4] = args;
  const options = create();
  let overrides = create();
  let value;
  if (isString(arg1)) {
    const matches = arg1.match(/(\d{4}-\d{2}-\d{2})(T|\s)?(.*)/);
    if (!matches) {
      throw createCoreError(CoreErrorCodes.INVALID_ISO_DATE_ARGUMENT);
    }
    const dateTime = matches[3] ? matches[3].trim().startsWith("T") ? `${matches[1].trim()}${matches[3].trim()}` : `${matches[1].trim()}T${matches[3].trim()}` : matches[1].trim();
    value = new Date(dateTime);
    try {
      value.toISOString();
    } catch {
      throw createCoreError(CoreErrorCodes.INVALID_ISO_DATE_ARGUMENT);
    }
  } else if (isDate(arg1)) {
    if (isNaN(arg1.getTime())) {
      throw createCoreError(CoreErrorCodes.INVALID_DATE_ARGUMENT);
    }
    value = arg1;
  } else if (isNumber(arg1)) {
    value = arg1;
  } else {
    throw createCoreError(CoreErrorCodes.INVALID_ARGUMENT);
  }
  if (isString(arg2)) {
    options.key = arg2;
  } else if (isPlainObject$8(arg2)) {
    Object.keys(arg2).forEach((key) => {
      if (DATETIME_FORMAT_OPTIONS_KEYS.includes(key)) {
        overrides[key] = arg2[key];
      } else {
        options[key] = arg2[key];
      }
    });
  }
  if (isString(arg3)) {
    options.locale = arg3;
  } else if (isPlainObject$8(arg3)) {
    overrides = arg3;
  }
  if (isPlainObject$8(arg4)) {
    overrides = arg4;
  }
  return [options.key || "", value, options, overrides];
}
function clearDateTimeFormat(ctx, locale, format2) {
  const context = ctx;
  for (const key in format2) {
    const id = `${locale}__${key}`;
    if (!context.__datetimeFormatters.has(id)) {
      continue;
    }
    context.__datetimeFormatters.delete(id);
  }
}
function number(context, ...args) {
  const { numberFormats, unresolving, fallbackLocale, onWarn, localeFallbacker } = context;
  const { __numberFormatters } = context;
  if (!Availabilities.numberFormat) {
    onWarn(getWarnMessage$1(CoreWarnCodes.CANNOT_FORMAT_NUMBER));
    return MISSING_RESOLVE_VALUE;
  }
  const [key, value, options, overrides] = parseNumberArgs(...args);
  const missingWarn = isBoolean(options.missingWarn) ? options.missingWarn : context.missingWarn;
  const fallbackWarn = isBoolean(options.fallbackWarn) ? options.fallbackWarn : context.fallbackWarn;
  const part = !!options.part;
  const locale = getLocale$1(context, options);
  const locales = localeFallbacker(
    context,
    // eslint-disable-line @typescript-eslint/no-explicit-any
    fallbackLocale,
    locale
  );
  if (!isString(key) || key === "") {
    return new Intl.NumberFormat(locale, overrides).format(value);
  }
  let numberFormat = {};
  let targetLocale;
  let format2 = null;
  let from = locale;
  let to = null;
  const type = "number format";
  for (let i = 0; i < locales.length; i++) {
    targetLocale = to = locales[i];
    if (locale !== targetLocale && isTranslateFallbackWarn(fallbackWarn, key)) {
      onWarn(getWarnMessage$1(CoreWarnCodes.FALLBACK_TO_NUMBER_FORMAT, {
        key,
        target: targetLocale
      }));
    }
    if (locale !== targetLocale) {
      const emitter = context.__v_emitter;
      if (emitter) {
        emitter.emit("fallback", {
          type,
          key,
          from,
          to,
          groupId: `${type}:${key}`
        });
      }
    }
    numberFormat = numberFormats[targetLocale] || {};
    format2 = numberFormat[key];
    if (isPlainObject$8(format2))
      break;
    handleMissing(context, key, targetLocale, missingWarn, type);
    from = to;
  }
  if (!isPlainObject$8(format2) || !isString(targetLocale)) {
    return unresolving ? NOT_REOSLVED : key;
  }
  let id = `${targetLocale}__${key}`;
  if (!isEmptyObject(overrides)) {
    id = `${id}__${JSON.stringify(overrides)}`;
  }
  let formatter = __numberFormatters.get(id);
  if (!formatter) {
    formatter = new Intl.NumberFormat(targetLocale, assign({}, format2, overrides));
    __numberFormatters.set(id, formatter);
  }
  return !part ? formatter.format(value) : formatter.formatToParts(value);
}
const NUMBER_FORMAT_OPTIONS_KEYS = [
  "localeMatcher",
  "style",
  "currency",
  "currencyDisplay",
  "currencySign",
  "useGrouping",
  "minimumIntegerDigits",
  "minimumFractionDigits",
  "maximumFractionDigits",
  "minimumSignificantDigits",
  "maximumSignificantDigits",
  "compactDisplay",
  "notation",
  "signDisplay",
  "unit",
  "unitDisplay",
  "roundingMode",
  "roundingPriority",
  "roundingIncrement",
  "trailingZeroDisplay"
];
function parseNumberArgs(...args) {
  const [arg1, arg2, arg3, arg4] = args;
  const options = create();
  let overrides = create();
  if (!isNumber(arg1)) {
    throw createCoreError(CoreErrorCodes.INVALID_ARGUMENT);
  }
  const value = arg1;
  if (isString(arg2)) {
    options.key = arg2;
  } else if (isPlainObject$8(arg2)) {
    Object.keys(arg2).forEach((key) => {
      if (NUMBER_FORMAT_OPTIONS_KEYS.includes(key)) {
        overrides[key] = arg2[key];
      } else {
        options[key] = arg2[key];
      }
    });
  }
  if (isString(arg3)) {
    options.locale = arg3;
  } else if (isPlainObject$8(arg3)) {
    overrides = arg3;
  }
  if (isPlainObject$8(arg4)) {
    overrides = arg4;
  }
  return [options.key || "", value, options, overrides];
}
function clearNumberFormat(ctx, locale, format2) {
  const context = ctx;
  for (const key in format2) {
    const id = `${locale}__${key}`;
    if (!context.__numberFormatters.has(id)) {
      continue;
    }
    context.__numberFormatters.delete(id);
  }
}
const DEFAULT_MODIFIER = (str) => str;
const DEFAULT_MESSAGE = (ctx) => "";
const DEFAULT_MESSAGE_DATA_TYPE = "text";
const DEFAULT_NORMALIZE = (values) => values.length === 0 ? "" : join(values);
const DEFAULT_INTERPOLATE = toDisplayString;
function pluralDefault(choice, choicesLength) {
  choice = Math.abs(choice);
  if (choicesLength === 2) {
    return choice ? choice > 1 ? 1 : 0 : 1;
  }
  return choice ? Math.min(choice, 2) : 0;
}
function getPluralIndex(options) {
  const index = isNumber(options.pluralIndex) ? options.pluralIndex : -1;
  return options.named && (isNumber(options.named.count) || isNumber(options.named.n)) ? isNumber(options.named.count) ? options.named.count : isNumber(options.named.n) ? options.named.n : index : index;
}
function normalizeNamed(pluralIndex, props) {
  if (!props.count) {
    props.count = pluralIndex;
  }
  if (!props.n) {
    props.n = pluralIndex;
  }
}
function createMessageContext(options = {}) {
  const locale = options.locale;
  const pluralIndex = getPluralIndex(options);
  const pluralRule = isObject(options.pluralRules) && isString(locale) && isFunction(options.pluralRules[locale]) ? options.pluralRules[locale] : pluralDefault;
  const orgPluralRule = isObject(options.pluralRules) && isString(locale) && isFunction(options.pluralRules[locale]) ? pluralDefault : void 0;
  const plural = (messages) => {
    return messages[pluralRule(pluralIndex, messages.length, orgPluralRule)];
  };
  const _list = options.list || [];
  const list = (index) => _list[index];
  const _named = options.named || create();
  isNumber(options.pluralIndex) && normalizeNamed(pluralIndex, _named);
  const named = (key) => _named[key];
  function message(key, useLinked) {
    const msg = isFunction(options.messages) ? options.messages(key, !!useLinked) : isObject(options.messages) ? options.messages[key] : false;
    return !msg ? options.parent ? options.parent.message(key) : DEFAULT_MESSAGE : msg;
  }
  const _modifier = (name) => options.modifiers ? options.modifiers[name] : DEFAULT_MODIFIER;
  const normalize = isPlainObject$8(options.processor) && isFunction(options.processor.normalize) ? options.processor.normalize : DEFAULT_NORMALIZE;
  const interpolate = isPlainObject$8(options.processor) && isFunction(options.processor.interpolate) ? options.processor.interpolate : DEFAULT_INTERPOLATE;
  const type = isPlainObject$8(options.processor) && isString(options.processor.type) ? options.processor.type : DEFAULT_MESSAGE_DATA_TYPE;
  const linked = (key, ...args) => {
    const [arg1, arg2] = args;
    let type2 = "text";
    let modifier = "";
    if (args.length === 1) {
      if (isObject(arg1)) {
        modifier = arg1.modifier || modifier;
        type2 = arg1.type || type2;
      } else if (isString(arg1)) {
        modifier = arg1 || modifier;
      }
    } else if (args.length === 2) {
      if (isString(arg1)) {
        modifier = arg1 || modifier;
      }
      if (isString(arg2)) {
        type2 = arg2 || type2;
      }
    }
    const ret = message(key, true)(ctx);
    const msg = (
      // The message in vnode resolved with linked are returned as an array by processor.nomalize
      type2 === "vnode" && isArray(ret) && modifier ? ret[0] : ret
    );
    return modifier ? _modifier(modifier)(msg, type2) : msg;
  };
  const ctx = {
    [
      "list"
      /* HelperNameMap.LIST */
    ]: list,
    [
      "named"
      /* HelperNameMap.NAMED */
    ]: named,
    [
      "plural"
      /* HelperNameMap.PLURAL */
    ]: plural,
    [
      "linked"
      /* HelperNameMap.LINKED */
    ]: linked,
    [
      "message"
      /* HelperNameMap.MESSAGE */
    ]: message,
    [
      "type"
      /* HelperNameMap.TYPE */
    ]: type,
    [
      "interpolate"
      /* HelperNameMap.INTERPOLATE */
    ]: interpolate,
    [
      "normalize"
      /* HelperNameMap.NORMALIZE */
    ]: normalize,
    [
      "values"
      /* HelperNameMap.VALUES */
    ]: assign(create(), _list, _named)
  };
  return ctx;
}
const NOOP_MESSAGE_FUNCTION = () => "";
const isMessageFunction = (val) => isFunction(val);
function translate(context, ...args) {
  const { fallbackFormat, postTranslation, unresolving, messageCompiler, fallbackLocale, messages } = context;
  const [key, options] = parseTranslateArgs(...args);
  const missingWarn = isBoolean(options.missingWarn) ? options.missingWarn : context.missingWarn;
  const fallbackWarn = isBoolean(options.fallbackWarn) ? options.fallbackWarn : context.fallbackWarn;
  const escapeParameter = isBoolean(options.escapeParameter) ? options.escapeParameter : context.escapeParameter;
  const resolvedMessage = !!options.resolvedMessage;
  const defaultMsgOrKey = isString(options.default) || isBoolean(options.default) ? !isBoolean(options.default) ? options.default : !messageCompiler ? () => key : key : fallbackFormat ? !messageCompiler ? () => key : key : null;
  const enableDefaultMsg = fallbackFormat || defaultMsgOrKey != null && (isString(defaultMsgOrKey) || isFunction(defaultMsgOrKey));
  const locale = getLocale$1(context, options);
  escapeParameter && escapeParams(options);
  let [formatScope, targetLocale, message] = !resolvedMessage ? resolveMessageFormat(context, key, locale, fallbackLocale, fallbackWarn, missingWarn) : [
    key,
    locale,
    messages[locale] || create()
  ];
  let format2 = formatScope;
  let cacheBaseKey = key;
  if (!resolvedMessage && !(isString(format2) || isMessageAST(format2) || isMessageFunction(format2))) {
    if (enableDefaultMsg) {
      format2 = defaultMsgOrKey;
      cacheBaseKey = format2;
    }
  }
  if (!resolvedMessage && (!(isString(format2) || isMessageAST(format2) || isMessageFunction(format2)) || !isString(targetLocale))) {
    return unresolving ? NOT_REOSLVED : key;
  }
  if (isString(format2) && context.messageCompiler == null) {
    warn(`The message format compilation is not supported in this build. Because message compiler isn't included. You need to pre-compilation all message format. So translate function return '${key}'.`);
    return key;
  }
  let occurred = false;
  const onError = () => {
    occurred = true;
  };
  const msg = !isMessageFunction(format2) ? compileMessageFormat(context, key, targetLocale, format2, cacheBaseKey, onError) : format2;
  if (occurred) {
    return format2;
  }
  const ctxOptions = getMessageContextOptions(context, targetLocale, message, options);
  const msgContext = createMessageContext(ctxOptions);
  const messaged = evaluateMessage(context, msg, msgContext);
  let ret = postTranslation ? postTranslation(messaged, key) : messaged;
  if (escapeParameter && isString(ret)) {
    ret = sanitizeTranslatedHtml(ret);
  }
  {
    const payloads = {
      timestamp: Date.now(),
      key: isString(key) ? key : isMessageFunction(format2) ? format2.key : "",
      locale: targetLocale || (isMessageFunction(format2) ? format2.locale : ""),
      format: isString(format2) ? format2 : isMessageFunction(format2) ? format2.source : "",
      message: ret
    };
    payloads.meta = assign({}, context.__meta, /* @__PURE__ */ getAdditionalMeta() || {});
    translateDevTools(payloads);
  }
  return ret;
}
function escapeParams(options) {
  if (isArray(options.list)) {
    options.list = options.list.map((item) => isString(item) ? escapeHtml(item) : item);
  } else if (isObject(options.named)) {
    Object.keys(options.named).forEach((key) => {
      if (isString(options.named[key])) {
        options.named[key] = escapeHtml(options.named[key]);
      }
    });
  }
}
function resolveMessageFormat(context, key, locale, fallbackLocale, fallbackWarn, missingWarn) {
  const { messages, onWarn, messageResolver: resolveValue2, localeFallbacker } = context;
  const locales = localeFallbacker(context, fallbackLocale, locale);
  let message = create();
  let targetLocale;
  let format2 = null;
  let from = locale;
  let to = null;
  const type = "translate";
  for (let i = 0; i < locales.length; i++) {
    targetLocale = to = locales[i];
    if (locale !== targetLocale && !isAlmostSameLocale(locale, targetLocale) && isTranslateFallbackWarn(fallbackWarn, key)) {
      onWarn(getWarnMessage$1(CoreWarnCodes.FALLBACK_TO_TRANSLATE, {
        key,
        target: targetLocale
      }));
    }
    if (locale !== targetLocale) {
      const emitter = context.__v_emitter;
      if (emitter) {
        emitter.emit("fallback", {
          type,
          key,
          from,
          to,
          groupId: `${type}:${key}`
        });
      }
    }
    message = messages[targetLocale] || create();
    let start = null;
    let startTag;
    let endTag;
    if (inBrowser) {
      start = window.performance.now();
      startTag = "intlify-message-resolve-start";
      endTag = "intlify-message-resolve-end";
      mark && mark(startTag);
    }
    if ((format2 = resolveValue2(message, key)) === null) {
      format2 = message[key];
    }
    if (inBrowser) {
      const end = window.performance.now();
      const emitter = context.__v_emitter;
      if (emitter && start && format2) {
        emitter.emit("message-resolve", {
          type: "message-resolve",
          key,
          message: format2,
          time: end - start,
          groupId: `${type}:${key}`
        });
      }
      if (startTag && endTag && mark && measure) {
        mark(endTag);
        measure("intlify message resolve", startTag, endTag);
      }
    }
    if (isString(format2) || isMessageAST(format2) || isMessageFunction(format2)) {
      break;
    }
    if (!isImplicitFallback(targetLocale, locales)) {
      const missingRet = handleMissing(
        context,
        // eslint-disable-line @typescript-eslint/no-explicit-any
        key,
        targetLocale,
        missingWarn,
        type
      );
      if (missingRet !== key) {
        format2 = missingRet;
      }
    }
    from = to;
  }
  return [format2, targetLocale, message];
}
function compileMessageFormat(context, key, targetLocale, format2, cacheBaseKey, onError) {
  const { messageCompiler, warnHtmlMessage } = context;
  if (isMessageFunction(format2)) {
    const msg2 = format2;
    msg2.locale = msg2.locale || targetLocale;
    msg2.key = msg2.key || key;
    return msg2;
  }
  if (messageCompiler == null) {
    const msg2 = (() => format2);
    msg2.locale = targetLocale;
    msg2.key = key;
    return msg2;
  }
  let start = null;
  let startTag;
  let endTag;
  if (inBrowser) {
    start = window.performance.now();
    startTag = "intlify-message-compilation-start";
    endTag = "intlify-message-compilation-end";
    mark && mark(startTag);
  }
  const msg = messageCompiler(format2, getCompileContext(context, targetLocale, cacheBaseKey, format2, warnHtmlMessage, onError));
  if (inBrowser) {
    const end = window.performance.now();
    const emitter = context.__v_emitter;
    if (emitter && start) {
      emitter.emit("message-compilation", {
        type: "message-compilation",
        message: format2,
        time: end - start,
        groupId: `${"translate"}:${key}`
      });
    }
    if (startTag && endTag && mark && measure) {
      mark(endTag);
      measure("intlify message compilation", startTag, endTag);
    }
  }
  msg.locale = targetLocale;
  msg.key = key;
  msg.source = format2;
  return msg;
}
function evaluateMessage(context, msg, msgCtx) {
  let start = null;
  let startTag;
  let endTag;
  if (inBrowser) {
    start = window.performance.now();
    startTag = "intlify-message-evaluation-start";
    endTag = "intlify-message-evaluation-end";
    mark && mark(startTag);
  }
  const messaged = msg(msgCtx);
  if (inBrowser) {
    const end = window.performance.now();
    const emitter = context.__v_emitter;
    if (emitter && start) {
      emitter.emit("message-evaluation", {
        type: "message-evaluation",
        value: messaged,
        time: end - start,
        groupId: `${"translate"}:${msg.key}`
      });
    }
    if (startTag && endTag && mark && measure) {
      mark(endTag);
      measure("intlify message evaluation", startTag, endTag);
    }
  }
  return messaged;
}
function parseTranslateArgs(...args) {
  const [arg1, arg2, arg3] = args;
  const options = create();
  if (!isString(arg1) && !isNumber(arg1) && !isMessageFunction(arg1) && !isMessageAST(arg1)) {
    throw createCoreError(CoreErrorCodes.INVALID_ARGUMENT);
  }
  const key = isNumber(arg1) ? String(arg1) : isMessageFunction(arg1) ? arg1 : arg1;
  if (isNumber(arg2)) {
    options.plural = arg2;
  } else if (isString(arg2)) {
    options.default = arg2;
  } else if (isPlainObject$8(arg2) && !isEmptyObject(arg2)) {
    options.named = arg2;
  } else if (isArray(arg2)) {
    options.list = arg2;
  }
  if (isNumber(arg3)) {
    options.plural = arg3;
  } else if (isString(arg3)) {
    options.default = arg3;
  } else if (isPlainObject$8(arg3)) {
    assign(options, arg3);
  }
  return [key, options];
}
function getCompileContext(context, locale, key, source, warnHtmlMessage, onError) {
  return {
    locale,
    key,
    warnHtmlMessage,
    onError: (err) => {
      onError && onError(err);
      {
        const _source = getSourceForCodeFrame(source);
        const message = `Message compilation error: ${err.message}`;
        const codeFrame = err.location && _source && generateCodeFrame(_source, err.location.start.offset, err.location.end.offset);
        const emitter = context.__v_emitter;
        if (emitter && _source) {
          emitter.emit("compile-error", {
            message: _source,
            error: err.message,
            start: err.location && err.location.start.offset,
            end: err.location && err.location.end.offset,
            groupId: `${"translate"}:${key}`
          });
        }
        console.error(codeFrame ? `${message}
${codeFrame}` : message);
      }
    },
    onCacheKey: (source2) => generateFormatCacheKey(locale, key, source2)
  };
}
function getSourceForCodeFrame(source) {
  if (isString(source)) {
    return source;
  } else {
    if (source.loc && source.loc.source) {
      return source.loc.source;
    }
  }
}
function getMessageContextOptions(context, locale, message, options) {
  const { modifiers, pluralRules, messageResolver: resolveValue2, fallbackLocale, fallbackWarn, missingWarn, fallbackContext } = context;
  const resolveMessage = (key, useLinked) => {
    let val = resolveValue2(message, key);
    if (val == null && (fallbackContext || useLinked)) {
      const [, , message2] = resolveMessageFormat(
        fallbackContext || context,
        // NOTE: if has fallbackContext, fallback to root, else if use linked, fallback to local context
        key,
        locale,
        fallbackLocale,
        fallbackWarn,
        missingWarn
      );
      val = resolveValue2(message2, key);
    }
    if (isString(val) || isMessageAST(val)) {
      let occurred = false;
      const onError = () => {
        occurred = true;
      };
      const msg = compileMessageFormat(context, key, locale, val, key, onError);
      return !occurred ? msg : NOOP_MESSAGE_FUNCTION;
    } else if (isMessageFunction(val)) {
      return val;
    } else {
      return NOOP_MESSAGE_FUNCTION;
    }
  };
  const ctxOptions = {
    locale,
    modifiers,
    pluralRules,
    messages: resolveMessage
  };
  if (context.processor) {
    ctxOptions.processor = context.processor;
  }
  if (options.list) {
    ctxOptions.list = options.list;
  }
  if (options.named) {
    ctxOptions.named = options.named;
  }
  if (isNumber(options.plural)) {
    ctxOptions.pluralIndex = options.plural;
  }
  return ctxOptions;
}
{
  initFeatureFlags$1();
}
function getDevtoolsGlobalHook() {
  return getTarget().__VUE_DEVTOOLS_GLOBAL_HOOK__;
}
function getTarget() {
  return typeof navigator !== "undefined" && typeof window !== "undefined" ? window : typeof globalThis !== "undefined" ? globalThis : {};
}
const isProxyAvailable = typeof Proxy === "function";
const HOOK_SETUP = "devtools-plugin:setup";
const HOOK_PLUGIN_SETTINGS_SET = "plugin:settings:set";
let supported;
let perf;
function isPerformanceSupported() {
  var _a;
  if (supported !== void 0) {
    return supported;
  }
  if (typeof window !== "undefined" && window.performance) {
    supported = true;
    perf = window.performance;
  } else if (typeof globalThis !== "undefined" && ((_a = globalThis.perf_hooks) === null || _a === void 0 ? void 0 : _a.performance)) {
    supported = true;
    perf = globalThis.perf_hooks.performance;
  } else {
    supported = false;
  }
  return supported;
}
function now() {
  return isPerformanceSupported() ? perf.now() : Date.now();
}
class ApiProxy {
  constructor(plugin, hook) {
    this.target = null;
    this.targetQueue = [];
    this.onQueue = [];
    this.plugin = plugin;
    this.hook = hook;
    const defaultSettings = {};
    if (plugin.settings) {
      for (const id in plugin.settings) {
        const item = plugin.settings[id];
        defaultSettings[id] = item.defaultValue;
      }
    }
    const localSettingsSaveId = `__vue-devtools-plugin-settings__${plugin.id}`;
    let currentSettings = Object.assign({}, defaultSettings);
    try {
      const raw = localStorage.getItem(localSettingsSaveId);
      const data = JSON.parse(raw);
      Object.assign(currentSettings, data);
    } catch (e) {
    }
    this.fallbacks = {
      getSettings() {
        return currentSettings;
      },
      setSettings(value) {
        try {
          localStorage.setItem(localSettingsSaveId, JSON.stringify(value));
        } catch (e) {
        }
        currentSettings = value;
      },
      now() {
        return now();
      }
    };
    if (hook) {
      hook.on(HOOK_PLUGIN_SETTINGS_SET, (pluginId, value) => {
        if (pluginId === this.plugin.id) {
          this.fallbacks.setSettings(value);
        }
      });
    }
    this.proxiedOn = new Proxy({}, {
      get: (_target, prop) => {
        if (this.target) {
          return this.target.on[prop];
        } else {
          return (...args) => {
            this.onQueue.push({
              method: prop,
              args
            });
          };
        }
      }
    });
    this.proxiedTarget = new Proxy({}, {
      get: (_target, prop) => {
        if (this.target) {
          return this.target[prop];
        } else if (prop === "on") {
          return this.proxiedOn;
        } else if (Object.keys(this.fallbacks).includes(prop)) {
          return (...args) => {
            this.targetQueue.push({
              method: prop,
              args,
              resolve: () => {
              }
            });
            return this.fallbacks[prop](...args);
          };
        } else {
          return (...args) => {
            return new Promise((resolve) => {
              this.targetQueue.push({
                method: prop,
                args,
                resolve
              });
            });
          };
        }
      }
    });
  }
  async setRealTarget(target) {
    this.target = target;
    for (const item of this.onQueue) {
      this.target.on[item.method](...item.args);
    }
    for (const item of this.targetQueue) {
      item.resolve(await this.target[item.method](...item.args));
    }
  }
}
function setupDevtoolsPlugin(pluginDescriptor, setupFn) {
  const descriptor = pluginDescriptor;
  const target = getTarget();
  const hook = getDevtoolsGlobalHook();
  const enableProxy = isProxyAvailable && descriptor.enableEarlyProxy;
  if (hook && (target.__VUE_DEVTOOLS_PLUGIN_API_AVAILABLE__ || !enableProxy)) {
    hook.emit(HOOK_SETUP, pluginDescriptor, setupFn);
  } else {
    const proxy = enableProxy ? new ApiProxy(descriptor, hook) : null;
    const list = target.__VUE_DEVTOOLS_PLUGINS__ = target.__VUE_DEVTOOLS_PLUGINS__ || [];
    list.push({
      pluginDescriptor: descriptor,
      setupFn,
      proxy
    });
    if (proxy) {
      setupFn(proxy.proxiedTarget);
    }
  }
}
/*!
  * vue-i18n v10.0.8
  * (c) 2025 kazuya kawaguchi
  * Released under the MIT License.
  */
const VERSION = "10.0.8";
function initFeatureFlags() {
  if (typeof __VUE_I18N_FULL_INSTALL__ !== "boolean") {
    getGlobalThis$1().__VUE_I18N_FULL_INSTALL__ = true;
  }
  if (typeof __VUE_I18N_LEGACY_API__ !== "boolean") {
    getGlobalThis$1().__VUE_I18N_LEGACY_API__ = true;
  }
  if (typeof __INTLIFY_DROP_MESSAGE_COMPILER__ !== "boolean") {
    getGlobalThis$1().__INTLIFY_DROP_MESSAGE_COMPILER__ = false;
  }
  if (typeof __INTLIFY_PROD_DEVTOOLS__ !== "boolean") {
    getGlobalThis$1().__INTLIFY_PROD_DEVTOOLS__ = false;
  }
}
const I18nWarnCodes = {
  FALLBACK_TO_ROOT: CORE_WARN_CODES_EXTEND_POINT,
  // 8
  NOT_FOUND_PARENT_SCOPE: 9,
  IGNORE_OBJ_FLATTEN: 10,
  DEPRECATE_TC: 11
};
const warnMessages = {
  [I18nWarnCodes.FALLBACK_TO_ROOT]: `Fall back to {type} '{key}' with root locale.`,
  [I18nWarnCodes.NOT_FOUND_PARENT_SCOPE]: `Not found parent scope. use the global scope.`,
  [I18nWarnCodes.IGNORE_OBJ_FLATTEN]: `Ignore object flatten: '{key}' key has an string value`,
  [I18nWarnCodes.DEPRECATE_TC]: `'tc' and '$tc' has been deprecated in v10. Use 't' or '$t' instead. 'tc' and '$tc’ are going to remove in v11.`
};
function getWarnMessage(code, ...args) {
  return format$2(warnMessages[code], ...args);
}
const I18nErrorCodes = {
  // composer module errors
  UNEXPECTED_RETURN_TYPE: CORE_ERROR_CODES_EXTEND_POINT,
  // 24
  // legacy module errors
  INVALID_ARGUMENT: 25,
  // i18n module errors
  MUST_BE_CALL_SETUP_TOP: 26,
  NOT_INSTALLED: 27,
  // directive module errors
  REQUIRED_VALUE: 28,
  INVALID_VALUE: 29,
  // vue-devtools errors
  CANNOT_SETUP_VUE_DEVTOOLS_PLUGIN: 30,
  NOT_INSTALLED_WITH_PROVIDE: 31,
  // unexpected error
  UNEXPECTED_ERROR: 32,
  // not compatible legacy vue-i18n constructor
  NOT_COMPATIBLE_LEGACY_VUE_I18N: 33,
  // Not available Compostion API in Legacy API mode. Please make sure that the legacy API mode is working properly
  NOT_AVAILABLE_COMPOSITION_IN_LEGACY: 34
};
function createI18nError(code, ...args) {
  return createCompileError(code, null, { messages: errorMessages, args });
}
const errorMessages = {
  [I18nErrorCodes.UNEXPECTED_RETURN_TYPE]: "Unexpected return type in composer",
  [I18nErrorCodes.INVALID_ARGUMENT]: "Invalid argument",
  [I18nErrorCodes.MUST_BE_CALL_SETUP_TOP]: "Must be called at the top of a `setup` function",
  [I18nErrorCodes.NOT_INSTALLED]: "Need to install with `app.use` function",
  [I18nErrorCodes.UNEXPECTED_ERROR]: "Unexpected error",
  [I18nErrorCodes.REQUIRED_VALUE]: `Required in value: {0}`,
  [I18nErrorCodes.INVALID_VALUE]: `Invalid value`,
  [I18nErrorCodes.CANNOT_SETUP_VUE_DEVTOOLS_PLUGIN]: `Cannot setup vue-devtools plugin`,
  [I18nErrorCodes.NOT_INSTALLED_WITH_PROVIDE]: "Need to install with `provide` function",
  [I18nErrorCodes.NOT_COMPATIBLE_LEGACY_VUE_I18N]: "Not compatible legacy VueI18n.",
  [I18nErrorCodes.NOT_AVAILABLE_COMPOSITION_IN_LEGACY]: "Not available Compostion API in Legacy API mode. Please make sure that the legacy API mode is working properly"
};
const TranslateVNodeSymbol = /* @__PURE__ */ makeSymbol("__translateVNode");
const DatetimePartsSymbol = /* @__PURE__ */ makeSymbol("__datetimeParts");
const NumberPartsSymbol = /* @__PURE__ */ makeSymbol("__numberParts");
const EnableEmitter = /* @__PURE__ */ makeSymbol("__enableEmitter");
const DisableEmitter = /* @__PURE__ */ makeSymbol("__disableEmitter");
const SetPluralRulesSymbol = makeSymbol("__setPluralRules");
const InejctWithOptionSymbol = /* @__PURE__ */ makeSymbol("__injectWithOption");
const DisposeSymbol = /* @__PURE__ */ makeSymbol("__dispose");
function handleFlatJson(obj) {
  if (!isObject$1(obj)) {
    return obj;
  }
  if (isMessageAST(obj)) {
    return obj;
  }
  for (const key in obj) {
    if (!hasOwn$1(obj, key)) {
      continue;
    }
    if (!key.includes(".")) {
      if (isObject$1(obj[key])) {
        handleFlatJson(obj[key]);
      }
    } else {
      const subKeys = key.split(".");
      const lastIndex = subKeys.length - 1;
      let currentObj = obj;
      let hasStringValue = false;
      for (let i = 0; i < lastIndex; i++) {
        if (subKeys[i] === "__proto__") {
          throw new Error(`unsafe key: ${subKeys[i]}`);
        }
        if (!(subKeys[i] in currentObj)) {
          currentObj[subKeys[i]] = create$1();
        }
        if (!isObject$1(currentObj[subKeys[i]])) {
          warn$1(getWarnMessage(I18nWarnCodes.IGNORE_OBJ_FLATTEN, {
            key: subKeys[i]
          }));
          hasStringValue = true;
          break;
        }
        currentObj = currentObj[subKeys[i]];
      }
      if (!hasStringValue) {
        if (!isMessageAST(currentObj)) {
          currentObj[subKeys[lastIndex]] = obj[key];
          delete obj[key];
        } else {
          if (!AST_NODE_PROPS_KEYS.includes(subKeys[lastIndex])) {
            delete obj[key];
          }
        }
      }
      if (!isMessageAST(currentObj)) {
        const target = currentObj[subKeys[lastIndex]];
        if (isObject$1(target)) {
          handleFlatJson(target);
        }
      }
    }
  }
  return obj;
}
function getLocaleMessages(locale, options) {
  const { messages, __i18n, messageResolver, flatJson } = options;
  const ret = isPlainObject$9(messages) ? messages : isArray$1(__i18n) ? create$1() : { [locale]: create$1() };
  if (isArray$1(__i18n)) {
    __i18n.forEach((custom) => {
      if ("locale" in custom && "resource" in custom) {
        const { locale: locale2, resource } = custom;
        if (locale2) {
          ret[locale2] = ret[locale2] || create$1();
          deepCopy(resource, ret[locale2]);
        } else {
          deepCopy(resource, ret);
        }
      } else {
        isString$1(custom) && deepCopy(JSON.parse(custom), ret);
      }
    });
  }
  if (messageResolver == null && flatJson) {
    for (const key in ret) {
      if (hasOwn$1(ret, key)) {
        handleFlatJson(ret[key]);
      }
    }
  }
  return ret;
}
function getComponentOptions(instance) {
  return instance.type;
}
function adjustI18nResources(gl, options, componentOptions) {
  let messages = isObject$1(options.messages) ? options.messages : create$1();
  if ("__i18nGlobal" in componentOptions) {
    messages = getLocaleMessages(gl.locale.value, {
      messages,
      __i18n: componentOptions.__i18nGlobal
    });
  }
  const locales = Object.keys(messages);
  if (locales.length) {
    locales.forEach((locale) => {
      gl.mergeLocaleMessage(locale, messages[locale]);
    });
  }
  {
    if (isObject$1(options.datetimeFormats)) {
      const locales2 = Object.keys(options.datetimeFormats);
      if (locales2.length) {
        locales2.forEach((locale) => {
          gl.mergeDateTimeFormat(locale, options.datetimeFormats[locale]);
        });
      }
    }
    if (isObject$1(options.numberFormats)) {
      const locales2 = Object.keys(options.numberFormats);
      if (locales2.length) {
        locales2.forEach((locale) => {
          gl.mergeNumberFormat(locale, options.numberFormats[locale]);
        });
      }
    }
  }
}
function createTextNode(key) {
  return createVNode(Text, null, key, 0);
}
const DEVTOOLS_META = "__INTLIFY_META__";
const NOOP_RETURN_ARRAY = () => [];
const NOOP_RETURN_FALSE = () => false;
let composerID = 0;
function defineCoreMissingHandler(missing) {
  return ((ctx, locale, key, type) => {
    return missing(locale, key, getCurrentInstance() || void 0, type);
  });
}
const getMetaInfo = /* @__NO_SIDE_EFFECTS__ */ () => {
  const instance = getCurrentInstance();
  let meta = null;
  return instance && (meta = getComponentOptions(instance)[DEVTOOLS_META]) ? { [DEVTOOLS_META]: meta } : null;
};
function createComposer(options = {}) {
  const { __root, __injectWithOption } = options;
  const _isGlobal = __root === void 0;
  const flatJson = options.flatJson;
  const _ref = inBrowser$1 ? ref : shallowRef;
  let _inheritLocale = isBoolean$1(options.inheritLocale) ? options.inheritLocale : true;
  const _locale = _ref(
    // prettier-ignore
    __root && _inheritLocale ? __root.locale.value : isString$1(options.locale) ? options.locale : DEFAULT_LOCALE
  );
  const _fallbackLocale = _ref(
    // prettier-ignore
    __root && _inheritLocale ? __root.fallbackLocale.value : isString$1(options.fallbackLocale) || isArray$1(options.fallbackLocale) || isPlainObject$9(options.fallbackLocale) || options.fallbackLocale === false ? options.fallbackLocale : _locale.value
  );
  const _messages = _ref(getLocaleMessages(_locale.value, options));
  const _datetimeFormats = _ref(isPlainObject$9(options.datetimeFormats) ? options.datetimeFormats : { [_locale.value]: {} });
  const _numberFormats = _ref(isPlainObject$9(options.numberFormats) ? options.numberFormats : { [_locale.value]: {} });
  let _missingWarn = __root ? __root.missingWarn : isBoolean$1(options.missingWarn) || isRegExp$1(options.missingWarn) ? options.missingWarn : true;
  let _fallbackWarn = __root ? __root.fallbackWarn : isBoolean$1(options.fallbackWarn) || isRegExp$1(options.fallbackWarn) ? options.fallbackWarn : true;
  let _fallbackRoot = __root ? __root.fallbackRoot : isBoolean$1(options.fallbackRoot) ? options.fallbackRoot : true;
  let _fallbackFormat = !!options.fallbackFormat;
  let _missing = isFunction$1(options.missing) ? options.missing : null;
  let _runtimeMissing = isFunction$1(options.missing) ? defineCoreMissingHandler(options.missing) : null;
  let _postTranslation = isFunction$1(options.postTranslation) ? options.postTranslation : null;
  let _warnHtmlMessage = __root ? __root.warnHtmlMessage : isBoolean$1(options.warnHtmlMessage) ? options.warnHtmlMessage : true;
  let _escapeParameter = !!options.escapeParameter;
  const _modifiers = __root ? __root.modifiers : isPlainObject$9(options.modifiers) ? options.modifiers : {};
  let _pluralRules = options.pluralRules || __root && __root.pluralRules;
  let _context;
  const getCoreContext = () => {
    _isGlobal && setFallbackContext(null);
    const ctxOptions = {
      version: VERSION,
      locale: _locale.value,
      fallbackLocale: _fallbackLocale.value,
      messages: _messages.value,
      modifiers: _modifiers,
      pluralRules: _pluralRules,
      missing: _runtimeMissing === null ? void 0 : _runtimeMissing,
      missingWarn: _missingWarn,
      fallbackWarn: _fallbackWarn,
      fallbackFormat: _fallbackFormat,
      unresolving: true,
      postTranslation: _postTranslation === null ? void 0 : _postTranslation,
      warnHtmlMessage: _warnHtmlMessage,
      escapeParameter: _escapeParameter,
      messageResolver: options.messageResolver,
      messageCompiler: options.messageCompiler,
      __meta: { framework: "vue" }
    };
    {
      ctxOptions.datetimeFormats = _datetimeFormats.value;
      ctxOptions.numberFormats = _numberFormats.value;
      ctxOptions.__datetimeFormatters = isPlainObject$9(_context) ? _context.__datetimeFormatters : void 0;
      ctxOptions.__numberFormatters = isPlainObject$9(_context) ? _context.__numberFormatters : void 0;
    }
    {
      ctxOptions.__v_emitter = isPlainObject$9(_context) ? _context.__v_emitter : void 0;
    }
    const ctx = createCoreContext(ctxOptions);
    _isGlobal && setFallbackContext(ctx);
    return ctx;
  };
  _context = getCoreContext();
  updateFallbackLocale(_context, _locale.value, _fallbackLocale.value);
  function trackReactivityValues() {
    return [
      _locale.value,
      _fallbackLocale.value,
      _messages.value,
      _datetimeFormats.value,
      _numberFormats.value
    ];
  }
  const locale = computed({
    get: () => _locale.value,
    set: (val) => {
      _locale.value = val;
      _context.locale = _locale.value;
    }
  });
  const fallbackLocale = computed({
    get: () => _fallbackLocale.value,
    set: (val) => {
      _fallbackLocale.value = val;
      _context.fallbackLocale = _fallbackLocale.value;
      updateFallbackLocale(_context, _locale.value, val);
    }
  });
  const messages = computed(() => _messages.value);
  const datetimeFormats = /* @__PURE__ */ computed(() => _datetimeFormats.value);
  const numberFormats = /* @__PURE__ */ computed(() => _numberFormats.value);
  function getPostTranslationHandler() {
    return isFunction$1(_postTranslation) ? _postTranslation : null;
  }
  function setPostTranslationHandler(handler) {
    _postTranslation = handler;
    _context.postTranslation = handler;
  }
  function getMissingHandler() {
    return _missing;
  }
  function setMissingHandler(handler) {
    if (handler !== null) {
      _runtimeMissing = defineCoreMissingHandler(handler);
    }
    _missing = handler;
    _context.missing = _runtimeMissing;
  }
  function isResolvedTranslateMessage(type, arg) {
    return type !== "translate" || !arg.resolvedMessage;
  }
  const wrapWithDeps = (fn, argumentParser, warnType, fallbackSuccess, fallbackFail, successCondition) => {
    trackReactivityValues();
    let ret;
    try {
      if (true) {
        /* @__PURE__ */ setAdditionalMeta(/* @__PURE__ */ getMetaInfo());
      }
      if (!_isGlobal) {
        _context.fallbackContext = __root ? getFallbackContext() : void 0;
      }
      ret = fn(_context);
    } finally {
      if (!_isGlobal) {
        _context.fallbackContext = void 0;
      }
    }
    if (warnType !== "translate exists" && // for not `te` (e.g `t`)
    isNumber$1(ret) && ret === NOT_REOSLVED || warnType === "translate exists" && !ret) {
      const [key, arg2] = argumentParser();
      if (__root && isString$1(key) && isResolvedTranslateMessage(warnType, arg2)) {
        if (_fallbackRoot && (isTranslateFallbackWarn(_fallbackWarn, key) || isTranslateMissingWarn(_missingWarn, key))) {
          warn$1(getWarnMessage(I18nWarnCodes.FALLBACK_TO_ROOT, {
            key,
            type: warnType
          }));
        }
        {
          const { __v_emitter: emitter } = _context;
          if (emitter && _fallbackRoot) {
            emitter.emit("fallback", {
              type: warnType,
              key,
              to: "global",
              groupId: `${warnType}:${key}`
            });
          }
        }
      }
      return __root && _fallbackRoot ? fallbackSuccess(__root) : fallbackFail(key);
    } else if (successCondition(ret)) {
      return ret;
    } else {
      throw createI18nError(I18nErrorCodes.UNEXPECTED_RETURN_TYPE);
    }
  };
  function t(...args) {
    return wrapWithDeps((context) => Reflect.apply(translate, null, [context, ...args]), () => parseTranslateArgs(...args), "translate", (root) => Reflect.apply(root.t, root, [...args]), (key) => key, (val) => isString$1(val));
  }
  function rt(...args) {
    const [arg1, arg2, arg3] = args;
    if (arg3 && !isObject$1(arg3)) {
      throw createI18nError(I18nErrorCodes.INVALID_ARGUMENT);
    }
    return t(...[arg1, arg2, assign$1({ resolvedMessage: true }, arg3 || {})]);
  }
  function d(...args) {
    return wrapWithDeps((context) => Reflect.apply(datetime, null, [context, ...args]), () => parseDateTimeArgs(...args), "datetime format", (root) => Reflect.apply(root.d, root, [...args]), () => MISSING_RESOLVE_VALUE, (val) => isString$1(val));
  }
  function n(...args) {
    return wrapWithDeps((context) => Reflect.apply(number, null, [context, ...args]), () => parseNumberArgs(...args), "number format", (root) => Reflect.apply(root.n, root, [...args]), () => MISSING_RESOLVE_VALUE, (val) => isString$1(val));
  }
  function normalize(values) {
    return values.map((val) => isString$1(val) || isNumber$1(val) || isBoolean$1(val) ? createTextNode(String(val)) : val);
  }
  const interpolate = (val) => val;
  const processor = {
    normalize,
    interpolate,
    type: "vnode"
  };
  function translateVNode(...args) {
    return wrapWithDeps((context) => {
      let ret;
      const _context2 = context;
      try {
        _context2.processor = processor;
        ret = Reflect.apply(translate, null, [_context2, ...args]);
      } finally {
        _context2.processor = null;
      }
      return ret;
    }, () => parseTranslateArgs(...args), "translate", (root) => root[TranslateVNodeSymbol](...args), (key) => [createTextNode(key)], (val) => isArray$1(val));
  }
  function numberParts(...args) {
    return wrapWithDeps((context) => Reflect.apply(number, null, [context, ...args]), () => parseNumberArgs(...args), "number format", (root) => root[NumberPartsSymbol](...args), NOOP_RETURN_ARRAY, (val) => isString$1(val) || isArray$1(val));
  }
  function datetimeParts(...args) {
    return wrapWithDeps((context) => Reflect.apply(datetime, null, [context, ...args]), () => parseDateTimeArgs(...args), "datetime format", (root) => root[DatetimePartsSymbol](...args), NOOP_RETURN_ARRAY, (val) => isString$1(val) || isArray$1(val));
  }
  function setPluralRules(rules) {
    _pluralRules = rules;
    _context.pluralRules = _pluralRules;
  }
  function te(key, locale2) {
    return wrapWithDeps(() => {
      if (!key) {
        return false;
      }
      const targetLocale = isString$1(locale2) ? locale2 : _locale.value;
      const message = getLocaleMessage(targetLocale);
      const resolved = _context.messageResolver(message, key);
      return isMessageAST(resolved) || isMessageFunction(resolved) || isString$1(resolved);
    }, () => [key], "translate exists", (root) => {
      return Reflect.apply(root.te, root, [key, locale2]);
    }, NOOP_RETURN_FALSE, (val) => isBoolean$1(val));
  }
  function resolveMessages(key) {
    let messages2 = null;
    const locales = fallbackWithLocaleChain(_context, _fallbackLocale.value, _locale.value);
    for (let i = 0; i < locales.length; i++) {
      const targetLocaleMessages = _messages.value[locales[i]] || {};
      const messageValue = _context.messageResolver(targetLocaleMessages, key);
      if (messageValue != null) {
        messages2 = messageValue;
        break;
      }
    }
    return messages2;
  }
  function tm(key) {
    const messages2 = resolveMessages(key);
    return messages2 != null ? messages2 : __root ? __root.tm(key) || {} : {};
  }
  function getLocaleMessage(locale2) {
    return _messages.value[locale2] || {};
  }
  function setLocaleMessage(locale2, message) {
    if (flatJson) {
      const _message = { [locale2]: message };
      for (const key in _message) {
        if (hasOwn$1(_message, key)) {
          handleFlatJson(_message[key]);
        }
      }
      message = _message[locale2];
    }
    _messages.value[locale2] = message;
    _context.messages = _messages.value;
  }
  function mergeLocaleMessage(locale2, message) {
    _messages.value[locale2] = _messages.value[locale2] || {};
    const _message = { [locale2]: message };
    if (flatJson) {
      for (const key in _message) {
        if (hasOwn$1(_message, key)) {
          handleFlatJson(_message[key]);
        }
      }
    }
    message = _message[locale2];
    deepCopy(message, _messages.value[locale2]);
    _context.messages = _messages.value;
  }
  function getDateTimeFormat(locale2) {
    return _datetimeFormats.value[locale2] || {};
  }
  function setDateTimeFormat(locale2, format2) {
    _datetimeFormats.value[locale2] = format2;
    _context.datetimeFormats = _datetimeFormats.value;
    clearDateTimeFormat(_context, locale2, format2);
  }
  function mergeDateTimeFormat(locale2, format2) {
    _datetimeFormats.value[locale2] = assign$1(_datetimeFormats.value[locale2] || {}, format2);
    _context.datetimeFormats = _datetimeFormats.value;
    clearDateTimeFormat(_context, locale2, format2);
  }
  function getNumberFormat(locale2) {
    return _numberFormats.value[locale2] || {};
  }
  function setNumberFormat(locale2, format2) {
    _numberFormats.value[locale2] = format2;
    _context.numberFormats = _numberFormats.value;
    clearNumberFormat(_context, locale2, format2);
  }
  function mergeNumberFormat(locale2, format2) {
    _numberFormats.value[locale2] = assign$1(_numberFormats.value[locale2] || {}, format2);
    _context.numberFormats = _numberFormats.value;
    clearNumberFormat(_context, locale2, format2);
  }
  composerID++;
  if (__root && inBrowser$1) {
    watch(__root.locale, (val) => {
      if (_inheritLocale) {
        _locale.value = val;
        _context.locale = val;
        updateFallbackLocale(_context, _locale.value, _fallbackLocale.value);
      }
    });
    watch(__root.fallbackLocale, (val) => {
      if (_inheritLocale) {
        _fallbackLocale.value = val;
        _context.fallbackLocale = val;
        updateFallbackLocale(_context, _locale.value, _fallbackLocale.value);
      }
    });
  }
  const composer = {
    id: composerID,
    locale,
    fallbackLocale,
    get inheritLocale() {
      return _inheritLocale;
    },
    set inheritLocale(val) {
      _inheritLocale = val;
      if (val && __root) {
        _locale.value = __root.locale.value;
        _fallbackLocale.value = __root.fallbackLocale.value;
        updateFallbackLocale(_context, _locale.value, _fallbackLocale.value);
      }
    },
    get availableLocales() {
      return Object.keys(_messages.value).sort();
    },
    messages,
    get modifiers() {
      return _modifiers;
    },
    get pluralRules() {
      return _pluralRules || {};
    },
    get isGlobal() {
      return _isGlobal;
    },
    get missingWarn() {
      return _missingWarn;
    },
    set missingWarn(val) {
      _missingWarn = val;
      _context.missingWarn = _missingWarn;
    },
    get fallbackWarn() {
      return _fallbackWarn;
    },
    set fallbackWarn(val) {
      _fallbackWarn = val;
      _context.fallbackWarn = _fallbackWarn;
    },
    get fallbackRoot() {
      return _fallbackRoot;
    },
    set fallbackRoot(val) {
      _fallbackRoot = val;
    },
    get fallbackFormat() {
      return _fallbackFormat;
    },
    set fallbackFormat(val) {
      _fallbackFormat = val;
      _context.fallbackFormat = _fallbackFormat;
    },
    get warnHtmlMessage() {
      return _warnHtmlMessage;
    },
    set warnHtmlMessage(val) {
      _warnHtmlMessage = val;
      _context.warnHtmlMessage = val;
    },
    get escapeParameter() {
      return _escapeParameter;
    },
    set escapeParameter(val) {
      _escapeParameter = val;
      _context.escapeParameter = val;
    },
    t,
    getLocaleMessage,
    setLocaleMessage,
    mergeLocaleMessage,
    getPostTranslationHandler,
    setPostTranslationHandler,
    getMissingHandler,
    setMissingHandler,
    [SetPluralRulesSymbol]: setPluralRules
  };
  {
    composer.datetimeFormats = datetimeFormats;
    composer.numberFormats = numberFormats;
    composer.rt = rt;
    composer.te = te;
    composer.tm = tm;
    composer.d = d;
    composer.n = n;
    composer.getDateTimeFormat = getDateTimeFormat;
    composer.setDateTimeFormat = setDateTimeFormat;
    composer.mergeDateTimeFormat = mergeDateTimeFormat;
    composer.getNumberFormat = getNumberFormat;
    composer.setNumberFormat = setNumberFormat;
    composer.mergeNumberFormat = mergeNumberFormat;
    composer[InejctWithOptionSymbol] = __injectWithOption;
    composer[TranslateVNodeSymbol] = translateVNode;
    composer[DatetimePartsSymbol] = datetimeParts;
    composer[NumberPartsSymbol] = numberParts;
  }
  {
    composer[EnableEmitter] = (emitter) => {
      _context.__v_emitter = emitter;
    };
    composer[DisableEmitter] = () => {
      _context.__v_emitter = void 0;
    };
  }
  return composer;
}
const VUE_I18N_COMPONENT_TYPES = "vue-i18n: composer properties";
const VueDevToolsLabels = {
  "vue-devtools-plugin-vue-i18n": "Vue I18n DevTools",
  "vue-i18n-resource-inspector": "Vue I18n DevTools",
  "vue-i18n-timeline": "Vue I18n"
};
const VueDevToolsPlaceholders = {
  "vue-i18n-resource-inspector": "Search for scopes ..."
};
const VueDevToolsTimelineColors = {
  "vue-i18n-timeline": 16764185
};
let devtoolsApi;
async function enableDevTools(app, i18n2) {
  return new Promise((resolve, reject) => {
    try {
      setupDevtoolsPlugin({
        id: "vue-devtools-plugin-vue-i18n",
        label: VueDevToolsLabels["vue-devtools-plugin-vue-i18n"],
        packageName: "vue-i18n",
        homepage: "https://vue-i18n.intlify.dev",
        logo: "https://vue-i18n.intlify.dev/vue-i18n-devtools-logo.png",
        componentStateTypes: [VUE_I18N_COMPONENT_TYPES],
        app
        // eslint-disable-line @typescript-eslint/no-explicit-any
      }, (api) => {
        devtoolsApi = api;
        api.on.visitComponentTree(({ componentInstance, treeNode }) => {
          updateComponentTreeTags(componentInstance, treeNode, i18n2);
        });
        api.on.inspectComponent(({ componentInstance, instanceData }) => {
          if (componentInstance.vnode.el && componentInstance.vnode.el.__VUE_I18N__ && instanceData) {
            if (i18n2.mode === "legacy") {
              if (componentInstance.vnode.el.__VUE_I18N__ !== i18n2.global.__composer) {
                inspectComposer(instanceData, componentInstance.vnode.el.__VUE_I18N__);
              }
            } else {
              inspectComposer(instanceData, componentInstance.vnode.el.__VUE_I18N__);
            }
          }
        });
        api.addInspector({
          id: "vue-i18n-resource-inspector",
          label: VueDevToolsLabels["vue-i18n-resource-inspector"],
          icon: "language",
          treeFilterPlaceholder: VueDevToolsPlaceholders["vue-i18n-resource-inspector"]
        });
        api.on.getInspectorTree((payload) => {
          if (payload.app === app && payload.inspectorId === "vue-i18n-resource-inspector") {
            registerScope(payload, i18n2);
          }
        });
        const roots = /* @__PURE__ */ new Map();
        api.on.getInspectorState(async (payload) => {
          if (payload.app === app && payload.inspectorId === "vue-i18n-resource-inspector") {
            api.unhighlightElement();
            inspectScope(payload, i18n2);
            if (payload.nodeId === "global") {
              if (!roots.has(payload.app)) {
                const [root] = await api.getComponentInstances(payload.app);
                roots.set(payload.app, root);
              }
              api.highlightElement(roots.get(payload.app));
            } else {
              const instance = getComponentInstance(payload.nodeId, i18n2);
              instance && api.highlightElement(instance);
            }
          }
        });
        api.on.editInspectorState((payload) => {
          if (payload.app === app && payload.inspectorId === "vue-i18n-resource-inspector") {
            editScope(payload, i18n2);
          }
        });
        api.addTimelineLayer({
          id: "vue-i18n-timeline",
          label: VueDevToolsLabels["vue-i18n-timeline"],
          color: VueDevToolsTimelineColors["vue-i18n-timeline"]
        });
        resolve(true);
      });
    } catch (e) {
      console.error(e);
      reject(false);
    }
  });
}
function getI18nScopeLable(instance) {
  return instance.type.name || instance.type.displayName || instance.type.__file || "Anonymous";
}
function updateComponentTreeTags(instance, treeNode, i18n2) {
  const global2 = i18n2.mode === "composition" ? i18n2.global : i18n2.global.__composer;
  if (instance && instance.vnode.el && instance.vnode.el.__VUE_I18N__) {
    if (instance.vnode.el.__VUE_I18N__ !== global2) {
      const tag = {
        label: `i18n (${getI18nScopeLable(instance)} Scope)`,
        textColor: 0,
        backgroundColor: 16764185
      };
      treeNode.tags.push(tag);
    }
  }
}
function inspectComposer(instanceData, composer) {
  const type = VUE_I18N_COMPONENT_TYPES;
  instanceData.state.push({
    type,
    key: "locale",
    editable: true,
    value: composer.locale.value
  });
  instanceData.state.push({
    type,
    key: "availableLocales",
    editable: false,
    value: composer.availableLocales
  });
  instanceData.state.push({
    type,
    key: "fallbackLocale",
    editable: true,
    value: composer.fallbackLocale.value
  });
  instanceData.state.push({
    type,
    key: "inheritLocale",
    editable: true,
    value: composer.inheritLocale
  });
  instanceData.state.push({
    type,
    key: "messages",
    editable: false,
    value: getLocaleMessageValue(composer.messages.value)
  });
  {
    instanceData.state.push({
      type,
      key: "datetimeFormats",
      editable: false,
      value: composer.datetimeFormats.value
    });
    instanceData.state.push({
      type,
      key: "numberFormats",
      editable: false,
      value: composer.numberFormats.value
    });
  }
}
function getLocaleMessageValue(messages) {
  const value = {};
  Object.keys(messages).forEach((key) => {
    const v = messages[key];
    if (isFunction$1(v) && "source" in v) {
      value[key] = getMessageFunctionDetails(v);
    } else if (isMessageAST(v) && v.loc && v.loc.source) {
      value[key] = v.loc.source;
    } else if (isObject$1(v)) {
      value[key] = getLocaleMessageValue(v);
    } else {
      value[key] = v;
    }
  });
  return value;
}
const ESC = {
  "<": "&lt;",
  ">": "&gt;",
  '"': "&quot;",
  "&": "&amp;"
};
function escape(s) {
  return s.replace(/[<>"&]/g, escapeChar);
}
function escapeChar(a) {
  return ESC[a] || a;
}
function getMessageFunctionDetails(func) {
  const argString = func.source ? `("${escape(func.source)}")` : `(?)`;
  return {
    _custom: {
      type: "function",
      display: `<span>ƒ</span> ${argString}`
    }
  };
}
function registerScope(payload, i18n2) {
  payload.rootNodes.push({
    id: "global",
    label: "Global Scope"
  });
  const global2 = i18n2.mode === "composition" ? i18n2.global : i18n2.global.__composer;
  for (const [keyInstance, instance] of i18n2.__instances) {
    const composer = i18n2.mode === "composition" ? instance : instance.__composer;
    if (global2 === composer) {
      continue;
    }
    payload.rootNodes.push({
      id: composer.id.toString(),
      label: `${getI18nScopeLable(keyInstance)} Scope`
    });
  }
}
function getComponentInstance(nodeId, i18n2) {
  let instance = null;
  if (nodeId !== "global") {
    for (const [component, composer] of i18n2.__instances.entries()) {
      if (composer.id.toString() === nodeId) {
        instance = component;
        break;
      }
    }
  }
  return instance;
}
function getComposer$2(nodeId, i18n2) {
  if (nodeId === "global") {
    return i18n2.mode === "composition" ? i18n2.global : i18n2.global.__composer;
  } else {
    const instance = Array.from(i18n2.__instances.values()).find((item) => item.id.toString() === nodeId);
    if (instance) {
      return i18n2.mode === "composition" ? instance : instance.__composer;
    } else {
      return null;
    }
  }
}
function inspectScope(payload, i18n2) {
  const composer = getComposer$2(payload.nodeId, i18n2);
  if (composer) {
    payload.state = makeScopeInspectState(composer);
  }
  return null;
}
function makeScopeInspectState(composer) {
  const state = {};
  const localeType = "Locale related info";
  const localeStates = [
    {
      type: localeType,
      key: "locale",
      editable: true,
      value: composer.locale.value
    },
    {
      type: localeType,
      key: "fallbackLocale",
      editable: true,
      value: composer.fallbackLocale.value
    },
    {
      type: localeType,
      key: "availableLocales",
      editable: false,
      value: composer.availableLocales
    },
    {
      type: localeType,
      key: "inheritLocale",
      editable: true,
      value: composer.inheritLocale
    }
  ];
  state[localeType] = localeStates;
  const localeMessagesType = "Locale messages info";
  const localeMessagesStates = [
    {
      type: localeMessagesType,
      key: "messages",
      editable: false,
      value: getLocaleMessageValue(composer.messages.value)
    }
  ];
  state[localeMessagesType] = localeMessagesStates;
  {
    const datetimeFormatsType = "Datetime formats info";
    const datetimeFormatsStates = [
      {
        type: datetimeFormatsType,
        key: "datetimeFormats",
        editable: false,
        value: composer.datetimeFormats.value
      }
    ];
    state[datetimeFormatsType] = datetimeFormatsStates;
    const numberFormatsType = "Datetime formats info";
    const numberFormatsStates = [
      {
        type: numberFormatsType,
        key: "numberFormats",
        editable: false,
        value: composer.numberFormats.value
      }
    ];
    state[numberFormatsType] = numberFormatsStates;
  }
  return state;
}
function addTimelineEvent(event, payload) {
  if (devtoolsApi) {
    let groupId;
    if (payload && "groupId" in payload) {
      groupId = payload.groupId;
      delete payload.groupId;
    }
    devtoolsApi.addTimelineEvent({
      layerId: "vue-i18n-timeline",
      event: {
        title: event,
        groupId,
        time: Date.now(),
        meta: {},
        data: payload || {},
        logType: event === "compile-error" ? "error" : event === "fallback" || event === "missing" ? "warning" : "default"
      }
    });
  }
}
function editScope(payload, i18n2) {
  const composer = getComposer$2(payload.nodeId, i18n2);
  if (composer) {
    const [field] = payload.path;
    if (field === "locale" && isString$1(payload.state.value)) {
      composer.locale.value = payload.state.value;
    } else if (field === "fallbackLocale" && (isString$1(payload.state.value) || isArray$1(payload.state.value) || isObject$1(payload.state.value))) {
      composer.fallbackLocale.value = payload.state.value;
    } else if (field === "inheritLocale" && isBoolean$1(payload.state.value)) {
      composer.inheritLocale = payload.state.value;
    }
  }
}
function convertComposerOptions(options) {
  const locale = isString$1(options.locale) ? options.locale : DEFAULT_LOCALE;
  const fallbackLocale = isString$1(options.fallbackLocale) || isArray$1(options.fallbackLocale) || isPlainObject$9(options.fallbackLocale) || options.fallbackLocale === false ? options.fallbackLocale : locale;
  const missing = isFunction$1(options.missing) ? options.missing : void 0;
  const missingWarn = isBoolean$1(options.silentTranslationWarn) || isRegExp$1(options.silentTranslationWarn) ? !options.silentTranslationWarn : true;
  const fallbackWarn = isBoolean$1(options.silentFallbackWarn) || isRegExp$1(options.silentFallbackWarn) ? !options.silentFallbackWarn : true;
  const fallbackRoot = isBoolean$1(options.fallbackRoot) ? options.fallbackRoot : true;
  const fallbackFormat = !!options.formatFallbackMessages;
  const modifiers = isPlainObject$9(options.modifiers) ? options.modifiers : {};
  const pluralizationRules = options.pluralizationRules;
  const postTranslation = isFunction$1(options.postTranslation) ? options.postTranslation : void 0;
  const warnHtmlMessage = isString$1(options.warnHtmlInMessage) ? options.warnHtmlInMessage !== "off" : true;
  const escapeParameter = !!options.escapeParameterHtml;
  const inheritLocale = isBoolean$1(options.sync) ? options.sync : true;
  let messages = options.messages;
  if (isPlainObject$9(options.sharedMessages)) {
    const sharedMessages = options.sharedMessages;
    const locales = Object.keys(sharedMessages);
    messages = locales.reduce((messages2, locale2) => {
      const message = messages2[locale2] || (messages2[locale2] = {});
      assign$1(message, sharedMessages[locale2]);
      return messages2;
    }, messages || {});
  }
  const { __i18n, __root, __injectWithOption } = options;
  const datetimeFormats = options.datetimeFormats;
  const numberFormats = options.numberFormats;
  const flatJson = options.flatJson;
  return {
    locale,
    fallbackLocale,
    messages,
    flatJson,
    datetimeFormats,
    numberFormats,
    missing,
    missingWarn,
    fallbackWarn,
    fallbackRoot,
    fallbackFormat,
    modifiers,
    pluralRules: pluralizationRules,
    postTranslation,
    warnHtmlMessage,
    escapeParameter,
    messageResolver: options.messageResolver,
    inheritLocale,
    __i18n,
    __root,
    __injectWithOption
  };
}
function createVueI18n(options = {}) {
  const composer = createComposer(convertComposerOptions(options));
  const { __extender } = options;
  const vueI18n = {
    // id
    id: composer.id,
    // locale
    get locale() {
      return composer.locale.value;
    },
    set locale(val) {
      composer.locale.value = val;
    },
    // fallbackLocale
    get fallbackLocale() {
      return composer.fallbackLocale.value;
    },
    set fallbackLocale(val) {
      composer.fallbackLocale.value = val;
    },
    // messages
    get messages() {
      return composer.messages.value;
    },
    // datetimeFormats
    get datetimeFormats() {
      return composer.datetimeFormats.value;
    },
    // numberFormats
    get numberFormats() {
      return composer.numberFormats.value;
    },
    // availableLocales
    get availableLocales() {
      return composer.availableLocales;
    },
    // missing
    get missing() {
      return composer.getMissingHandler();
    },
    set missing(handler) {
      composer.setMissingHandler(handler);
    },
    // silentTranslationWarn
    get silentTranslationWarn() {
      return isBoolean$1(composer.missingWarn) ? !composer.missingWarn : composer.missingWarn;
    },
    set silentTranslationWarn(val) {
      composer.missingWarn = isBoolean$1(val) ? !val : val;
    },
    // silentFallbackWarn
    get silentFallbackWarn() {
      return isBoolean$1(composer.fallbackWarn) ? !composer.fallbackWarn : composer.fallbackWarn;
    },
    set silentFallbackWarn(val) {
      composer.fallbackWarn = isBoolean$1(val) ? !val : val;
    },
    // modifiers
    get modifiers() {
      return composer.modifiers;
    },
    // formatFallbackMessages
    get formatFallbackMessages() {
      return composer.fallbackFormat;
    },
    set formatFallbackMessages(val) {
      composer.fallbackFormat = val;
    },
    // postTranslation
    get postTranslation() {
      return composer.getPostTranslationHandler();
    },
    set postTranslation(handler) {
      composer.setPostTranslationHandler(handler);
    },
    // sync
    get sync() {
      return composer.inheritLocale;
    },
    set sync(val) {
      composer.inheritLocale = val;
    },
    // warnInHtmlMessage
    get warnHtmlInMessage() {
      return composer.warnHtmlMessage ? "warn" : "off";
    },
    set warnHtmlInMessage(val) {
      composer.warnHtmlMessage = val !== "off";
    },
    // escapeParameterHtml
    get escapeParameterHtml() {
      return composer.escapeParameter;
    },
    set escapeParameterHtml(val) {
      composer.escapeParameter = val;
    },
    // pluralizationRules
    get pluralizationRules() {
      return composer.pluralRules || {};
    },
    // for internal
    __composer: composer,
    // t
    t(...args) {
      return Reflect.apply(composer.t, composer, [...args]);
    },
    // rt
    rt(...args) {
      return Reflect.apply(composer.rt, composer, [...args]);
    },
    // tc
    tc(...args) {
      const [arg1, arg2, arg3] = args;
      const options2 = { plural: 1 };
      let list = null;
      let named = null;
      {
        warnOnce$1(getWarnMessage(I18nWarnCodes.DEPRECATE_TC));
      }
      if (!isString$1(arg1)) {
        throw createI18nError(I18nErrorCodes.INVALID_ARGUMENT);
      }
      const key = arg1;
      if (isString$1(arg2)) {
        options2.locale = arg2;
      } else if (isNumber$1(arg2)) {
        options2.plural = arg2;
      } else if (isArray$1(arg2)) {
        list = arg2;
      } else if (isPlainObject$9(arg2)) {
        named = arg2;
      }
      if (isString$1(arg3)) {
        options2.locale = arg3;
      } else if (isArray$1(arg3)) {
        list = arg3;
      } else if (isPlainObject$9(arg3)) {
        named = arg3;
      }
      return Reflect.apply(composer.t, composer, [
        key,
        list || named || {},
        options2
      ]);
    },
    // te
    te(key, locale) {
      return composer.te(key, locale);
    },
    // tm
    tm(key) {
      return composer.tm(key);
    },
    // getLocaleMessage
    getLocaleMessage(locale) {
      return composer.getLocaleMessage(locale);
    },
    // setLocaleMessage
    setLocaleMessage(locale, message) {
      composer.setLocaleMessage(locale, message);
    },
    // mergeLocaleMessage
    mergeLocaleMessage(locale, message) {
      composer.mergeLocaleMessage(locale, message);
    },
    // d
    d(...args) {
      return Reflect.apply(composer.d, composer, [...args]);
    },
    // getDateTimeFormat
    getDateTimeFormat(locale) {
      return composer.getDateTimeFormat(locale);
    },
    // setDateTimeFormat
    setDateTimeFormat(locale, format2) {
      composer.setDateTimeFormat(locale, format2);
    },
    // mergeDateTimeFormat
    mergeDateTimeFormat(locale, format2) {
      composer.mergeDateTimeFormat(locale, format2);
    },
    // n
    n(...args) {
      return Reflect.apply(composer.n, composer, [...args]);
    },
    // getNumberFormat
    getNumberFormat(locale) {
      return composer.getNumberFormat(locale);
    },
    // setNumberFormat
    setNumberFormat(locale, format2) {
      composer.setNumberFormat(locale, format2);
    },
    // mergeNumberFormat
    mergeNumberFormat(locale, format2) {
      composer.mergeNumberFormat(locale, format2);
    }
  };
  vueI18n.__extender = __extender;
  {
    vueI18n.__enableEmitter = (emitter) => {
      const __composer = composer;
      __composer[EnableEmitter] && __composer[EnableEmitter](emitter);
    };
    vueI18n.__disableEmitter = () => {
      const __composer = composer;
      __composer[DisableEmitter] && __composer[DisableEmitter]();
    };
  }
  return vueI18n;
}
function defineMixin(vuei18n, composer, i18n2) {
  return {
    beforeCreate() {
      const instance = getCurrentInstance();
      if (!instance) {
        throw createI18nError(I18nErrorCodes.UNEXPECTED_ERROR);
      }
      const options = this.$options;
      if (options.i18n) {
        const optionsI18n = options.i18n;
        if (options.__i18n) {
          optionsI18n.__i18n = options.__i18n;
        }
        optionsI18n.__root = composer;
        if (this === this.$root) {
          this.$i18n = mergeToGlobal(vuei18n, optionsI18n);
        } else {
          optionsI18n.__injectWithOption = true;
          optionsI18n.__extender = i18n2.__vueI18nExtend;
          this.$i18n = createVueI18n(optionsI18n);
          const _vueI18n = this.$i18n;
          if (_vueI18n.__extender) {
            _vueI18n.__disposer = _vueI18n.__extender(this.$i18n);
          }
        }
      } else if (options.__i18n) {
        if (this === this.$root) {
          this.$i18n = mergeToGlobal(vuei18n, options);
        } else {
          this.$i18n = createVueI18n({
            __i18n: options.__i18n,
            __injectWithOption: true,
            __extender: i18n2.__vueI18nExtend,
            __root: composer
          });
          const _vueI18n = this.$i18n;
          if (_vueI18n.__extender) {
            _vueI18n.__disposer = _vueI18n.__extender(this.$i18n);
          }
        }
      } else {
        this.$i18n = vuei18n;
      }
      if (options.__i18nGlobal) {
        adjustI18nResources(composer, options, options);
      }
      this.$t = (...args) => this.$i18n.t(...args);
      this.$rt = (...args) => this.$i18n.rt(...args);
      this.$tc = (...args) => this.$i18n.tc(...args);
      this.$te = (key, locale) => this.$i18n.te(key, locale);
      this.$d = (...args) => this.$i18n.d(...args);
      this.$n = (...args) => this.$i18n.n(...args);
      this.$tm = (key) => this.$i18n.tm(key);
      i18n2.__setInstance(instance, this.$i18n);
    },
    mounted() {
      if (this.$el && this.$i18n) {
        const _vueI18n = this.$i18n;
        this.$el.__VUE_I18N__ = _vueI18n.__composer;
        const emitter = this.__v_emitter = createEmitter();
        _vueI18n.__enableEmitter && _vueI18n.__enableEmitter(emitter);
        emitter.on("*", addTimelineEvent);
      }
    },
    unmounted() {
      const instance = getCurrentInstance();
      if (!instance) {
        throw createI18nError(I18nErrorCodes.UNEXPECTED_ERROR);
      }
      const _vueI18n = this.$i18n;
      if (this.$el && this.$el.__VUE_I18N__) {
        if (this.__v_emitter) {
          this.__v_emitter.off("*", addTimelineEvent);
          delete this.__v_emitter;
        }
        if (this.$i18n) {
          _vueI18n.__disableEmitter && _vueI18n.__disableEmitter();
          delete this.$el.__VUE_I18N__;
        }
      }
      delete this.$t;
      delete this.$rt;
      delete this.$tc;
      delete this.$te;
      delete this.$d;
      delete this.$n;
      delete this.$tm;
      if (_vueI18n.__disposer) {
        _vueI18n.__disposer();
        delete _vueI18n.__disposer;
        delete _vueI18n.__extender;
      }
      i18n2.__deleteInstance(instance);
      delete this.$i18n;
    }
  };
}
function mergeToGlobal(g, options) {
  g.locale = options.locale || g.locale;
  g.fallbackLocale = options.fallbackLocale || g.fallbackLocale;
  g.missing = options.missing || g.missing;
  g.silentTranslationWarn = options.silentTranslationWarn || g.silentFallbackWarn;
  g.silentFallbackWarn = options.silentFallbackWarn || g.silentFallbackWarn;
  g.formatFallbackMessages = options.formatFallbackMessages || g.formatFallbackMessages;
  g.postTranslation = options.postTranslation || g.postTranslation;
  g.warnHtmlInMessage = options.warnHtmlInMessage || g.warnHtmlInMessage;
  g.escapeParameterHtml = options.escapeParameterHtml || g.escapeParameterHtml;
  g.sync = options.sync || g.sync;
  g.__composer[SetPluralRulesSymbol](options.pluralizationRules || g.pluralizationRules);
  const messages = getLocaleMessages(g.locale, {
    messages: options.messages,
    __i18n: options.__i18n
  });
  Object.keys(messages).forEach((locale) => g.mergeLocaleMessage(locale, messages[locale]));
  if (options.datetimeFormats) {
    Object.keys(options.datetimeFormats).forEach((locale) => g.mergeDateTimeFormat(locale, options.datetimeFormats[locale]));
  }
  if (options.numberFormats) {
    Object.keys(options.numberFormats).forEach((locale) => g.mergeNumberFormat(locale, options.numberFormats[locale]));
  }
  return g;
}
const baseFormatProps = {
  tag: {
    type: [String, Object]
  },
  locale: {
    type: String
  },
  scope: {
    type: String,
    // NOTE: avoid https://github.com/microsoft/rushstack/issues/1050
    validator: (val) => val === "parent" || val === "global",
    default: "parent"
    /* ComponentI18nScope */
  },
  i18n: {
    type: Object
  }
};
function getInterpolateArg({ slots }, keys) {
  if (keys.length === 1 && keys[0] === "default") {
    const ret = slots.default ? slots.default() : [];
    return ret.reduce((slot, current) => {
      return [
        ...slot,
        // prettier-ignore
        ...current.type === Fragment ? current.children : [current]
      ];
    }, []);
  } else {
    return keys.reduce((arg, key) => {
      const slot = slots[key];
      if (slot) {
        arg[key] = slot();
      }
      return arg;
    }, create$1());
  }
}
function getFragmentableTag() {
  return Fragment;
}
const TranslationImpl = /* @__PURE__ */ defineComponent({
  /* eslint-disable */
  name: "i18n-t",
  props: assign$1({
    keypath: {
      type: String,
      required: true
    },
    plural: {
      type: [Number, String],
      validator: (val) => isNumber$1(val) || !isNaN(val)
    }
  }, baseFormatProps),
  /* eslint-enable */
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  setup(props, context) {
    const { slots, attrs } = context;
    const i18n2 = props.i18n || useI18n({
      useScope: props.scope,
      __useComponent: true
    });
    return () => {
      const keys = Object.keys(slots).filter((key) => key !== "_");
      const options = create$1();
      if (props.locale) {
        options.locale = props.locale;
      }
      if (props.plural !== void 0) {
        options.plural = isString$1(props.plural) ? +props.plural : props.plural;
      }
      const arg = getInterpolateArg(context, keys);
      const children = i18n2[TranslateVNodeSymbol](props.keypath, arg, options);
      const assignedAttrs = assign$1(create$1(), attrs);
      const tag = isString$1(props.tag) || isObject$1(props.tag) ? props.tag : getFragmentableTag();
      return h(tag, assignedAttrs, children);
    };
  }
});
const Translation = TranslationImpl;
function isVNode(target) {
  return isArray$1(target) && !isString$1(target[0]);
}
function renderFormatter(props, context, slotKeys, partFormatter) {
  const { slots, attrs } = context;
  return () => {
    const options = { part: true };
    let overrides = create$1();
    if (props.locale) {
      options.locale = props.locale;
    }
    if (isString$1(props.format)) {
      options.key = props.format;
    } else if (isObject$1(props.format)) {
      if (isString$1(props.format.key)) {
        options.key = props.format.key;
      }
      overrides = Object.keys(props.format).reduce((options2, prop) => {
        return slotKeys.includes(prop) ? assign$1(create$1(), options2, { [prop]: props.format[prop] }) : options2;
      }, create$1());
    }
    const parts = partFormatter(...[props.value, options, overrides]);
    let children = [options.key];
    if (isArray$1(parts)) {
      children = parts.map((part, index) => {
        const slot = slots[part.type];
        const node = slot ? slot({ [part.type]: part.value, index, parts }) : [part.value];
        if (isVNode(node)) {
          node[0].key = `${part.type}-${index}`;
        }
        return node;
      });
    } else if (isString$1(parts)) {
      children = [parts];
    }
    const assignedAttrs = assign$1(create$1(), attrs);
    const tag = isString$1(props.tag) || isObject$1(props.tag) ? props.tag : getFragmentableTag();
    return h(tag, assignedAttrs, children);
  };
}
const NumberFormatImpl = /* @__PURE__ */ defineComponent({
  /* eslint-disable */
  name: "i18n-n",
  props: assign$1({
    value: {
      type: Number,
      required: true
    },
    format: {
      type: [String, Object]
    }
  }, baseFormatProps),
  /* eslint-enable */
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  setup(props, context) {
    const i18n2 = props.i18n || useI18n({
      useScope: props.scope,
      __useComponent: true
    });
    return renderFormatter(props, context, NUMBER_FORMAT_OPTIONS_KEYS, (...args) => (
      // eslint-disable-next-line @typescript-eslint/no-explicit-any
      i18n2[NumberPartsSymbol](...args)
    ));
  }
});
const NumberFormat = NumberFormatImpl;
const DatetimeFormatImpl = /* @__PURE__ */ defineComponent({
  /* eslint-disable */
  name: "i18n-d",
  props: assign$1({
    value: {
      type: [Number, Date],
      required: true
    },
    format: {
      type: [String, Object]
    }
  }, baseFormatProps),
  /* eslint-enable */
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  setup(props, context) {
    const i18n2 = props.i18n || useI18n({
      useScope: props.scope,
      __useComponent: true
    });
    return renderFormatter(props, context, DATETIME_FORMAT_OPTIONS_KEYS, (...args) => (
      // eslint-disable-next-line @typescript-eslint/no-explicit-any
      i18n2[DatetimePartsSymbol](...args)
    ));
  }
});
const DatetimeFormat = DatetimeFormatImpl;
function getComposer$1(i18n2, instance) {
  const i18nInternal = i18n2;
  if (i18n2.mode === "composition") {
    return i18nInternal.__getInstance(instance) || i18n2.global;
  } else {
    const vueI18n = i18nInternal.__getInstance(instance);
    return vueI18n != null ? vueI18n.__composer : i18n2.global.__composer;
  }
}
function vTDirective(i18n2) {
  const _process = (binding) => {
    const { instance, value } = binding;
    if (!instance || !instance.$) {
      throw createI18nError(I18nErrorCodes.UNEXPECTED_ERROR);
    }
    const composer = getComposer$1(i18n2, instance.$);
    const parsedValue = parseValue(value);
    return [
      Reflect.apply(composer.t, composer, [...makeParams(parsedValue)]),
      composer
    ];
  };
  const register = (el, binding) => {
    const [textContent, composer] = _process(binding);
    if (inBrowser$1 && i18n2.global === composer) {
      el.__i18nWatcher = watch(composer.locale, () => {
        binding.instance && binding.instance.$forceUpdate();
      });
    }
    el.__composer = composer;
    el.textContent = textContent;
  };
  const unregister = (el) => {
    if (inBrowser$1 && el.__i18nWatcher) {
      el.__i18nWatcher();
      el.__i18nWatcher = void 0;
      delete el.__i18nWatcher;
    }
    if (el.__composer) {
      el.__composer = void 0;
      delete el.__composer;
    }
  };
  const update = (el, { value }) => {
    if (el.__composer) {
      const composer = el.__composer;
      const parsedValue = parseValue(value);
      el.textContent = Reflect.apply(composer.t, composer, [
        ...makeParams(parsedValue)
      ]);
    }
  };
  const getSSRProps = (binding) => {
    const [textContent] = _process(binding);
    return { textContent };
  };
  return {
    created: register,
    unmounted: unregister,
    beforeUpdate: update,
    getSSRProps
  };
}
function parseValue(value) {
  if (isString$1(value)) {
    return { path: value };
  } else if (isPlainObject$9(value)) {
    if (!("path" in value)) {
      throw createI18nError(I18nErrorCodes.REQUIRED_VALUE, "path");
    }
    return value;
  } else {
    throw createI18nError(I18nErrorCodes.INVALID_VALUE);
  }
}
function makeParams(value) {
  const { path, locale, args, choice, plural } = value;
  const options = {};
  const named = args || {};
  if (isString$1(locale)) {
    options.locale = locale;
  }
  if (isNumber$1(choice)) {
    options.plural = choice;
  }
  if (isNumber$1(plural)) {
    options.plural = plural;
  }
  return [path, named, options];
}
function apply(app, i18n2, ...options) {
  const pluginOptions = isPlainObject$9(options[0]) ? options[0] : {};
  const globalInstall = isBoolean$1(pluginOptions.globalInstall) ? pluginOptions.globalInstall : true;
  if (globalInstall) {
    [Translation.name, "I18nT"].forEach((name) => app.component(name, Translation));
    [NumberFormat.name, "I18nN"].forEach((name) => app.component(name, NumberFormat));
    [DatetimeFormat.name, "I18nD"].forEach((name) => app.component(name, DatetimeFormat));
  }
  {
    app.directive("t", vTDirective(i18n2));
  }
}
const I18nInjectionKey = /* @__PURE__ */ makeSymbol("global-vue-i18n");
function createI18n(options = {}, VueI18nLegacy) {
  const __legacyMode = __VUE_I18N_LEGACY_API__ && isBoolean$1(options.legacy) ? options.legacy : __VUE_I18N_LEGACY_API__;
  const __globalInjection = isBoolean$1(options.globalInjection) ? options.globalInjection : true;
  const __instances = /* @__PURE__ */ new Map();
  const [globalScope, __global] = createGlobal(options, __legacyMode);
  const symbol = /* @__PURE__ */ makeSymbol("vue-i18n");
  function __getInstance(component) {
    return __instances.get(component) || null;
  }
  function __setInstance(component, instance) {
    __instances.set(component, instance);
  }
  function __deleteInstance(component) {
    __instances.delete(component);
  }
  const i18n2 = {
    // mode
    get mode() {
      return __VUE_I18N_LEGACY_API__ && __legacyMode ? "legacy" : "composition";
    },
    // install plugin
    async install(app, ...options2) {
      {
        app.__VUE_I18N__ = i18n2;
      }
      app.__VUE_I18N_SYMBOL__ = symbol;
      app.provide(app.__VUE_I18N_SYMBOL__, i18n2);
      if (isPlainObject$9(options2[0])) {
        const opts = options2[0];
        i18n2.__composerExtend = opts.__composerExtend;
        i18n2.__vueI18nExtend = opts.__vueI18nExtend;
      }
      let globalReleaseHandler = null;
      if (!__legacyMode && __globalInjection) {
        globalReleaseHandler = injectGlobalFields(app, i18n2.global);
      }
      if (__VUE_I18N_FULL_INSTALL__) {
        apply(app, i18n2, ...options2);
      }
      if (__VUE_I18N_LEGACY_API__ && __legacyMode) {
        app.mixin(defineMixin(__global, __global.__composer, i18n2));
      }
      const unmountApp = app.unmount;
      app.unmount = () => {
        globalReleaseHandler && globalReleaseHandler();
        i18n2.dispose();
        unmountApp();
      };
      {
        const ret = await enableDevTools(app, i18n2);
        if (!ret) {
          throw createI18nError(I18nErrorCodes.CANNOT_SETUP_VUE_DEVTOOLS_PLUGIN);
        }
        const emitter = createEmitter();
        if (__legacyMode) {
          const _vueI18n = __global;
          _vueI18n.__enableEmitter && _vueI18n.__enableEmitter(emitter);
        } else {
          const _composer = __global;
          _composer[EnableEmitter] && _composer[EnableEmitter](emitter);
        }
        emitter.on("*", addTimelineEvent);
      }
    },
    // global accessor
    get global() {
      return __global;
    },
    dispose() {
      globalScope.stop();
    },
    // @internal
    __instances,
    // @internal
    __getInstance,
    // @internal
    __setInstance,
    // @internal
    __deleteInstance
  };
  return i18n2;
}
function useI18n(options = {}) {
  const instance = getCurrentInstance();
  if (instance == null) {
    throw createI18nError(I18nErrorCodes.MUST_BE_CALL_SETUP_TOP);
  }
  if (!instance.isCE && instance.appContext.app != null && !instance.appContext.app.__VUE_I18N_SYMBOL__) {
    throw createI18nError(I18nErrorCodes.NOT_INSTALLED);
  }
  const i18n2 = getI18nInstance(instance);
  const gl = getGlobalComposer(i18n2);
  const componentOptions = getComponentOptions(instance);
  const scope = getScope(options, componentOptions);
  if (scope === "global") {
    adjustI18nResources(gl, options, componentOptions);
    return gl;
  }
  if (scope === "parent") {
    let composer2 = getComposer(i18n2, instance, options.__useComponent);
    if (composer2 == null) {
      {
        warn$1(getWarnMessage(I18nWarnCodes.NOT_FOUND_PARENT_SCOPE));
      }
      composer2 = gl;
    }
    return composer2;
  }
  const i18nInternal = i18n2;
  let composer = i18nInternal.__getInstance(instance);
  if (composer == null) {
    const composerOptions = assign$1({}, options);
    if ("__i18n" in componentOptions) {
      composerOptions.__i18n = componentOptions.__i18n;
    }
    if (gl) {
      composerOptions.__root = gl;
    }
    composer = createComposer(composerOptions);
    if (i18nInternal.__composerExtend) {
      composer[DisposeSymbol] = i18nInternal.__composerExtend(composer);
    }
    setupLifeCycle(i18nInternal, instance, composer);
    i18nInternal.__setInstance(instance, composer);
  }
  return composer;
}
function createGlobal(options, legacyMode, VueI18nLegacy) {
  const scope = effectScope();
  const obj = __VUE_I18N_LEGACY_API__ && legacyMode ? scope.run(() => createVueI18n(options)) : scope.run(() => createComposer(options));
  if (obj == null) {
    throw createI18nError(I18nErrorCodes.UNEXPECTED_ERROR);
  }
  return [scope, obj];
}
function getI18nInstance(instance) {
  const i18n2 = inject(!instance.isCE ? instance.appContext.app.__VUE_I18N_SYMBOL__ : I18nInjectionKey);
  if (!i18n2) {
    throw createI18nError(!instance.isCE ? I18nErrorCodes.UNEXPECTED_ERROR : I18nErrorCodes.NOT_INSTALLED_WITH_PROVIDE);
  }
  return i18n2;
}
function getScope(options, componentOptions) {
  return isEmptyObject$1(options) ? "__i18n" in componentOptions ? "local" : "global" : !options.useScope ? "local" : options.useScope;
}
function getGlobalComposer(i18n2) {
  return i18n2.mode === "composition" ? i18n2.global : i18n2.global.__composer;
}
function getComposer(i18n2, target, useComponent = false) {
  let composer = null;
  const root = target.root;
  let current = getParentComponentInstance(target, useComponent);
  while (current != null) {
    const i18nInternal = i18n2;
    if (i18n2.mode === "composition") {
      composer = i18nInternal.__getInstance(current);
    } else {
      if (__VUE_I18N_LEGACY_API__) {
        const vueI18n = i18nInternal.__getInstance(current);
        if (vueI18n != null) {
          composer = vueI18n.__composer;
          if (useComponent && composer && !composer[InejctWithOptionSymbol]) {
            composer = null;
          }
        }
      }
    }
    if (composer != null) {
      break;
    }
    if (root === current) {
      break;
    }
    current = current.parent;
  }
  return composer;
}
function getParentComponentInstance(target, useComponent = false) {
  if (target == null) {
    return null;
  }
  return !useComponent ? target.parent : target.vnode.ctx || target.parent;
}
function setupLifeCycle(i18n2, target, composer) {
  let emitter = null;
  onMounted(() => {
    if (target.vnode.el) {
      target.vnode.el.__VUE_I18N__ = composer;
      emitter = createEmitter();
      const _composer = composer;
      _composer[EnableEmitter] && _composer[EnableEmitter](emitter);
      emitter.on("*", addTimelineEvent);
    }
  }, target);
  onUnmounted(() => {
    const _composer = composer;
    if (target.vnode.el && target.vnode.el.__VUE_I18N__) {
      emitter && emitter.off("*", addTimelineEvent);
      _composer[DisableEmitter] && _composer[DisableEmitter]();
      delete target.vnode.el.__VUE_I18N__;
    }
    i18n2.__deleteInstance(target);
    const dispose = _composer[DisposeSymbol];
    if (dispose) {
      dispose();
      delete _composer[DisposeSymbol];
    }
  }, target);
}
const globalExportProps = [
  "locale",
  "fallbackLocale",
  "availableLocales"
];
const globalExportMethods = ["t", "rt", "d", "n", "tm", "te"];
function injectGlobalFields(app, composer) {
  const i18n2 = /* @__PURE__ */ Object.create(null);
  globalExportProps.forEach((prop) => {
    const desc = Object.getOwnPropertyDescriptor(composer, prop);
    if (!desc) {
      throw createI18nError(I18nErrorCodes.UNEXPECTED_ERROR);
    }
    const wrap = isRef(desc.value) ? {
      get() {
        return desc.value.value;
      },
      // eslint-disable-next-line @typescript-eslint/no-explicit-any
      set(val) {
        desc.value.value = val;
      }
    } : {
      get() {
        return desc.get && desc.get();
      }
    };
    Object.defineProperty(i18n2, prop, wrap);
  });
  app.config.globalProperties.$i18n = i18n2;
  globalExportMethods.forEach((method) => {
    const desc = Object.getOwnPropertyDescriptor(composer, method);
    if (!desc || !desc.value) {
      throw createI18nError(I18nErrorCodes.UNEXPECTED_ERROR);
    }
    Object.defineProperty(app.config.globalProperties, `$${method}`, desc);
  });
  const dispose = () => {
    delete app.config.globalProperties.$i18n;
    globalExportMethods.forEach((method) => {
      delete app.config.globalProperties[`$${method}`];
    });
  };
  return dispose;
}
{
  initFeatureFlags();
}
registerMessageCompiler(compile);
registerMessageResolver(resolveValue);
registerLocaleFallbacker(fallbackWithLocaleChain);
{
  const target = getGlobalThis$1();
  target.__INTLIFY__ = true;
  setDevToolsHook(target.__INTLIFY_DEVTOOLS_GLOBAL_HOOK__);
}
const DEFAULT_GRID_CONFIG = {
  startHour: 0,
  endHour: 24,
  startMinute: 0,
  endMinute: 24 * 60,
  hourHeight: 160,
  snapInterval: 15,
  columnMinWidth: 120,
  showAllDaySection: true,
  allDaySectionTitle: ""
};
const DEFAULT_FIRST_DAY_OF_WEEK = 1;
const DEFAULT_TIME_FORMAT = "g:i A";
const DEFAULT_INTERACTION_CONFIG = {
  enableCardDragDrop: false,
  enableCardResize: false,
  showAddAppointmentButton: true,
  showEditAppointmentButton: true
};
const DEFAULT_POPOVER_CONFIG = {
  customerName: true,
  dateTime: true,
  serviceName: true,
  customerEmail: true,
  customerPhone: true,
  staffMemberName: false,
  location: false,
  numberOfPerson: false,
  price: true,
  showStatusDropdown: true,
  enableStatusDropdown: true,
  showEditAppointmentButton: true,
  enableEditAppointmentButton: true,
  showRescheduleAppointmentButton: true,
  enableRescheduleAppointmentButton: true,
  additionalDetails: []
};
const calendar$3 = { "today": "Today", "month": "Month", "week": "Week", "timeline": "Timeline", "filter": "Filter", "displaySettings": "Card Fields", "apply": "Save", "addNew": "Add New", "addAppointment": "Add Appointment", "allDay": "All Day", "close": "Close", "saveComingSoon": "Save Coming Soon", "extraDetails": "Extra Details", "noStaff": "-", "noLocation": "-" };
const en = {
  calendar: calendar$3
};
const calendar$2 = { "today": "Hoy", "week": "Semana", "timeline": "Línea de tiempo", "filter": "Filtrar", "displaySettings": "Campos de tarjeta", "apply": "Guardar", "addNew": "Añadir nuevo", "allDay": "Todo el día", "close": "Cerrar", "extraDetails": "Detalles adicionales", "noStaff": "—", "noLocation": "—" };
const es = {
  calendar: calendar$2
};
const calendar$1 = { "today": "اليوم", "filter": "تصفية", "allDay": "طوال اليوم", "extraDetails": "تفاصيل إضافية", "noStaff": "—", "noLocation": "—" };
const ar = {
  calendar: calendar$1
};
const calendar = { "today": "آج", "filter": "فلٹر", "allDay": "سارا دن", "extraDetails": "اضافی تفصیلات", "noStaff": "—", "noLocation": "—" };
const ur = {
  calendar
};
const i18n = createI18n({
  legacy: false,
  // Ensure Vue 3 Composition API mode is used
  locale: "en",
  // Default locale
  fallbackLocale: "en",
  messages: {
    en,
    es,
    ar,
    ur
  }
});
const DEFAULT_DATA_TIME_FORMAT = "H:i";
const DATE_ONLY_INPUT_PATTERN = /^(\d{4})-(\d{1,2})-(\d{1,2})$/;
const CALENDAR_TIME_FORMAT_TOKENS = ["H", "G", "h", "g", "i", "s", "A", "a"];
const CALENDAR_TIME_FORMAT_TOKEN_SET = new Set(CALENDAR_TIME_FORMAT_TOKENS);
const DAYJS_TIME_TOKEN_PATTERN = /(?:HH|hh|mm)/;
let configuredTimeFormat = DEFAULT_TIME_FORMAT;
let hasExplicitTimeFormat = false;
function getLocale() {
  return i18n.global.locale.value ?? "en";
}
function hasProvidedTimeFormat(value) {
  if (typeof value === "string") {
    return value.trim().length > 0;
  }
  return value !== null && value !== void 0;
}
function isCalendarTimeFormatToken(value) {
  return CALENDAR_TIME_FORMAT_TOKEN_SET.has(value);
}
function isAsciiLetter(value) {
  return /^[A-Za-z]$/.test(value);
}
function pushLiteral(segments, value) {
  if (!value) {
    return;
  }
  const lastSegment = segments[segments.length - 1];
  if ((lastSegment == null ? void 0 : lastSegment.type) === "literal") {
    lastSegment.value += value;
    return;
  }
  segments.push({ type: "literal", value });
}
function tokenizeCalendarTimeFormat(format2) {
  const segments = [];
  let index = 0;
  while (index < format2.length) {
    const current = format2[index];
    if (current === "\\") {
      const escaped = format2[index + 1];
      pushLiteral(segments, escaped ?? current);
      index += escaped ? 2 : 1;
      continue;
    }
    if (current === "[") {
      const closingIndex = format2.indexOf("]", index + 1);
      if (closingIndex !== -1) {
        pushLiteral(segments, format2.slice(index + 1, closingIndex));
        index = closingIndex + 1;
        continue;
      }
    }
    if (isAsciiLetter(current)) {
      const wordStart = index;
      while (index < format2.length && isAsciiLetter(format2[index])) {
        index += 1;
      }
      const word = format2.slice(wordStart, index);
      if (word.length > 1 && Array.from(word).some((char) => !isCalendarTimeFormatToken(char))) {
        pushLiteral(segments, word);
        continue;
      }
      for (const char of word) {
        if (isCalendarTimeFormatToken(char)) {
          segments.push({ type: "token", value: char });
        } else {
          pushLiteral(segments, char);
        }
      }
      continue;
    }
    pushLiteral(segments, current);
    index += 1;
  }
  return segments;
}
function hasCalendarTimeFormatToken(format2) {
  return tokenizeCalendarTimeFormat(format2).some((segment) => segment.type === "token");
}
function getFormatOutsideBracketLiterals(format2) {
  let outside = "";
  let index = 0;
  while (index < format2.length) {
    const current = format2[index];
    if (current === "\\") {
      index += format2[index + 1] ? 2 : 1;
      continue;
    }
    if (current === "[") {
      const closingIndex = format2.indexOf("]", index + 1);
      if (closingIndex !== -1) {
        index = closingIndex + 1;
        continue;
      }
    }
    outside += current;
    index += 1;
  }
  return outside;
}
function normalizeDayJsTimeFormat(value) {
  const trimmed = value.trim();
  if (!DAYJS_TIME_TOKEN_PATTERN.test(getFormatOutsideBracketLiterals(trimmed))) {
    return null;
  }
  let result = "";
  let index = 0;
  while (index < trimmed.length) {
    const current = trimmed[index];
    if (current === "\\") {
      const escaped = trimmed[index + 1];
      result += escaped ? `${current}${escaped}` : current;
      index += escaped ? 2 : 1;
      continue;
    }
    if (current === "[") {
      const closingIndex = trimmed.indexOf("]", index + 1);
      if (closingIndex !== -1) {
        result += trimmed.slice(index, closingIndex + 1);
        index = closingIndex + 1;
        continue;
      }
    }
    const twoCharacterToken = trimmed.slice(index, index + 2);
    switch (twoCharacterToken) {
      case "HH":
        result += "H";
        index += 2;
        continue;
      case "hh":
        result += "h";
        index += 2;
        continue;
      case "mm":
        result += "i";
        index += 2;
        continue;
      case "ss":
        result += "s";
        index += 2;
        continue;
    }
    switch (current) {
      case "H":
        result += "G";
        break;
      case "h":
        result += "g";
        break;
      default:
        result += current;
        break;
    }
    index += 1;
  }
  return hasCalendarTimeFormatToken(result) ? result : null;
}
function normalizeCalendarTimeFormat(value) {
  if (typeof value === "number") {
    if (value === 24) return "H:i";
    if (value === 12) return DEFAULT_TIME_FORMAT;
  }
  if (typeof value === "boolean") {
    return value ? "H:i" : DEFAULT_TIME_FORMAT;
  }
  if (typeof value !== "string") {
    return DEFAULT_TIME_FORMAT;
  }
  const trimmed = value.trim();
  if (!trimmed) {
    return DEFAULT_TIME_FORMAT;
  }
  const dayJsFormat = normalizeDayJsTimeFormat(trimmed);
  if (dayJsFormat) {
    return dayJsFormat;
  }
  const normalized = trimmed.toLowerCase();
  if (normalized === "24" || normalized === "24h" || normalized === "24hr" || normalized === "24hrs" || normalized === "24-hour" || normalized === "24 hours" || normalized === "24hour" || normalized === "24hours") {
    return "H:i";
  }
  if (normalized === "12" || normalized === "12h" || normalized === "12hr" || normalized === "12hrs" || normalized === "12-hour" || normalized === "12 hours" || normalized === "12hour" || normalized === "12hours" || normalized === "ampm" || normalized === "am/pm") {
    return DEFAULT_TIME_FORMAT;
  }
  if (hasCalendarTimeFormatToken(trimmed)) {
    return trimmed;
  }
  return DEFAULT_TIME_FORMAT;
}
function setCalendarTimeFormat(value) {
  hasExplicitTimeFormat = hasProvidedTimeFormat(value);
  configuredTimeFormat = hasExplicitTimeFormat ? normalizeCalendarTimeFormat(value) : DEFAULT_TIME_FORMAT;
}
function pad2(value) {
  return `${value}`.padStart(2, "0");
}
function toTwelveHour(hours) {
  return hours % 12 || 12;
}
function formatTimeParts(hours, minutes, seconds, format2) {
  let result = "";
  for (const segment of tokenizeCalendarTimeFormat(format2)) {
    if (segment.type === "literal") {
      result += segment.value;
      continue;
    }
    switch (segment.value) {
      case "H":
        result += pad2(hours);
        break;
      case "G":
        result += `${hours}`;
        break;
      case "h":
        result += pad2(toTwelveHour(hours));
        break;
      case "g":
        result += `${toTwelveHour(hours)}`;
        break;
      case "i":
        result += pad2(minutes);
        break;
      case "s":
        result += pad2(seconds);
        break;
      case "A":
        result += hours >= 12 ? "PM" : "AM";
        break;
      case "a":
        result += hours >= 12 ? "pm" : "am";
        break;
    }
  }
  return result;
}
function formatNormalizedTime(hours, minutes, seconds = 0, format2 = configuredTimeFormat) {
  const normalizedHours = (hours % 24 + 24) % 24;
  return formatTimeParts(normalizedHours, minutes, seconds, format2);
}
function parseLocalDateOnly(value) {
  const match = value.trim().match(DATE_ONLY_INPUT_PATTERN);
  if (!match) {
    return null;
  }
  const year = Number(match[1]);
  const month = Number(match[2]);
  const day = Number(match[3]);
  const parsed = new Date(year, month - 1, day);
  if (parsed.getFullYear() !== year || parsed.getMonth() !== month - 1 || parsed.getDate() !== day) {
    return null;
  }
  return parsed;
}
function parseDateInput(value) {
  if (value instanceof Date) {
    return Number.isNaN(value.getTime()) ? null : new Date(value);
  }
  if (typeof value === "string") {
    const trimmed = value.trim();
    if (!trimmed) {
      return null;
    }
    const dateOnly = parseLocalDateOnly(trimmed);
    if (dateOnly) {
      return dateOnly;
    }
    const parsed = new Date(trimmed);
    return Number.isNaN(parsed.getTime()) ? null : parsed;
  }
  if (typeof value === "number") {
    const parsed = new Date(value);
    return Number.isNaN(parsed.getTime()) ? null : parsed;
  }
  return null;
}
function startOfDay$1(d) {
  const r = new Date(d);
  r.setHours(0, 0, 0, 0);
  return r;
}
function startOfWeek(d, weekStartsOn = DEFAULT_FIRST_DAY_OF_WEEK) {
  const date = startOfDay$1(d);
  const day = date.getDay();
  const diff = (day < weekStartsOn ? 7 : 0) + day - weekStartsOn;
  date.setDate(date.getDate() - diff);
  return date;
}
function addDays(d, n) {
  const r = new Date(d);
  r.setDate(r.getDate() + n);
  return r;
}
function addMonthsClamped(d, n) {
  const sourceDate = new Date(d);
  if (!Number.isFinite(n) || n === 0) {
    return sourceDate;
  }
  const targetMonthStart = new Date(sourceDate.getFullYear(), sourceDate.getMonth() + n, 1);
  const targetLastDay = new Date(
    targetMonthStart.getFullYear(),
    targetMonthStart.getMonth() + 1,
    0
  ).getDate();
  sourceDate.setFullYear(
    targetMonthStart.getFullYear(),
    targetMonthStart.getMonth(),
    Math.min(sourceDate.getDate(), targetLastDay)
  );
  return sourceDate;
}
function getWeekDays(referenceDate, weekStartsOn = DEFAULT_FIRST_DAY_OF_WEEK) {
  const start = startOfWeek(referenceDate, weekStartsOn);
  return Array.from({ length: 7 }, (_, i) => addDays(start, i));
}
function isSameDay(a, b) {
  return a.getFullYear() === b.getFullYear() && a.getMonth() === b.getMonth() && a.getDate() === b.getDate();
}
function isToday(d) {
  return isSameDay(d, /* @__PURE__ */ new Date());
}
function formatDayLabel(d) {
  return `${d.getDate()}`;
}
function formatDayFull(d) {
  const day = new Intl.DateTimeFormat(getLocale(), { weekday: "long" }).format(d);
  return day.charAt(0).toUpperCase() + day.slice(1);
}
function formatFullDate(d) {
  const formatter = new Intl.DateTimeFormat(getLocale(), {
    weekday: "long",
    day: "numeric",
    month: "long",
    year: "numeric"
  });
  return formatter.format(d);
}
function formatMonthRange(days) {
  if (days.length === 0) return "";
  const first = days[0];
  const last = days[days.length - 1];
  const fDay = first.getDate();
  const lDay = last.getDate();
  const formatter = new Intl.DateTimeFormat(getLocale(), { month: "long" });
  const uncapF = formatter.format(first);
  const uncapL = formatter.format(last);
  const fMonth = uncapF.charAt(0).toUpperCase() + uncapF.slice(1);
  const lMonth = uncapL.charAt(0).toUpperCase() + uncapL.slice(1);
  if (first.getMonth() === last.getMonth()) {
    return `${fDay} - ${lDay} ${fMonth}`;
  }
  return `${fDay} ${fMonth} - ${lDay} ${lMonth}`;
}
function formatMonthYear(d) {
  const formatter = new Intl.DateTimeFormat(getLocale(), { month: "long", year: "numeric" });
  return formatter.format(d);
}
function getMonthDays(referenceDate, weekStartsOn = DEFAULT_FIRST_DAY_OF_WEEK) {
  const year = referenceDate.getFullYear();
  const month = referenceDate.getMonth();
  const firstDayOfMonth = new Date(year, month, 1);
  const startDate = startOfWeek(firstDayOfMonth, weekStartsOn);
  const days = [];
  let currentDate = startDate;
  while (days.length < 42) {
    days.push(currentDate);
    currentDate = addDays(currentDate, 1);
    if (days.length >= 35 && days.length % 7 === 0 && currentDate.getMonth() !== month) {
      break;
    }
  }
  return days;
}
function formatTime(d) {
  return formatNormalizedTime(d.getHours(), d.getMinutes(), d.getSeconds());
}
function formatTimeForDataEntry(d) {
  const dataFormat = hasExplicitTimeFormat ? configuredTimeFormat : DEFAULT_DATA_TIME_FORMAT;
  return formatNormalizedTime(d.getHours(), d.getMinutes(), d.getSeconds(), dataFormat);
}
function formatTimeForExtendedDataEntry(d, referenceDate = d) {
  const dayOffset = Math.round(
    (startOfDay$1(d).getTime() - startOfDay$1(referenceDate).getTime()) / 864e5
  );
  return formatTimeParts(
    d.getHours() + dayOffset * 24,
    d.getMinutes(),
    d.getSeconds(),
    "H:i:s"
  );
}
function formatTimeRange(start, end) {
  return `${formatTime(start)} - ${formatTime(end)}`;
}
function formatMinuteLabel(totalMinutes) {
  const normalizedMinutes = (Math.round(totalMinutes) % 1440 + 1440) % 1440;
  const hours = Math.floor(normalizedMinutes / 60);
  const minutes = normalizedMinutes % 60;
  return formatNormalizedTime(hours, minutes, 0);
}
const MINUTES_IN_DAY = 24 * 60;
function isPlainObject$7(value) {
  return typeof value === "object" && value !== null && !Array.isArray(value);
}
function toFiniteNumber(value) {
  if (typeof value === "number" && Number.isFinite(value)) {
    return value;
  }
  if (typeof value === "string") {
    const trimmed = value.trim();
    if (!trimmed) {
      return null;
    }
    const parsed = Number(trimmed);
    return Number.isFinite(parsed) ? parsed : null;
  }
  return null;
}
function parseTimeRangeValue(value) {
  var _a;
  if (value === void 0) {
    return null;
  }
  if (typeof value === "number" && Number.isFinite(value)) {
    return Math.round(value * 60);
  }
  if (typeof value !== "string") {
    return null;
  }
  const trimmed = value.trim();
  if (!trimmed) {
    return null;
  }
  if (/^\d+(?:\.\d+)?$/.test(trimmed)) {
    return Math.round(Number(trimmed) * 60);
  }
  const match = trimmed.match(/^(\d{1,2})(?::(\d{1,2}))?\s*([ap]m)?$/i);
  if (!match) {
    return null;
  }
  const hour = Number(match[1]);
  const minute = match[2] ? Number(match[2]) : 0;
  const meridiem = (_a = match[3]) == null ? void 0 : _a.toLowerCase();
  if (minute < 0 || minute > 59) {
    return null;
  }
  if (meridiem) {
    if (hour < 1 || hour > 12) {
      return null;
    }
    const normalizedHour = hour % 12 + (meridiem === "pm" ? 12 : 0);
    return normalizedHour * 60 + minute;
  }
  if (hour < 0 || hour > 24 || hour === 24 && minute !== 0) {
    return null;
  }
  return hour * 60 + minute;
}
function clampMinute(value, min, max) {
  return Math.max(min, Math.min(max, value));
}
function applyGridFields(target, source) {
  const startHour = toFiniteNumber(source.startHour);
  if (startHour !== null) {
    target.startHour = startHour;
    target.startMinute = Math.round(startHour * 60);
  }
  const endHour = toFiniteNumber(source.endHour);
  if (endHour !== null) {
    target.endHour = endHour;
    target.endMinute = Math.round(endHour * 60);
  }
  const startMinute = toFiniteNumber(source.startMinute);
  if (startMinute !== null) {
    target.startMinute = Math.round(startMinute);
    target.startHour = target.startMinute / 60;
  }
  const endMinute = toFiniteNumber(source.endMinute);
  if (endMinute !== null) {
    target.endMinute = Math.round(endMinute);
    target.endHour = target.endMinute / 60;
  }
  const hourHeight = toFiniteNumber(source.hourHeight);
  if (hourHeight !== null && hourHeight > 0) {
    target.hourHeight = hourHeight;
  }
  const snapInterval = toFiniteNumber(source.snapInterval);
  if (snapInterval !== null && snapInterval > 0) {
    target.snapInterval = Math.round(snapInterval);
  }
  const columnMinWidth = toFiniteNumber(source.columnMinWidth);
  if (columnMinWidth !== null && columnMinWidth > 0) {
    target.columnMinWidth = columnMinWidth;
  }
  if (typeof source.showAllDaySection === "boolean") {
    target.showAllDaySection = source.showAllDaySection;
  }
  if (typeof source.allDaySectionTitle === "string") {
    const trimmed = source.allDaySectionTitle.trim();
    target.allDaySectionTitle = trimmed || DEFAULT_GRID_CONFIG.allDaySectionTitle;
  }
}
function applyTimeRange(target, timeRange) {
  if (!isPlainObject$7(timeRange)) {
    return;
  }
  const startMinute = parseTimeRangeValue(timeRange.start);
  if (startMinute !== null) {
    target.startMinute = startMinute;
    target.startHour = startMinute / 60;
  }
  const endMinute = parseTimeRangeValue(timeRange.end);
  if (endMinute !== null) {
    target.endMinute = endMinute;
    target.endHour = endMinute / 60;
  }
}
function getGridStartMinute(config) {
  const startMinute = toFiniteNumber(config.startMinute);
  if (startMinute !== null) {
    return clampMinute(Math.round(startMinute), 0, MINUTES_IN_DAY - 1);
  }
  const startHour = toFiniteNumber(config.startHour) ?? DEFAULT_GRID_CONFIG.startHour;
  return clampMinute(Math.round(startHour * 60), 0, MINUTES_IN_DAY - 1);
}
function getGridEndMinute(config) {
  const endMinute = toFiniteNumber(config.endMinute);
  if (endMinute !== null) {
    return clampMinute(Math.round(endMinute), 1, MINUTES_IN_DAY);
  }
  const endHour = toFiniteNumber(config.endHour) ?? DEFAULT_GRID_CONFIG.endHour;
  return clampMinute(Math.round(endHour * 60), 1, MINUTES_IN_DAY);
}
function getGridTotalMinutes(config) {
  return Math.max(0, getGridEndMinute(config) - getGridStartMinute(config));
}
function getPixelsPerMinute(config) {
  return config.hourHeight / 60;
}
function timeToMinutesOfDay(date) {
  return date.getHours() * 60 + date.getMinutes();
}
function getHourMarkerMinutes(config) {
  const startMinute = getGridStartMinute(config);
  const endMinute = getGridEndMinute(config);
  const markers = [startMinute];
  let nextHour = startMinute % 60 === 0 ? startMinute + 60 : Math.ceil(startMinute / 60) * 60;
  while (nextHour < endMinute) {
    markers.push(nextHour);
    nextHour += 60;
  }
  if (markers[markers.length - 1] !== endMinute) {
    markers.push(endMinute);
  }
  return markers;
}
function getGridLineMinutes(config, intervalMinutes = 30) {
  const startMinute = getGridStartMinute(config);
  const endMinute = getGridEndMinute(config);
  const lines = [startMinute];
  let nextLine = startMinute % intervalMinutes === 0 ? startMinute + intervalMinutes : Math.ceil(startMinute / intervalMinutes) * intervalMinutes;
  while (nextLine < endMinute) {
    lines.push(nextLine);
    nextLine += intervalMinutes;
  }
  return lines;
}
function expandGridConfigForBookings(config, bookings = []) {
  if (bookings.length === 0) {
    return config;
  }
  const baseStartMinute = getGridStartMinute(config);
  const baseEndMinute = getGridEndMinute(config);
  let startMinute = baseStartMinute;
  let endMinute = baseEndMinute;
  for (const booking of bookings) {
    const bookingStartMinute = timeToMinutesOfDay(booking.start);
    const bookingEndMinute = timeToMinutesOfDay(booking.end);
    if (bookingStartMinute < startMinute) {
      startMinute = bookingStartMinute;
    }
    if (bookingEndMinute > endMinute) {
      endMinute = bookingEndMinute;
    }
  }
  if (startMinute === baseStartMinute && endMinute === baseEndMinute) {
    return config;
  }
  const roundedStartMinute = Math.max(0, Math.floor(startMinute / 60) * 60);
  const roundedEndMinute = Math.min(MINUTES_IN_DAY, Math.ceil(endMinute / 60) * 60);
  return {
    ...config,
    startHour: roundedStartMinute / 60,
    endHour: roundedEndMinute / 60,
    startMinute: roundedStartMinute,
    endMinute: roundedEndMinute
  };
}
function resolveGridConfig(config = {}) {
  const normalized = { ...DEFAULT_GRID_CONFIG };
  if (isPlainObject$7(config)) {
    applyGridFields(normalized, config);
    applyTimeRange(normalized, config.timeRange);
    if (isPlainObject$7(config.gridConfig)) {
      applyGridFields(normalized, config.gridConfig);
      applyTimeRange(normalized, config.gridConfig.timeRange);
    }
  }
  const snapInterval = normalized.snapInterval > 0 ? Math.round(normalized.snapInterval) : DEFAULT_GRID_CONFIG.snapInterval;
  const hourHeight = normalized.hourHeight > 0 ? normalized.hourHeight : DEFAULT_GRID_CONFIG.hourHeight;
  const columnMinWidth = normalized.columnMinWidth > 0 ? normalized.columnMinWidth : DEFAULT_GRID_CONFIG.columnMinWidth;
  let startMinute = getGridStartMinute(normalized);
  let endMinute = getGridEndMinute(normalized);
  if (endMinute <= startMinute) {
    startMinute = DEFAULT_GRID_CONFIG.startMinute ?? 0;
    endMinute = DEFAULT_GRID_CONFIG.endMinute ?? MINUTES_IN_DAY;
  }
  return {
    ...normalized,
    startHour: startMinute / 60,
    endHour: endMinute / 60,
    startMinute,
    endMinute,
    hourHeight,
    snapInterval,
    columnMinWidth,
    showAllDaySection: typeof normalized.showAllDaySection === "boolean" ? normalized.showAllDaySection : DEFAULT_GRID_CONFIG.showAllDaySection,
    allDaySectionTitle: typeof normalized.allDaySectionTitle === "string" && normalized.allDaySectionTitle.trim() ? normalized.allDaySectionTitle.trim() : DEFAULT_GRID_CONFIG.allDaySectionTitle
  };
}
function useCalendar(config = DEFAULT_GRID_CONFIG) {
  const injectedConfig = inject("bpaInitialConfig", {});
  const gridConfig = ref(resolveGridConfig({
    ...config,
    ...injectedConfig ?? {}
  }));
  const firstDayOfWeek = ref(normalizeFirstDayOfWeek(
    (injectedConfig == null ? void 0 : injectedConfig.firstDayOfWeek) ?? config.firstDayOfWeek
  ));
  const currentDate = ref(resolveInitialDate(
    (injectedConfig == null ? void 0 : injectedConfig.initialDate) ?? (injectedConfig == null ? void 0 : injectedConfig.currentDate)
  ));
  const currentView = ref("month");
  const weekDays = computed(() => currentView.value === "day" ? [new Date(currentDate.value)] : getWeekDays(currentDate.value, firstDayOfWeek.value));
  const monthDays = computed(() => getMonthDays(currentDate.value, firstDayOfWeek.value));
  const headerTitle = computed(() => {
    if (currentView.value === "month") {
      return formatMonthYear(currentDate.value);
    }
    if (currentView.value === "day") {
      return formatFullDate(currentDate.value);
    }
    return formatMonthRange(weekDays.value);
  });
  const dayColumns = computed(
    () => weekDays.value.map((date) => ({
      date,
      label: formatDayLabel(date),
      dayOfWeek: formatDayFull(date),
      isToday: isToday(date),
      bookings: [],
      allDayBookings: []
    }))
  );
  function goToToday() {
    currentDate.value = /* @__PURE__ */ new Date();
  }
  function goToPrevious() {
    const d = new Date(currentDate.value);
    if (currentView.value === "month") {
      currentDate.value = addMonthsClamped(d, -1);
      return;
    } else if (currentView.value === "day") {
      d.setDate(d.getDate() - 1);
    } else {
      d.setDate(d.getDate() - 7);
    }
    currentDate.value = d;
  }
  function goToNext() {
    const d = new Date(currentDate.value);
    if (currentView.value === "month") {
      currentDate.value = addMonthsClamped(d, 1);
      return;
    } else if (currentView.value === "day") {
      d.setDate(d.getDate() + 1);
    } else {
      d.setDate(d.getDate() + 7);
    }
    currentDate.value = d;
  }
  function setView(view) {
    currentView.value = view;
  }
  function setDate(date) {
    currentDate.value = resolveInitialDate(date);
  }
  return {
    currentDate,
    currentView,
    gridConfig,
    firstDayOfWeek,
    weekDays,
    monthDays,
    headerTitle,
    dayColumns,
    goToToday,
    goToPrevious,
    goToNext,
    setDate,
    setView
  };
}
function normalizeFirstDayOfWeek(value) {
  if (typeof value === "number" && Number.isInteger(value) && value >= 0 && value <= 6) {
    return value;
  }
  return DEFAULT_FIRST_DAY_OF_WEEK;
}
function resolveInitialDate(value) {
  return parseDateInput(value ?? /* @__PURE__ */ new Date()) ?? /* @__PURE__ */ new Date();
}
const DEFAULT_CALENDAR_UI_TEXT = {
  shell: {
    close: "Close",
    closeSidebar: "Close sidebar",
    openSidebar: "Open sidebar"
  },
  header: {
    logo: "Calendar",
    today: "Today",
    month: "Month",
    week: "Week",
    day: "Day",
    timeline: "Timeline",
    filter: "Filter",
    addNew: "Add New",
    weekLayoutToggle: "Week layout toggle"
  },
  displaySettings: {
    title: "Card Fields",
    save: "Save",
    reorder: "Reorder"
  },
  bookingFilters: {
    title: "Filters",
    apply: "Apply",
    fields: {
      service: "Service",
      status: "Status",
      employee: "Employee",
      location: "Location",
      category: "Category"
    },
    placeholders: {
      service: "Select service",
      status: "Select status",
      employee: "Select employee",
      location: "Select location",
      category: "Select category"
    }
  },
  allDay: {
    title: "Full day & Multi Day Bookings",
    empty: "No full day or multi day bookings",
    fullDay: "Full Day",
    multiDay: "Multi Day",
    expand: "Expand full day and multi day bookings",
    collapse: "Collapse full day and multi day bookings"
  },
  dayHeader: {
    expand: "Expand",
    collapse: "Collapse"
  },
  month: {
    more: "more"
  },
  eventPopover: {
    close: "Close event details",
    bookingStatus: "Booking status",
    editAppointment: "Edit appointment",
    rescheduleAppointment: "Reschedule appointment"
  },
  statuses: {
    approved: "Approved",
    pending: "Pending",
    cancelled: "Cancelled",
    rejected: "Rejected",
    noShow: "No-Show",
    completed: "Completed"
  },
  bookingForm: {
    title: "Add Appointment",
    intro: "This is the Vue 3 booking form shell for the next phase. The fields are wired and styled, but saving is still intentionally disabled.",
    phoneError: "Enter a valid phone number.",
    saveComingSoon: "Save Coming Soon",
    fields: {
      customerName: "Customer Name",
      customerPhone: "Customer Phone",
      service: "Service",
      staffMember: "Staff Member",
      location: "Location",
      startsAt: "Starts At",
      endsAt: "Ends At",
      price: "Price",
      status: "Status",
      notes: "Notes",
      attachments: "Attachments",
      sendNotifications: "Send Notifications"
    },
    placeholders: {
      customerName: "Enter customer name",
      customerPhone: "Enter customer phone",
      service: "Choose service",
      staffMember: "Choose staff",
      location: "Choose location",
      startsAt: "Select start",
      endsAt: "Select end",
      status: "Choose status",
      notes: "Add internal notes"
    },
    options: {
      service: {
        haircut: "Haircut",
        consultation: "Consultation",
        colorSession: "Color Session"
      },
      staff: {
        anna: "Anna Smith",
        liam: "Liam Carter",
        sara: "Sara Khan"
      },
      location: {
        studioA: "Studio A",
        studioB: "Studio B",
        online: "Online"
      }
    },
    upload: {
      title: "Drop files here",
      hint: "or click to browse"
    },
    notifications: {
      title: "Send Notifications",
      description: "Keep this on when the booking form gets connected to the live booking flow."
    }
  },
  sidebar: {
    ariaLabel: "Plugin sidebar",
    calendar: "Calendar",
    appointments: "Appointments",
    payments: "Payments",
    customers: "Customers",
    services: "Services",
    notifications: "Notifications",
    customize: "Customize",
    settings: "Settings",
    goPremium: "Go Premium"
  },
  displayFields: {
    customerName: "Customer Name",
    timeDuration: "Time Duration",
    serviceName: "Service Name",
    price: "Price"
  }
};
function isPlainObject$6(value) {
  return typeof value === "object" && value !== null && !Array.isArray(value);
}
function getLocaleOverride(overrides, locale) {
  var _a, _b;
  if (!overrides) {
    return void 0;
  }
  const normalizedLocale = String(locale ?? "").trim();
  if (!normalizedLocale) {
    return void 0;
  }
  const localeParts = normalizedLocale.split(/[-_]/).filter(Boolean);
  const candidates = [
    normalizedLocale,
    normalizedLocale.toLowerCase(),
    normalizedLocale.toUpperCase(),
    localeParts[0],
    (_a = localeParts[0]) == null ? void 0 : _a.toLowerCase(),
    (_b = localeParts[0]) == null ? void 0 : _b.toUpperCase()
  ].filter((candidate) => Boolean(candidate));
  for (const candidate of candidates) {
    const match = overrides[candidate];
    if (match) {
      return match;
    }
  }
  return void 0;
}
function mergeDeep(base, override) {
  if (!isPlainObject$6(base) || !isPlainObject$6(override)) {
    return override ?? base;
  }
  const result = { ...base };
  for (const [key, value] of Object.entries(override)) {
    if (typeof value === "undefined") {
      continue;
    }
    const current = base[key];
    if (isPlainObject$6(current) && isPlainObject$6(value)) {
      result[key] = mergeDeep(current, value);
      continue;
    }
    result[key] = value;
  }
  return result;
}
function resolveCalendarUiText(config = {}, locale = "en") {
  return mergeDeep(
    mergeDeep(DEFAULT_CALENDAR_UI_TEXT, config.uiText),
    getLocaleOverride(config.uiTextLocales, locale)
  );
}
function useCalendarText() {
  const injectedConfig = inject("bpaInitialConfig", {});
  const { locale } = useI18n();
  return computed(() => resolveCalendarUiText({
    uiText: injectedConfig.uiText,
    uiTextLocales: injectedConfig.uiTextLocales
  }, String(locale.value ?? "en")));
}
const CARD_MIN_WIDTH = 140;
const CARD_MIN_HEIGHT = 68;
function buildOverlapClusters(bookings) {
  if (bookings.length === 0) return [];
  const sorted = [...bookings].sort((a, b) => {
    const d = a.start.getTime() - b.start.getTime();
    return d !== 0 ? d : a.end.getTime() - b.end.getTime();
  });
  const clusters = [];
  let currentCluster = [sorted[0]];
  let clusterEnd = sorted[0].end.getTime();
  for (let i = 1; i < sorted.length; i++) {
    const booking = sorted[i];
    if (booking.start.getTime() < clusterEnd) {
      currentCluster.push(booking);
      clusterEnd = Math.max(clusterEnd, booking.end.getTime());
    } else {
      clusters.push(finalizeCluster(currentCluster, clusters.length));
      currentCluster = [booking];
      clusterEnd = booking.end.getTime();
    }
  }
  clusters.push(finalizeCluster(currentCluster, clusters.length));
  return clusters;
}
function finalizeCluster(bookings, id) {
  const laneAssignments = assignLanes(bookings);
  let maxLane = 0;
  laneAssignments.forEach((lane) => {
    if (lane > maxLane) maxLane = lane;
  });
  return {
    id,
    bookings,
    laneAssignments,
    totalLanes: maxLane + 1
  };
}
function assignLanes(bookings) {
  const sorted = [...bookings].sort((a, b) => {
    const d = a.start.getTime() - b.start.getTime();
    return d !== 0 ? d : a.end.getTime() - b.end.getTime();
  });
  const assignments = /* @__PURE__ */ new Map();
  const laneEnds = [];
  for (const booking of sorted) {
    const startMs = booking.start.getTime();
    let assignedLane = -1;
    for (let lane = 0; lane < laneEnds.length; lane++) {
      if (laneEnds[lane] <= startMs) {
        assignedLane = lane;
        break;
      }
    }
    if (assignedLane === -1) {
      assignedLane = laneEnds.length;
      laneEnds.push(0);
    }
    laneEnds[assignedLane] = booking.end.getTime();
    assignments.set(booking.id, assignedLane);
  }
  return assignments;
}
function computeTimePosition(booking, config) {
  const gridStartMinute = getGridStartMinute(config);
  const gridEndMinute = getGridEndMinute(config);
  const visibleStartMinute = Math.max(timeToMinutesOfDay(booking.start), gridStartMinute);
  const visibleEndMinute = Math.min(timeToMinutesOfDay(booking.end), gridEndMinute);
  const pixelsPerMinute = getPixelsPerMinute(config);
  const top = Math.max(0, (visibleStartMinute - gridStartMinute) * pixelsPerMinute);
  const durationHeight = Math.max(visibleEndMinute - visibleStartMinute, 0) * pixelsPerMinute;
  const baseHeight = Math.max(
    durationHeight,
    pixelsPerMinute * config.snapInterval,
    CARD_MIN_HEIGHT
  );
  return { top, height: Math.max(baseHeight, 24) };
}
function bookingIntersectsVisibleRange(booking, config) {
  const gridStartMinute = getGridStartMinute(config);
  const gridEndMinute = getGridEndMinute(config);
  const bookingStartMinute = timeToMinutesOfDay(booking.start);
  const bookingEndMinute = timeToMinutesOfDay(booking.end);
  return bookingEndMinute > gridStartMinute && bookingStartMinute < gridEndMinute;
}
function computeLanePosition(booking, cluster, cardWidth) {
  const lane = cluster.laneAssignments.get(booking.id) ?? 0;
  const GAP2 = 12;
  const PADDING_X = 12;
  return {
    leftPx: PADDING_X + lane * (cardWidth + GAP2),
    widthPx: cardWidth
  };
}
function computeColumnLayout(bookings, config) {
  const visibleBookings = bookings.filter((booking) => bookingIntersectsVisibleRange(booking, config));
  if (visibleBookings.length === 0) {
    return { positioned: [], requiredWidth: config.columnMinWidth, maxLanes: 0 };
  }
  const clusters = buildOverlapClusters(visibleBookings);
  const result = [];
  let maxLanes = 1;
  for (const c of clusters) {
    if (c.totalLanes > maxLanes) maxLanes = c.totalLanes;
  }
  const cardWidth = CARD_MIN_WIDTH;
  const GAP2 = 12;
  const PADDING_X = 12;
  const requiredWidth = Math.max(config.columnMinWidth, PADDING_X * 2 + maxLanes * cardWidth + Math.max(0, maxLanes - 1) * GAP2);
  for (const cluster of clusters) {
    for (const booking of cluster.bookings) {
      const { top, height } = computeTimePosition(booking, config);
      const { leftPx, widthPx } = computeLanePosition(booking, cluster, cardWidth);
      const laneIndex = cluster.laneAssignments.get(booking.id) ?? 0;
      const rect = {
        top,
        height,
        left: leftPx,
        width: widthPx
      };
      result.push({
        booking,
        rect,
        laneIndex,
        totalLanes: cluster.totalLanes,
        clusterId: cluster.id
      });
    }
  }
  return { positioned: result, requiredWidth, maxLanes };
}
function getDensityMode(overlapCount) {
  if (overlapCount <= 3) return "full";
  if (overlapCount <= 6) return "compact";
  return "ultra-compact";
}
function getDensityConfig(overlapCount) {
  const mode = getDensityMode(overlapCount);
  switch (mode) {
    case "full":
      return {
        mode,
        showMetadata: true,
        showStatus: true,
        showTime: true,
        showService: true,
        showCustomer: true,
        fontSize: "normal"
      };
    case "compact":
      return {
        mode,
        showMetadata: false,
        showStatus: true,
        showTime: true,
        showService: true,
        showCustomer: false,
        fontSize: "small"
      };
    case "ultra-compact":
      return {
        mode,
        showMetadata: false,
        showStatus: false,
        showTime: true,
        showService: false,
        showCustomer: false,
        fontSize: "xs"
      };
  }
}
function computeAllDayLayout(bookings, days) {
  if (bookings.length === 0 || days.length === 0) return [];
  const dayStarts = days.map((d) => startOfDay(d).getTime());
  const dayEnds = days.map((d) => startOfDay(d).getTime() + 864e5);
  const visibleStart = dayStarts[0];
  const visibleEnd = dayEnds[dayEnds.length - 1];
  const sorted = [...bookings].sort((a, b) => {
    const d = a.startDate.getTime() - b.startDate.getTime();
    return d !== 0 ? d : b.endDate.getTime() - a.endDate.getTime();
  });
  const rows = [];
  const rowEndCols = [];
  for (const booking of sorted) {
    const bStart = startOfDay(booking.startDate).getTime();
    const bEnd = startOfDay(booking.endDate).getTime() + 864e5;
    let startCol = -1;
    let endCol = -1;
    for (let i = 0; i < days.length; i++) {
      if (bStart < dayEnds[i] && bEnd > dayStarts[i]) {
        if (startCol === -1) startCol = i;
        endCol = i;
      }
    }
    if (startCol === -1) continue;
    let assignedRow = -1;
    for (let r = 0; r < rowEndCols.length; r++) {
      if (rowEndCols[r] < startCol) {
        assignedRow = r;
        break;
      }
    }
    if (assignedRow === -1) {
      assignedRow = rowEndCols.length;
      rowEndCols.push(-1);
    }
    rowEndCols[assignedRow] = endCol;
    rows.push({
      booking,
      row: assignedRow,
      startCol,
      endCol,
      spanCols: endCol - startCol + 1,
      continuesBefore: bStart < visibleStart,
      continuesAfter: bEnd > visibleEnd
    });
  }
  return rows;
}
function startOfDay(d) {
  const r = new Date(d);
  r.setHours(0, 0, 0, 0);
  return r;
}
function cloneDate(value) {
  return new Date(value);
}
function buildOriginalStart(booking) {
  return booking.originalStart ? cloneDate(booking.originalStart) : cloneDate(booking.start);
}
function buildOriginalEnd(booking) {
  return booking.originalEnd ? cloneDate(booking.originalEnd) : cloneDate(booking.end);
}
function buildOvernightStartSlice(booking, day) {
  const clampedEnd = cloneDate(day);
  clampedEnd.setHours(23, 59, 59, 999);
  return {
    ...booking,
    id: booking.id,
    sliceKey: `${booking.id}-overnight-start`,
    end: clampedEnd,
    isOvernightPart: true,
    overnightPosition: "start",
    originalStart: buildOriginalStart(booking),
    originalEnd: buildOriginalEnd(booking)
  };
}
function buildOvernightEndSlice(booking, day) {
  const clampedStart = cloneDate(day);
  clampedStart.setHours(0, 0, 0, 0);
  return {
    ...booking,
    id: booking.id,
    sliceKey: `${booking.id}-overnight-end`,
    start: clampedStart,
    isOvernightPart: true,
    overnightPosition: "end",
    originalStart: buildOriginalStart(booking),
    originalEnd: buildOriginalEnd(booking)
  };
}
function getTimeBookingSlicesForDay(booking, day) {
  if (booking.isOvernightPart) {
    return isSameDay(booking.start, day) || isSameDay(booking.end, day) ? [booking] : [];
  }
  const startsOnDay = isSameDay(booking.start, day);
  const endsOnDay = isSameDay(booking.end, day);
  const isOvernight = !isSameDay(booking.start, booking.end);
  if (!isOvernight) {
    return startsOnDay ? [booking] : [];
  }
  const result = [];
  if (startsOnDay) {
    result.push(buildOvernightStartSlice(booking, day));
  }
  if (endsOnDay) {
    result.push(buildOvernightEndSlice(booking, day));
  }
  return result;
}
function getTimeBookingsForDay(bookings, day) {
  return bookings.flatMap((booking) => getTimeBookingSlicesForDay(booking, day));
}
function useBookings(gridConfig) {
  const timeBookings = shallowRef([]);
  const allDayBookings = shallowRef([]);
  function upsertBookings(existing, incoming) {
    if (incoming.length === 0) {
      return existing;
    }
    const incomingById = /* @__PURE__ */ new Map();
    for (const booking of incoming) {
      if (!incomingById.has(booking.id)) {
        incomingById.set(booking.id, []);
      }
      incomingById.get(booking.id).push(booking);
    }
    const usedIndices = /* @__PURE__ */ new Map();
    const merged = [];
    for (const booking of existing) {
      const replacements = incomingById.get(booking.id) || [];
      const nextIndex = usedIndices.get(booking.id) || 0;
      if (nextIndex < replacements.length) {
        merged.push(replacements[nextIndex]);
        usedIndices.set(booking.id, nextIndex + 1);
      } else {
        merged.push(booking);
      }
    }
    for (const [id, replacements] of incomingById.entries()) {
      const usedCount = usedIndices.get(id) || 0;
      for (let i = usedCount; i < replacements.length; i++) {
        merged.push(replacements[i]);
      }
    }
    return merged;
  }
  function setTimeBookings(bookings) {
    timeBookings.value = bookings;
  }
  function setAllDayBookings(bookings) {
    allDayBookings.value = bookings;
  }
  function appendTimeBookings(bookings) {
    timeBookings.value = upsertBookings(timeBookings.value, bookings);
  }
  function appendAllDayBookings(bookings) {
    allDayBookings.value = upsertBookings(allDayBookings.value, bookings);
  }
  function replaceTimeBookings(bookings) {
    timeBookings.value = upsertBookings(timeBookings.value, bookings);
  }
  function replaceAllDayBookings(bookings) {
    allDayBookings.value = upsertBookings(allDayBookings.value, bookings);
  }
  function addTimeBooking(booking) {
    timeBookings.value = [...timeBookings.value, booking];
  }
  function addAllDayBooking(booking) {
    allDayBookings.value = [...allDayBookings.value, booking];
  }
  function updateTimeBooking(id, updates) {
    timeBookings.value = timeBookings.value.map(
      (b) => b.id === id ? { ...b, ...updates } : b
    );
  }
  function removeTimeBooking(id) {
    timeBookings.value = timeBookings.value.filter((b) => b.id !== id);
  }
  function removeAllDayBooking(id) {
    allDayBookings.value = allDayBookings.value.filter((b) => b.id !== id);
  }
  function getBookingsForDay(day) {
    return getTimeBookingsForDay(timeBookings.value, day);
  }
  function getAllDayLayoutForWeek(days) {
    return computeAllDayLayout(allDayBookings.value, days);
  }
  const columnLayoutCache = /* @__PURE__ */ new Map();
  function getColumnLayoutCached(day) {
    const dayBookings = getBookingsForDay(day);
    const config = gridConfig();
    const configKey = [
      config.startMinute ?? config.startHour,
      config.endMinute ?? config.endHour,
      config.hourHeight,
      config.snapInterval,
      config.columnMinWidth
    ].join("|");
    const key = `${day.toISOString().slice(0, 10)}:${configKey}:${dayBookings.map((b) => `${b.id}|${b.start.getTime()}|${b.end.getTime()}`).join(",")}`;
    if (columnLayoutCache.has(key)) return columnLayoutCache.get(key);
    const layout = computeColumnLayout(dayBookings, gridConfig());
    columnLayoutCache.set(key, layout);
    if (columnLayoutCache.size > 100) {
      const firstKey = columnLayoutCache.keys().next().value;
      columnLayoutCache.delete(firstKey);
    }
    return layout;
  }
  function invalidateLayoutCache() {
    columnLayoutCache.clear();
  }
  return {
    timeBookings,
    allDayBookings,
    setTimeBookings,
    setAllDayBookings,
    appendTimeBookings,
    appendAllDayBookings,
    replaceTimeBookings,
    replaceAllDayBookings,
    addTimeBooking,
    addAllDayBooking,
    updateTimeBooking,
    removeTimeBooking,
    removeAllDayBooking,
    getBookingsForDay,
    getColumnLayoutCached,
    getAllDayLayoutForWeek,
    invalidateLayoutCache
  };
}
function snapMinutes(minutes, interval) {
  return Math.round(minutes / interval) * interval;
}
function computeDragDelta(state, config, columnWidth) {
  const deltaY = state.currentY - state.originY;
  const pixelsPerMinute = getPixelsPerMinute(config);
  const rawDeltaMinutes = deltaY / pixelsPerMinute;
  const deltaMinutes = snapMinutes(rawDeltaMinutes, config.snapInterval);
  const deltaX = state.currentX - state.originX;
  const deltaColumns = Math.round(deltaX / columnWidth);
  return { deltaMinutes, deltaColumns };
}
function applyDragDelta(originalStart, originalEnd, delta, days, config, originalDayIndex) {
  const durationMs = originalEnd.getTime() - originalStart.getTime();
  const gridStartMinute = getGridStartMinute(config);
  const gridEndMinute = getGridEndMinute(config);
  let startDayIndex = typeof originalDayIndex === "number" && originalDayIndex >= 0 ? originalDayIndex : days.findIndex(
    (d) => d.getFullYear() === originalStart.getFullYear() && d.getMonth() === originalStart.getMonth() && d.getDate() === originalStart.getDate()
  );
  if (startDayIndex < 0) startDayIndex = 0;
  const newDayIndex = Math.max(
    0,
    Math.min(days.length - 1, startDayIndex + delta.deltaColumns)
  );
  const targetDay = days[newDayIndex];
  const startMinutes = timeToMinutesOfDay(originalStart) + delta.deltaMinutes;
  const maxStartMinute = Math.max(gridStartMinute, gridEndMinute - config.snapInterval);
  const clampedStart = Math.max(
    gridStartMinute,
    Math.min(maxStartMinute, startMinutes)
  );
  const snappedStart = snapMinutes(clampedStart, config.snapInterval);
  const newStart = new Date(targetDay);
  newStart.setHours(Math.floor(snappedStart / 60), snappedStart % 60, 0, 0);
  const newEnd = new Date(newStart.getTime() + durationMs);
  if (timeToMinutesOfDay(newEnd) > gridEndMinute) {
    return null;
  }
  return { newStart, newEnd, newDayIndex };
}
function computeResize(state, config) {
  const deltaY = state.currentY - state.originY;
  const pixelsPerMinute = getPixelsPerMinute(config);
  const rawDeltaMinutes = deltaY / pixelsPerMinute;
  const deltaMinutes = snapMinutes(rawDeltaMinutes, config.snapInterval);
  const gridStartMinute = getGridStartMinute(config);
  const gridEndMinute = getGridEndMinute(config);
  let newStart;
  let newEnd;
  if (state.type === "resize-top") {
    const startMinutes = timeToMinutesOfDay(state.originalStart) + deltaMinutes;
    const clamped = Math.max(gridStartMinute, startMinutes);
    const snapped = snapMinutes(clamped, config.snapInterval);
    newStart = new Date(state.originalStart);
    newStart.setHours(Math.floor(snapped / 60), snapped % 60, 0, 0);
    newEnd = new Date(state.originalEnd);
    if (newStart.getTime() >= newEnd.getTime() - config.snapInterval * 6e4) {
      return null;
    }
  } else {
    const endMinutes = timeToMinutesOfDay(state.originalEnd) + deltaMinutes;
    const clamped = Math.min(gridEndMinute, endMinutes);
    const snapped = snapMinutes(clamped, config.snapInterval);
    newStart = new Date(state.originalStart);
    newEnd = new Date(state.originalEnd);
    newEnd.setHours(Math.floor(snapped / 60), snapped % 60, 0, 0);
    if (newEnd.getTime() <= newStart.getTime() + config.snapInterval * 6e4) {
      return null;
    }
  }
  return { newStart, newEnd };
}
function createDragPreview(original, newStart, newEnd, newResourceId) {
  return {
    ...original,
    isPreview: true,
    start: newStart,
    end: newEnd,
    resourceId: newResourceId ?? original.resourceId
  };
}
function useDragResize(getConfig, getDays, getColumnWidth, callbacks) {
  const dragState = ref(null);
  const previewBooking = ref(null);
  const previewDayIndex = ref(-1);
  const originalDayIndexRef = ref(-1);
  const initialCardRectRef = ref(null);
  const isDragging = ref(false);
  function startInteraction(booking, type, pointerX, pointerY, dayIndex) {
    if (type === "none") return;
    const actualStart = booking.originalStart ?? booking.start;
    const actualEnd = booking.originalEnd ?? booking.end;
    dragState.value = {
      bookingId: booking.id,
      type,
      originX: pointerX,
      originY: pointerY,
      currentX: pointerX,
      currentY: pointerY,
      startTime: new Date(actualStart),
      endTime: new Date(actualEnd),
      originalStart: new Date(actualStart),
      originalEnd: new Date(actualEnd),
      originalResourceId: booking.resourceId,
      currentResourceId: booking.resourceId,
      isActive: true,
      snappedStart: new Date(actualStart),
      snappedEnd: new Date(actualEnd)
    };
    previewBooking.value = { ...booking, start: new Date(actualStart), end: new Date(actualEnd) };
    previewDayIndex.value = dayIndex;
    isDragging.value = true;
    document.addEventListener("pointermove", handlePointerMove);
    document.addEventListener("pointerup", handlePointerUp);
    document.body.style.userSelect = "none";
    document.body.style.cursor = type === "drag" ? "grabbing" : "ns-resize";
  }
  function handlePointerMove(e) {
    if (!dragState.value || !previewBooking.value) return;
    dragState.value = {
      ...dragState.value,
      currentX: e.clientX,
      currentY: e.clientY
    };
    const config = getConfig();
    const days = getDays();
    if (dragState.value.type === "drag") {
      const delta = computeDragDelta(dragState.value, config, getColumnWidth());
      const result = applyDragDelta(
        dragState.value.originalStart,
        dragState.value.originalEnd,
        delta,
        days,
        config,
        originalDayIndexRef.value
      );
      if (result) {
        dragState.value.snappedStart = result.newStart;
        dragState.value.snappedEnd = result.newEnd;
        previewBooking.value = createDragPreview(
          previewBooking.value,
          result.newStart,
          result.newEnd
        );
        previewDayIndex.value = result.newDayIndex;
      }
    } else {
      const result = computeResize(dragState.value, config);
      if (result) {
        dragState.value.snappedStart = result.newStart;
        dragState.value.snappedEnd = result.newEnd;
        previewBooking.value = createDragPreview(
          previewBooking.value,
          result.newStart,
          result.newEnd
        );
      }
    }
  }
  function startDrag(booking, pointerX, pointerY, dayIndex, cardRect) {
    originalDayIndexRef.value = dayIndex;
    initialCardRectRef.value = cardRect ? { left: cardRect.left, top: cardRect.top, width: cardRect.width, height: cardRect.height } : null;
    startInteraction(booking, "drag", pointerX, pointerY, dayIndex);
  }
  function startResizeTop(booking, pointerX, pointerY, dayIndex) {
    startInteraction(booking, "resize-top", pointerX, pointerY, dayIndex);
  }
  function startResizeBottom(booking, pointerX, pointerY, dayIndex) {
    startInteraction(booking, "resize-bottom", pointerX, pointerY, dayIndex);
  }
  function handlePointerUp() {
    document.removeEventListener("pointermove", handlePointerMove);
    document.removeEventListener("pointerup", handlePointerUp);
    document.body.style.userSelect = "";
    document.body.style.cursor = "";
    if (!dragState.value) return;
    const state = dragState.value;
    const hasMovement = Math.abs(state.currentX - state.originX) > 3 || Math.abs(state.currentY - state.originY) > 3;
    if (hasMovement) {
      if (state.type === "drag") {
        callbacks.onDragEnd(
          state.bookingId,
          state.snappedStart,
          state.snappedEnd,
          previewDayIndex.value
        );
      } else {
        callbacks.onResizeEnd(
          state.bookingId,
          state.snappedStart,
          state.snappedEnd
        );
      }
    } else {
      callbacks.onCancel();
    }
    dragState.value = null;
    previewBooking.value = null;
    previewDayIndex.value = -1;
    originalDayIndexRef.value = -1;
    initialCardRectRef.value = null;
    isDragging.value = false;
  }
  function cancelInteraction() {
    document.removeEventListener("pointermove", handlePointerMove);
    document.removeEventListener("pointerup", handlePointerUp);
    document.body.style.userSelect = "";
    document.body.style.cursor = "";
    dragState.value = null;
    previewBooking.value = null;
    previewDayIndex.value = -1;
    originalDayIndexRef.value = -1;
    initialCardRectRef.value = null;
    isDragging.value = false;
    callbacks.onCancel();
  }
  function getDragPreviewFixedStyle() {
    const state = dragState.value;
    const rect = initialCardRectRef.value;
    if (!state || state.type !== "drag" || !rect) return null;
    const dx = state.currentX - state.originX;
    const dy = state.currentY - state.originY;
    return {
      position: "fixed",
      left: `${rect.left + dx}px`,
      top: `${rect.top + dy}px`,
      width: `${rect.width}px`,
      height: `${rect.height}px`,
      zIndex: "1000",
      pointerEvents: "none"
    };
  }
  function getDragTransform() {
    if (!dragState.value || dragState.value.type !== "drag") return null;
    return {
      x: dragState.value.currentX - dragState.value.originX,
      y: dragState.value.currentY - dragState.value.originY
    };
  }
  return {
    dragState: readonly(dragState),
    previewBooking: readonly(previewBooking),
    previewDayIndex: readonly(previewDayIndex),
    originalDayIndex: readonly(originalDayIndexRef),
    isDragging: readonly(isDragging),
    startDrag,
    startResizeTop,
    startResizeBottom,
    cancelInteraction,
    getDragTransform,
    getDragPreviewFixedStyle
  };
}
function isPlainObject$5(value) {
  return typeof value === "object" && value !== null && !Array.isArray(value);
}
function normalizeBooleanLike$4(value) {
  if (typeof value === "boolean") {
    return value;
  }
  if (typeof value === "number") {
    if (value === 1) return true;
    if (value === 0) return false;
  }
  if (typeof value === "string") {
    const normalized = value.trim().toLowerCase();
    if (normalized === "true" || normalized === "1") return true;
    if (normalized === "false" || normalized === "0") return false;
  }
  return void 0;
}
function normalizeTextValue$1(value) {
  if (typeof value === "string") {
    return value.trim();
  }
  if (typeof value === "number" || typeof value === "boolean") {
    return String(value);
  }
  if (Array.isArray(value)) {
    return value.map((entry) => normalizeTextValue$1(entry)).filter(Boolean).join(", ");
  }
  return "";
}
function normalizeFilterValue$1(value) {
  if (typeof value === "string") {
    const trimmed = value.trim();
    return trimmed ? trimmed : null;
  }
  if (typeof value === "number" || typeof value === "boolean") {
    return value;
  }
  return null;
}
function normalizeFilterValues$1(value) {
  if (Array.isArray(value)) {
    return value.flatMap((entry) => normalizeFilterValues$1(entry));
  }
  const normalized = normalizeFilterValue$1(value);
  return normalized === null ? [] : [normalized];
}
function formatLabel$1(value) {
  return value.replace(/[_-]+/g, " ").replace(/([a-z])([A-Z])/g, "$1 $2").trim().replace(/\s+/g, " ").replace(/\b\w/g, (letter) => letter.toUpperCase());
}
function normalizeLookupKey$2(value) {
  return value.trim().toLowerCase().replace(/[^a-z0-9]+/g, "");
}
function getValueByAliases$2(source, keys) {
  for (const [entryKey, entryValue] of Object.entries(source)) {
    const normalizedEntryKey = normalizeLookupKey$2(entryKey);
    if (!normalizedEntryKey) {
      continue;
    }
    for (const key of keys) {
      if (normalizedEntryKey === normalizeLookupKey$2(key)) {
        return entryValue;
      }
    }
  }
  return void 0;
}
function readTextValue$1(sources, keys) {
  for (const source of sources) {
    for (const key of keys) {
      const value = normalizeTextValue$1(getValueByAliases$2(source, [key]));
      if (value) {
        return value;
      }
    }
  }
  return "";
}
function readFilterValue$1(sources, keys) {
  for (const source of sources) {
    for (const key of keys) {
      const value = normalizeFilterValue$1(getValueByAliases$2(source, [key]));
      if (value !== null) {
        return value;
      }
    }
  }
  return null;
}
function normalizeServiceSelection(entry) {
  if (typeof entry === "string" || typeof entry === "number" || typeof entry === "boolean") {
    const label = normalizeTextValue$1(entry);
    if (!label) {
      return null;
    }
    return {
      serviceId: null,
      serviceName: label,
      categoryId: null,
      categoryName: ""
    };
  }
  if (!isPlainObject$5(entry)) {
    return null;
  }
  const serviceCandidate = isPlainObject$5(entry.service) ? entry.service : null;
  const categoryCandidate = isPlainObject$5(entry.category) ? entry.category : null;
  const serviceSources = serviceCandidate ? [serviceCandidate, entry] : [entry];
  const categorySources = categoryCandidate ? [categoryCandidate, entry] : [entry];
  const serviceId = readFilterValue$1(serviceSources, ["serviceId", "serviceID", "service_id", "service_ids", "serviceIds", "id", "value"]);
  const serviceName = readTextValue$1(serviceSources, ["serviceName", "service_name", "service_names", "serviceNames", "serviceLabel", "service_label", "displayName", "display_name", "name", "label", "title"]);
  const categoryId = readFilterValue$1(categorySources, ["categoryId", "categoryID", "category_id", "category_ids", "categoryIds", "serviceCategoryId", "service_category_id", "serviceCategoryIds", "service_category_ids", "id", "value"]);
  const categoryName = readTextValue$1(categorySources, ["categoryName", "category_name", "category_names", "categoryNames", "categoryLabel", "category_label", "serviceCategoryLabel", "service_category_label", "displayName", "display_name", "serviceCategoryName", "service_category_name", "serviceCategoryNames", "service_category_names", "category", "label", "name", "title"]);
  if (!serviceName && serviceId === null && !categoryName && categoryId === null) {
    return null;
  }
  return {
    serviceId,
    serviceName,
    categoryId,
    categoryName
  };
}
function normalizeServiceSelections(value) {
  if (Array.isArray(value)) {
    return value.flatMap((entry) => normalizeServiceSelections(entry));
  }
  const normalized = normalizeServiceSelection(value);
  return normalized ? [normalized] : [];
}
function uniqueFilterValues$1(values) {
  const seen = /* @__PURE__ */ new Set();
  const result = [];
  values.forEach((value) => {
    const key = String(value);
    if (seen.has(key)) {
      return;
    }
    seen.add(key);
    result.push(value);
  });
  return result;
}
function uniqueTextValues$1(values) {
  const seen = /* @__PURE__ */ new Set();
  const result = [];
  values.forEach((value) => {
    const trimmed = value.trim();
    if (!trimmed) {
      return;
    }
    const key = trimmed.toLowerCase();
    if (seen.has(key)) {
      return;
    }
    seen.add(key);
    result.push(trimmed);
  });
  return result;
}
function buildServiceLabel(items) {
  return uniqueTextValues$1(items.map((item) => item.serviceName || normalizeTextValue$1(item.serviceId) || formatLabel$1(String(item.serviceId ?? "")))).join(", ");
}
function buildCategoryLabel(items) {
  return uniqueTextValues$1(items.map((item) => item.categoryName || normalizeTextValue$1(item.categoryId) || formatLabel$1(String(item.categoryId ?? "")))).join(", ");
}
function buildFallbackSelection(source, metadata) {
  const serviceSources = [];
  const categorySources = [];
  if (isPlainObject$5(source)) {
    const sourceRecord = source;
    serviceSources.push(sourceRecord);
    categorySources.push(sourceRecord);
  }
  if (isPlainObject$5(metadata)) {
    serviceSources.push(metadata);
    categorySources.push(metadata);
  }
  const serviceId = readFilterValue$1(serviceSources, ["serviceId", "serviceID", "service_id", "service_ids", "serviceIds"]);
  const serviceName = readTextValue$1(serviceSources, ["serviceName", "service_name", "service_names", "serviceNames", "serviceLabel", "service_label", "displayName", "display_name", "name", "label", "title"]);
  const categoryId = readFilterValue$1(categorySources, ["categoryId", "categoryID", "category_id", "category_ids", "categoryIds", "serviceCategoryId", "service_category_id", "serviceCategoryIds", "service_category_ids"]);
  const categoryName = readTextValue$1(categorySources, ["categoryName", "category_name", "category_names", "categoryNames", "categoryLabel", "category_label", "serviceCategoryLabel", "service_category_label", "displayName", "display_name", "serviceCategoryName", "service_category_name", "serviceCategoryNames", "service_category_names", "category", "label", "name", "title"]);
  if (!serviceName && serviceId === null && !categoryName && categoryId === null) {
    return null;
  }
  return {
    serviceId,
    serviceName,
    categoryId,
    categoryName
  };
}
function resolveBookingServiceSummary(source) {
  const metadata = isPlainObject$5(source.metadata) ? source.metadata : null;
  const rawServicesData = source.servicesData ?? (isPlainObject$5(source) ? getValueByAliases$2(source, ["servicesData", "services_data"]) : void 0) ?? (metadata ? getValueByAliases$2(metadata, ["servicesData", "services_data"]) : void 0);
  const parsedItems = normalizeServiceSelections(rawServicesData);
  const hasServicesData = rawServicesData !== void 0 && rawServicesData !== null;
  const isMultiService = normalizeBooleanLike$4(source.isMultiService) ?? normalizeBooleanLike$4(metadata == null ? void 0 : metadata.isMultiService) ?? hasServicesData;
  const fallbackSelection = buildFallbackSelection(source, metadata);
  const items = parsedItems.length > 0 ? parsedItems : hasServicesData ? [] : fallbackSelection ? [fallbackSelection] : [];
  const serviceValues = uniqueFilterValues$1(items.flatMap((item) => normalizeFilterValues$1(item.serviceId ?? item.serviceName)));
  const categoryValues = uniqueFilterValues$1(items.flatMap((item) => normalizeFilterValues$1(item.categoryId ?? item.categoryName)));
  return {
    isMultiService: !!isMultiService || items.length > 1,
    items,
    serviceValues,
    serviceLabel: items.length > 0 ? buildServiceLabel(items) : hasServicesData ? "" : normalizeTextValue$1(source.serviceName) || normalizeTextValue$1(metadata == null ? void 0 : metadata.serviceName),
    categoryValues,
    categoryLabel: items.length > 0 ? buildCategoryLabel(items) : hasServicesData ? "" : normalizeTextValue$1(source.categoryName) || normalizeTextValue$1(source.category) || normalizeTextValue$1(source.categoryId) || normalizeTextValue$1(metadata == null ? void 0 : metadata.categoryName) || normalizeTextValue$1(metadata == null ? void 0 : metadata.category) || normalizeTextValue$1(metadata == null ? void 0 : metadata.categoryId)
  };
}
function resolveBookingServiceLabel(source) {
  return resolveBookingServiceSummary(source).serviceLabel;
}
function isPlainObject$4(value) {
  return typeof value === "object" && value !== null && !Array.isArray(value);
}
function normalizeBooleanLike$3(value) {
  if (typeof value === "boolean") {
    return value;
  }
  if (typeof value === "number") {
    if (value === 1) return true;
    if (value === 0) return false;
  }
  if (typeof value === "string") {
    const normalized = value.trim().toLowerCase();
    if (normalized === "true" || normalized === "1") return true;
    if (normalized === "false" || normalized === "0") return false;
  }
  return void 0;
}
function normalizeTextValue(value) {
  if (typeof value === "string") {
    return value.trim();
  }
  if (typeof value === "number" || typeof value === "boolean") {
    return String(value);
  }
  if (Array.isArray(value)) {
    return value.map((entry) => normalizeTextValue(entry)).filter(Boolean).join(", ");
  }
  return "";
}
function normalizeTextValues(value) {
  if (Array.isArray(value)) {
    return value.flatMap((entry) => normalizeTextValues(entry));
  }
  const normalized = normalizeTextValue(value);
  return normalized ? [normalized] : [];
}
function normalizeFilterValue(value) {
  if (typeof value === "string") {
    const trimmed = value.trim();
    return trimmed ? trimmed : null;
  }
  if (typeof value === "number" || typeof value === "boolean") {
    return value;
  }
  return null;
}
function normalizeFilterValues(value) {
  if (Array.isArray(value)) {
    return value.flatMap((entry) => normalizeFilterValues(entry));
  }
  const normalized = normalizeFilterValue(value);
  return normalized === null ? [] : [normalized];
}
function isZeroLikeStaffId(value) {
  if (typeof value === "number") {
    return value === 0;
  }
  if (typeof value === "string") {
    return value.trim() === "0";
  }
  return false;
}
function formatLabel(value) {
  return value.replace(/[_-]+/g, " ").replace(/([a-z])([A-Z])/g, "$1 $2").trim().replace(/\s+/g, " ").replace(/\b\w/g, (letter) => letter.toUpperCase());
}
function normalizeLookupKey$1(value) {
  return value.trim().toLowerCase().replace(/[^a-z0-9]+/g, "");
}
function getValueByAliases$1(source, keys) {
  for (const [entryKey, entryValue] of Object.entries(source)) {
    const normalizedEntryKey = normalizeLookupKey$1(entryKey);
    if (!normalizedEntryKey) {
      continue;
    }
    for (const key of keys) {
      if (normalizedEntryKey === normalizeLookupKey$1(key)) {
        return entryValue;
      }
    }
  }
  return void 0;
}
function readTextValue(sources, keys) {
  for (const source of sources) {
    for (const key of keys) {
      const value = normalizeTextValue(getValueByAliases$1(source, [key]));
      if (value) {
        return value;
      }
    }
  }
  return "";
}
function readTextValues(sources, keys) {
  const values = [];
  for (const source of sources) {
    for (const key of keys) {
      const resolvedValues = normalizeTextValues(getValueByAliases$1(source, [key]));
      if (resolvedValues.length > 0) {
        values.push(...resolvedValues);
      }
    }
  }
  return uniqueTextValues(values);
}
function readFilterValue(sources, keys) {
  for (const source of sources) {
    for (const key of keys) {
      const value = normalizeFilterValue(getValueByAliases$1(source, [key]));
      if (value !== null) {
        return value;
      }
    }
  }
  return null;
}
function readFilterValues(sources, keys) {
  const values = [];
  for (const source of sources) {
    for (const key of keys) {
      values.push(...normalizeFilterValues(getValueByAliases$1(source, [key])));
    }
  }
  return uniqueFilterValues(values);
}
function normalizeStaffAssignment(entry) {
  if (typeof entry === "string" || typeof entry === "number" || typeof entry === "boolean") {
    const label = normalizeTextValue(entry);
    if (!label) {
      return null;
    }
    return {
      staffId: null,
      staffName: label
    };
  }
  if (!isPlainObject$4(entry)) {
    return null;
  }
  const nestedStaff = isPlainObject$4(entry.staff) ? entry.staff : null;
  const sources = nestedStaff ? [nestedStaff, entry] : [entry];
  const staffId = readFilterValue(sources, [
    "staffId",
    "staffID",
    "staff_id",
    "staffIds",
    "staffMemberId",
    "staff_member_id",
    "employeeId",
    "employee_id",
    "id",
    "value"
  ]);
  const staffName = readTextValue(sources, [
    "staffName",
    "staff_name",
    "staffNames",
    "staff_names",
    "staffMemberName",
    "staff_member_name",
    "employeeName",
    "employee_name",
    "displayName",
    "display_name",
    "name",
    "label",
    "title"
  ]);
  if (isZeroLikeStaffId(staffId)) {
    return null;
  }
  if (!staffName && staffId === null) {
    return null;
  }
  return {
    staffId,
    staffName
  };
}
function normalizeStaffAssignments(value) {
  if (Array.isArray(value)) {
    return value.flatMap((entry) => normalizeStaffAssignments(entry));
  }
  const normalized = normalizeStaffAssignment(value);
  return normalized ? [normalized] : [];
}
function uniqueFilterValues(values) {
  const seen = /* @__PURE__ */ new Set();
  const result = [];
  values.forEach((value) => {
    const key = String(value);
    if (seen.has(key)) {
      return;
    }
    seen.add(key);
    result.push(value);
  });
  return result;
}
function uniqueTextValues(values) {
  const seen = /* @__PURE__ */ new Set();
  const result = [];
  values.forEach((value) => {
    const trimmed = value.trim();
    if (!trimmed) {
      return;
    }
    const key = trimmed.toLowerCase();
    if (seen.has(key)) {
      return;
    }
    seen.add(key);
    result.push(trimmed);
  });
  return result;
}
function buildStaffLabel(items) {
  return uniqueTextValues(
    items.map((item) => item.staffName || normalizeTextValue(item.staffId) || formatLabel(String(item.staffId ?? "")))
  ).join(", ");
}
function buildFallbackAssignments(source, metadata) {
  const sources = [];
  if (isPlainObject$4(source)) {
    sources.push(source);
  }
  if (isPlainObject$4(metadata)) {
    sources.push(metadata);
  }
  const staffIds = readFilterValues(sources, [
    "staffMemberId",
    "staff_member_id",
    "staffId",
    "staffID",
    "staff_id",
    "staffIds",
    "employeeId",
    "employee_id"
  ]);
  const staffNames = readTextValues(sources, [
    "staffMemberName",
    "staff_member_name",
    "staffName",
    "staff_name",
    "staffNames",
    "staff_names",
    "employeeName",
    "employee_name"
  ]);
  const maxLength = Math.max(staffIds.length, staffNames.length);
  if (maxLength > 0) {
    return Array.from({ length: maxLength }, (_, index) => ({
      staffId: staffIds[index] ?? null,
      staffName: staffNames[index] ?? ""
    })).filter((item) => !isZeroLikeStaffId(item.staffId) && (item.staffId !== null || !!item.staffName));
  }
  const staffId = readFilterValue(sources, [
    "staffMemberId",
    "staff_member_id",
    "staffId",
    "staffID",
    "staff_id",
    "employeeId",
    "employee_id"
  ]);
  const staffName = readTextValue(sources, [
    "staffMemberName",
    "staff_member_name",
    "staffName",
    "staff_name",
    "employeeName",
    "employee_name"
  ]);
  if (isZeroLikeStaffId(staffId)) {
    return [];
  }
  if (!staffName && staffId === null) {
    return [];
  }
  return [{
    staffId,
    staffName
  }];
}
function resolveBookingStaffSummary(source) {
  const sourceRecord = isPlainObject$4(source) ? source : null;
  const metadata = isPlainObject$4(source.metadata) ? source.metadata : null;
  const rawStaffData = source.StaffData ?? source.staffData ?? (sourceRecord ? getValueByAliases$1(sourceRecord, ["StaffData", "staffData", "staff_data"]) : void 0) ?? (metadata ? getValueByAliases$1(metadata, ["StaffData", "staffData", "staff_data"]) : void 0);
  const parsedItems = normalizeStaffAssignments(rawStaffData);
  const hasStaffData = rawStaffData !== void 0 && rawStaffData !== null;
  const isMultiStaff = normalizeBooleanLike$3(source.isMultiStaff) ?? normalizeBooleanLike$3(metadata == null ? void 0 : metadata.isMultiStaff) ?? hasStaffData;
  const fallbackItems = buildFallbackAssignments(source, metadata);
  const items = parsedItems.length > 0 ? parsedItems : hasStaffData ? [] : fallbackItems;
  const staffValues = uniqueFilterValues(items.flatMap((item) => normalizeFilterValues(item.staffId ?? item.staffName)));
  return {
    isMultiStaff: !!isMultiStaff || items.length > 1,
    items,
    staffValues,
    staffLabel: items.length > 0 ? buildStaffLabel(items) : hasStaffData ? "" : normalizeTextValue(source.staffMemberName) || normalizeTextValue(metadata == null ? void 0 : metadata.staffMemberName)
  };
}
function resolveBookingStaffLabel(source) {
  return resolveBookingStaffSummary(source).staffLabel;
}
const approvedIconSrc = "data:image/svg+xml,%3csvg%20width='14'%20height='14'%20viewBox='0%200%2014%2014'%20fill='none'%20xmlns='http://www.w3.org/2000/svg'%3e%3crect%20width='14'%20height='14'%20rx='7'%20fill='%2310B981'/%3e%3cpath%20d='M3.99951%207.05747L5.96013%209L9.99951%205'%20stroke='white'%20stroke-width='1.5'%20stroke-linecap='round'%20stroke-linejoin='round'/%3e%3c/svg%3e";
const pendingIconSrc = "data:image/svg+xml,%3csvg%20width='14'%20height='14'%20viewBox='0%200%2014%2014'%20fill='none'%20xmlns='http://www.w3.org/2000/svg'%3e%3crect%20width='14'%20height='14'%20rx='7'%20fill='%23FF9500'/%3e%3cpath%20d='M7%2010H7.0001M7%204V7.23077'%20stroke='white'%20stroke-width='1.5'%20stroke-linecap='round'%20stroke-linejoin='round'/%3e%3c/svg%3e";
const cancelledIconSrc = "data:image/svg+xml,%3csvg%20width='14'%20height='14'%20viewBox='0%200%2014%2014'%20fill='none'%20xmlns='http://www.w3.org/2000/svg'%3e%3crect%20width='14'%20height='14'%20rx='7'%20fill='%23FF4733'/%3e%3cpath%20d='M4.5%204.5L9.44467%209.50002'%20stroke='white'%20stroke-width='1.5'%20stroke-linecap='round'/%3e%3cpath%20d='M9.50001%204.5L4.55534%209.50002'%20stroke='white'%20stroke-width='1.5'%20stroke-linecap='round'/%3e%3c/svg%3e";
const rejectedIconSrc = "data:image/svg+xml,%3csvg%20width='14'%20height='14'%20viewBox='0%200%2014%2014'%20fill='none'%20xmlns='http://www.w3.org/2000/svg'%3e%3crect%20width='14'%20height='14'%20rx='7'%20fill='%23FF3377'/%3e%3cline%20x1='3.75'%20y1='7.04999'%20x2='10.25'%20y2='7.04999'%20stroke='white'%20stroke-width='1.5'%20stroke-linecap='round'/%3e%3c/svg%3e";
const noShowIconSrc = "data:image/svg+xml,%3csvg%20width='14'%20height='14'%20viewBox='0%200%2014%2014'%20fill='none'%20xmlns='http://www.w3.org/2000/svg'%3e%3crect%20width='14'%20height='14'%20rx='7'%20fill='%23247FE0'/%3e%3cpath%20d='M3.36453%208.37317C3.0104%207.91309%202.83333%207.68305%202.83333%206.99998C2.83333%206.3169%203.0104%206.08688%203.36453%205.6268C4.07164%204.70816%205.25752%203.66669%206.99995%203.66669C8.74239%203.66669%209.92825%204.70816%2010.6354%205.6268C10.9895%206.08688%2011.1666%206.3169%2011.1666%206.99998C11.1666%207.68305%2010.9895%207.91309%2010.6354%208.37317C9.92825%209.29179%208.74239%2010.3333%206.99995%2010.3333C5.25752%2010.3333%204.07164%209.29179%203.36453%208.37317Z'%20stroke='white'/%3e%3cpath%20d='M8.04166%207.00003C8.04166%207.57533%207.57531%208.04168%207%208.04168C6.4247%208.04168%205.95835%207.57533%205.95835%207.00003C5.95835%206.42472%206.4247%205.95837%207%205.95837C7.57531%205.95837%208.04166%206.42472%208.04166%207.00003Z'%20fill='white'%20stroke='white'/%3e%3c/svg%3e";
const completedIconSrc = "data:image/svg+xml,%3csvg%20width='14'%20height='14'%20viewBox='0%200%2014%2014'%20fill='none'%20xmlns='http://www.w3.org/2000/svg'%3e%3crect%20width='14'%20height='14'%20rx='7'%20fill='%2310B981'/%3e%3cpath%20d='M3.99951%207.05747L5.96013%209L9.99951%205'%20stroke='white'%20stroke-width='1.5'%20stroke-linecap='round'%20stroke-linejoin='round'/%3e%3c/svg%3e";
const waitingListIconSrc = "data:image/svg+xml,%3csvg%20width='16'%20height='16'%20viewBox='0%200%2016%2016'%20fill='none'%20xmlns='http://www.w3.org/2000/svg'%3e%3cpath%20d='M8%200C3.6%200%200%203.6%200%208C0%2012.4%203.6%2016%208%2016C12.4%2016%2016%2012.4%2016%208C16%203.6%2012.4%200%208%200ZM9.6%2010.96C9.2%2011.2%208.72%2011.04%208.48%2010.64L7.28%208.4C7.2%208.24%207.2%208.16%207.2%208V4C7.2%203.52%207.52%203.2%208%203.2C8.48%203.2%208.8%203.52%208.8%204V7.76L9.92%209.84C10.08%2010.24%2010%2010.72%209.6%2010.96Z'%20fill='%239F74F5'/%3e%3c/svg%3e";
const DEFAULT_STATUS_TONE = {
  background: "rgba(73, 90, 120, 0.4)",
  border: "rgba(158, 172, 201, 0.28)",
  fill: "#93A5C6"
};
const STATUS_DEFINITIONS = [
  {
    key: "approved",
    label: "Approved",
    value: 1,
    iconSrc: approvedIconSrc,
    tone: {
      background: "rgba(16, 185, 129, 0.18)",
      border: "rgba(16, 185, 129, 0.34)",
      fill: "#10B981"
    },
    aliases: ["confirmed"]
  },
  {
    key: "pending",
    label: "Pending",
    value: 2,
    iconSrc: pendingIconSrc,
    tone: {
      background: "rgba(255, 149, 0, 0.18)",
      border: "rgba(255, 149, 0, 0.34)",
      fill: "#FF9500"
    },
    aliases: []
  },
  {
    key: "cancelled",
    label: "Cancelled",
    value: 3,
    iconSrc: cancelledIconSrc,
    tone: {
      background: "rgba(255, 71, 51, 0.18)",
      border: "rgba(255, 71, 51, 0.34)",
      fill: "#FF4733"
    },
    aliases: ["canceled"]
  },
  {
    key: "rejected",
    label: "Rejected",
    value: 4,
    iconSrc: rejectedIconSrc,
    tone: {
      background: "rgba(255, 51, 119, 0.18)",
      border: "rgba(255, 51, 119, 0.34)",
      fill: "#FF3377"
    },
    aliases: []
  },
  {
    key: "no-show",
    label: "No-Show",
    value: 5,
    iconSrc: noShowIconSrc,
    tone: {
      background: "rgba(36, 127, 224, 0.18)",
      border: "rgba(36, 127, 224, 0.34)",
      fill: "#247FE0"
    },
    aliases: ["noshow", "no show"]
  },
  {
    key: "completed",
    label: "Completed",
    value: 6,
    iconSrc: completedIconSrc,
    tone: {
      background: "rgba(16, 185, 129, 0.18)",
      border: "rgba(16, 185, 129, 0.34)",
      fill: "#10B981"
    },
    aliases: []
  },
  {
    key: "waiting-list",
    label: "Waiting List",
    value: 7,
    iconSrc: waitingListIconSrc,
    tone: {
      background: "rgba(159, 116, 245, 0.18)",
      border: "rgba(159, 116, 245, 0.34)",
      fill: "#9F74F5"
    },
    aliases: []
  }
];
const STATUS_LOOKUP = /* @__PURE__ */ new Map();
STATUS_DEFINITIONS.forEach((definition) => {
  registerLookup(definition, definition.key);
  registerLookup(definition, definition.label);
  registerLookup(definition, definition.value);
  definition.aliases.forEach((alias) => registerLookup(definition, alias));
});
function registerLookup(definition, candidate) {
  const key = normalizeStatusLookupKey(candidate);
  if (key) {
    STATUS_LOOKUP.set(key, definition);
  }
}
function normalizeStatusLookupKey(value) {
  if (typeof value === "number") {
    return String(value);
  }
  if (typeof value !== "string") {
    return null;
  }
  const trimmedValue = value.trim().toLowerCase();
  if (!trimmedValue) {
    return null;
  }
  return trimmedValue.replace(/[_\s]+/g, "-");
}
function formatFallbackLabel(value) {
  return String(value).split("-").map((segment) => segment.charAt(0).toUpperCase() + segment.slice(1)).join(" ");
}
function getStatusOrderValue(status) {
  const definition = getStatusDefinition(status);
  if (!definition) {
    return Number.MAX_SAFE_INTEGER;
  }
  return STATUS_DEFINITIONS.findIndex((entry) => entry.key === definition.key);
}
function getStatusDefinition(status) {
  const key = normalizeStatusLookupKey(status);
  return key ? STATUS_LOOKUP.get(key) ?? null : null;
}
function getDefaultStatusOptions(labelOverrides = {}) {
  return STATUS_DEFINITIONS.map((definition) => ({
    label: labelOverrides[getStatusLabelOverrideKey(definition)] ?? definition.label,
    value: definition.value,
    iconSrc: definition.iconSrc
  }));
}
function getStatusLabelOverrideKey(definition) {
  return definition.key === "no-show" ? "noShow" : definition.key;
}
function enhanceStatusOption(option) {
  const trimmedLabel = option.label.trim();
  const definition = getStatusDefinition(option.value) ?? getStatusDefinition(trimmedLabel);
  return {
    ...option,
    label: trimmedLabel || (definition == null ? void 0 : definition.label) || formatFallbackLabel(option.value),
    iconSrc: option.iconSrc ?? (definition == null ? void 0 : definition.iconSrc)
  };
}
function sortStatusOptions(options) {
  return [...options].sort((left, right) => {
    const leftOrder = getStatusOrderValue(left.value);
    const rightOrder = getStatusOrderValue(right.value);
    if (leftOrder !== rightOrder) {
      return leftOrder - rightOrder;
    }
    return left.label.localeCompare(right.label, void 0, { numeric: true });
  });
}
const STATUSES = getDefaultStatusOptions().map((option) => option.value);
const RAW_JSON_CORE_FIELDS = /* @__PURE__ */ new Set([
  "id",
  "customerName",
  "isPast",
  "allowResize",
  "preventResize",
  "start_date",
  "end_date",
  "start_time",
  "end_time",
  "start_time_val",
  "end_time_val",
  "start",
  "end",
  "serviceName",
  "serviceId",
  "servicesData",
  "isMultiService",
  "totalMultiServices",
  "status",
  "statusLabel",
  "staffMemberName",
  "staffMemberId",
  "isMultiStaff",
  "totalMultiStaff",
  "StaffData",
  "staffData",
  "location",
  "price",
  "theme",
  "metadata"
]);
function randomItem(arr) {
  return arr[Math.floor(Math.random() * arr.length)];
}
function randomInt(min, max) {
  return Math.floor(Math.random() * (max - min + 1)) + min;
}
function parseDate(dateStr) {
  const [year, month, day] = dateStr.split("-").map(Number);
  return new Date(year, month - 1, day);
}
function parseTime(timeStr) {
  const cleaned = timeStr.trim();
  const match24 = cleaned.match(/^([01]?\d|2[0-3]):([0-5]\d)(?::[0-5]\d)?$/);
  if (match24) {
    return {
      hours: parseInt(match24[1], 10),
      minutes: parseInt(match24[2], 10)
    };
  }
  const match12 = cleaned.match(/^(\d{1,2}):(\d{2})(?::[0-5]\d)?\s*(AM|PM)$/i);
  if (!match12) throw new Error(`Invalid time format: "${timeStr}"`);
  let hours = parseInt(match12[1], 10);
  const minutes = parseInt(match12[2], 10);
  const period = match12[3].toUpperCase();
  if (period === "PM" && hours !== 12) hours += 12;
  if (period === "AM" && hours === 12) hours = 0;
  return { hours, minutes };
}
function parseExtendedTime(timeStr) {
  const cleaned = timeStr.trim();
  const match = cleaned.match(/^(\d{1,3}):([0-5]\d)(?::([0-5]\d))?$/);
  if (!match) {
    throw new Error(`Invalid extended time format: "${timeStr}"`);
  }
  return {
    hours: parseInt(match[1], 10),
    minutes: parseInt(match[2], 10),
    seconds: match[3] ? parseInt(match[3], 10) : 0
  };
}
function applyTimeToDate(baseDate, time) {
  const result = new Date(baseDate);
  result.setHours(time.hours, time.minutes, time.seconds ?? 0, 0);
  return result;
}
function normalizeOptionalString(value) {
  if (typeof value !== "string") {
    return void 0;
  }
  const trimmed = value.trim();
  return trimmed || void 0;
}
function formatDateOnly(date) {
  const year = date.getFullYear();
  const month = `${date.getMonth() + 1}`.padStart(2, "0");
  const day = `${date.getDate()}`.padStart(2, "0");
  return `${year}-${month}-${day}`;
}
function isPlainObject$3(value) {
  return typeof value === "object" && value !== null && !Array.isArray(value);
}
function extractExtraLoaderFields(entry) {
  return Object.fromEntries(
    Object.entries(entry).filter(([key]) => !RAW_JSON_CORE_FIELDS.has(key))
  );
}
function normalizeStatus(status) {
  if (typeof status === "string") {
    const trimmed = status.trim();
    if (trimmed) {
      return trimmed;
    }
  }
  if (typeof status === "number" || typeof status === "boolean") {
    return status;
  }
  return randomItem(STATUSES);
}
function normalizeFilterOptionValue(value) {
  if (Array.isArray(value)) {
    return value.map((v) => normalizeFilterOptionValue(v)).filter((v) => v !== void 0);
  }
  if (typeof value === "string") {
    const trimmed = value.trim();
    return trimmed ? trimmed : void 0;
  }
  if (typeof value === "number" || typeof value === "boolean") {
    return value;
  }
  return void 0;
}
function normalizeOptionLabel(value) {
  if (typeof value !== "string") {
    return void 0;
  }
  const trimmed = value.trim();
  return trimmed || void 0;
}
function normalizeBooleanLike$2(value) {
  if (typeof value === "boolean") {
    return value;
  }
  if (typeof value === "number") {
    if (value === 1) return true;
    if (value === 0) return false;
    return void 0;
  }
  if (typeof value === "string") {
    const normalized = value.trim().toLowerCase();
    if (normalized === "true" || normalized === "1") return true;
    if (normalized === "false" || normalized === "0") return false;
  }
  return void 0;
}
function normalizePositiveInteger(value) {
  if (typeof value === "number" && Number.isFinite(value)) {
    const count = Math.trunc(value);
    return count > 1 ? count : void 0;
  }
  if (typeof value === "string") {
    const parsed = Number(value.trim());
    if (Number.isFinite(parsed)) {
      const count = Math.trunc(parsed);
      return count > 1 ? count : void 0;
    }
  }
  return void 0;
}
function resolveMultiServiceTotal(entry, nestedMetadata, serviceSummary) {
  const explicitCount = normalizePositiveInteger(entry.totalMultiServices) ?? normalizePositiveInteger(nestedMetadata.totalMultiServices) ?? normalizePositiveInteger(nestedMetadata.total_multi_services);
  if (explicitCount !== void 0) {
    return explicitCount;
  }
  const derivedCount = serviceSummary.items.length > 1 ? serviceSummary.items.length : serviceSummary.serviceValues.length > 1 ? serviceSummary.serviceValues.length : void 0;
  return serviceSummary.isMultiService ? derivedCount : void 0;
}
function resolveMultiStaffTotal(entry, nestedMetadata, staffSummary) {
  const explicitCount = normalizePositiveInteger(entry.totalMultiStaff) ?? normalizePositiveInteger(nestedMetadata.totalMultiStaff) ?? normalizePositiveInteger(nestedMetadata.total_multi_staff);
  if (explicitCount !== void 0) {
    return explicitCount;
  }
  const derivedCount = staffSummary.items.length > 1 ? staffSummary.items.length : staffSummary.staffValues.length > 1 ? staffSummary.staffValues.length : void 0;
  return staffSummary.isMultiStaff ? derivedCount : void 0;
}
function normalizeDateInput(value) {
  if (!(value instanceof Date) && typeof value !== "string" && typeof value !== "number") {
    return void 0;
  }
  return parseDateInput(value) ?? void 0;
}
function resolveLocationId(entry, nestedMetadata) {
  return normalizeFilterOptionValue(entry.locationId) ?? normalizeFilterOptionValue(entry.loationId) ?? normalizeFilterOptionValue(nestedMetadata.locationId) ?? normalizeFilterOptionValue(nestedMetadata.loationId);
}
function cloneStaffAssignments(items) {
  return items.map((item) => ({
    staffId: item.staffId,
    staffName: item.staffName
  }));
}
function buildBookingMetadata(entry, status, statusDefinition) {
  const extraLoaderFields = extractExtraLoaderFields(entry);
  const nestedMetadata = isPlainObject$3(entry.metadata) ? entry.metadata : {};
  const isPast = normalizeBooleanLike$2(entry.isPast) ?? normalizeBooleanLike$2(nestedMetadata.isPast);
  const allowResize = normalizeBooleanLike$2(entry.allowResize) ?? normalizeBooleanLike$2(nestedMetadata.allowResize);
  const preventResize = normalizeBooleanLike$2(entry.preventResize) ?? normalizeBooleanLike$2(nestedMetadata.preventResize);
  const isDayService = normalizeBooleanLike$2(entry.isDayService) ?? normalizeBooleanLike$2(nestedMetadata.isDayService);
  const customerId = normalizeFilterOptionValue(entry.customerId ?? nestedMetadata.customerId);
  const serviceSummary = resolveBookingServiceSummary({
    ...entry,
    metadata: nestedMetadata
  });
  const staffSummary = resolveBookingStaffSummary({
    ...entry,
    metadata: nestedMetadata
  });
  const serviceId = serviceSummary.isMultiService ? serviceSummary.items.length === 1 ? serviceSummary.serviceValues[0] : void 0 : serviceSummary.items.length === 1 ? serviceSummary.serviceValues[0] : normalizeFilterOptionValue(entry.serviceId);
  const staffData = cloneStaffAssignments(staffSummary.items);
  const staffMemberId = staffSummary.items.length === 1 ? staffSummary.staffValues[0] : void 0;
  const locationId = resolveLocationId(entry, nestedMetadata);
  const totalMultiServices = resolveMultiServiceTotal(entry, nestedMetadata, serviceSummary);
  const totalMultiStaff = resolveMultiStaffTotal(entry, nestedMetadata, staffSummary);
  return {
    ...nestedMetadata,
    ...extraLoaderFields,
    ...isPast !== void 0 ? { isPast } : {},
    ...allowResize !== void 0 ? { allowResize } : {},
    ...preventResize !== void 0 ? { preventResize } : {},
    ...isDayService !== void 0 ? { isDayService } : {},
    ...customerId !== void 0 ? { customerId } : {},
    ...serviceSummary.isMultiService ? { isMultiService: true } : {},
    ...totalMultiServices !== void 0 ? { totalMultiServices } : {},
    serviceId,
    serviceName: serviceSummary.isMultiService ? serviceSummary.serviceLabel : serviceSummary.serviceLabel || entry.serviceName,
    ...entry.servicesData !== void 0 ? { servicesData: entry.servicesData } : nestedMetadata.servicesData !== void 0 ? { servicesData: nestedMetadata.servicesData } : {},
    status,
    statusLabel: normalizeOptionLabel(entry.statusLabel) ?? (statusDefinition == null ? void 0 : statusDefinition.label),
    staffMemberName: staffSummary.staffLabel,
    ...staffMemberId !== void 0 ? { staffMemberId } : {},
    ...staffData.length > 0 ? { StaffData: staffData } : {},
    ...staffSummary.isMultiStaff ? { isMultiStaff: true } : {},
    ...totalMultiStaff !== void 0 ? { totalMultiStaff } : {},
    location: entry.location || "",
    ...locationId !== void 0 ? { locationId, loationId: locationId } : {},
    price: entry.price ? typeof entry.price === "number" ? `$${entry.price}.00` : entry.price : ""
  };
}
function resolveEntryDateRange(entry) {
  const startDateValue = normalizeOptionalString(entry.start_date);
  const endDateValue = normalizeOptionalString(entry.end_date);
  if (startDateValue) {
    const startDate2 = parseDate(startDateValue);
    const endDate2 = parseDate(endDateValue ?? startDateValue);
    return {
      startDate: startDate2,
      endDate: endDate2.getTime() >= startDate2.getTime() ? endDate2 : startDate2
    };
  }
  const startDate = normalizeDateInput(entry.start) ?? /* @__PURE__ */ new Date();
  const endDate = normalizeDateInput(entry.end) ?? startDate;
  return {
    startDate,
    endDate: endDate.getTime() >= startDate.getTime() ? endDate : startDate
  };
}
function buildAllDayTitle(entry) {
  const customerName = normalizeOptionalString(entry.customerName);
  const serviceName = normalizeOptionalString(resolveBookingServiceLabel({
    ...entry,
    metadata: isPlainObject$3(entry.metadata) ? entry.metadata : null
  }));
  if (customerName && serviceName && customerName !== serviceName) {
    return `${customerName} | ${serviceName}`;
  }
  return customerName ?? serviceName ?? `Booking #${entry.id}`;
}
function isDayServiceEntry(entry) {
  const nestedMetadata = isPlainObject$3(entry.metadata) ? entry.metadata : {};
  return normalizeBooleanLike$2(entry.isDayService) ?? normalizeBooleanLike$2(nestedMetadata.isDayService) ?? false;
}
function toAllDayBooking(entry) {
  const { startDate, endDate } = resolveEntryDateRange(entry);
  const status = normalizeStatus(entry.status);
  const statusDefinition = getStatusDefinition(status) ?? getStatusDefinition(entry.statusLabel);
  const customerName = normalizeOptionalString(entry.customerName);
  const nestedMetadata = isPlainObject$3(entry.metadata) ? entry.metadata : {};
  const serviceSummary = resolveBookingServiceSummary({
    ...entry,
    metadata: nestedMetadata
  });
  const staffSummary = resolveBookingStaffSummary({
    ...entry,
    metadata: nestedMetadata
  });
  const serviceName = normalizeOptionalString(
    serviceSummary.isMultiService ? serviceSummary.serviceLabel : serviceSummary.serviceLabel || entry.serviceName
  );
  const serviceId = serviceSummary.isMultiService ? serviceSummary.items.length === 1 ? serviceSummary.serviceValues[0] : void 0 : serviceSummary.items.length === 1 ? serviceSummary.serviceValues[0] : normalizeFilterOptionValue(entry.serviceId);
  const metadata = buildBookingMetadata(entry, status, statusDefinition);
  const isPast = typeof metadata.isPast === "boolean" ? metadata.isPast : void 0;
  const totalMultiServices = resolveMultiServiceTotal(entry, nestedMetadata, serviceSummary);
  const totalMultiStaff = resolveMultiStaffTotal(entry, nestedMetadata, staffSummary);
  return {
    id: String(entry.id),
    startDate,
    endDate,
    ...isPast !== void 0 ? { isPast } : {},
    title: buildAllDayTitle(entry),
    ...customerName ? { customerName } : {},
    ...serviceName ? { serviceName } : {},
    ...serviceId !== void 0 ? { serviceId } : {},
    ...serviceSummary.isMultiService ? { isMultiService: true } : {},
    ...totalMultiServices !== void 0 ? { totalMultiServices } : {},
    ...staffSummary.staffLabel ? { staffMemberName: staffSummary.staffLabel } : {},
    ...staffSummary.items.length === 1 ? { staffMemberId: staffSummary.staffValues[0] } : {},
    ...staffSummary.items.length > 0 ? { StaffData: cloneStaffAssignments(staffSummary.items) } : {},
    ...staffSummary.isMultiStaff ? { isMultiStaff: true } : {},
    ...totalMultiStaff !== void 0 ? { totalMultiStaff } : {},
    status,
    metadata,
    theme: entry.theme
  };
}
function normalizeRawEntries(rawEntries) {
  const timeBookings = [];
  const allDayBookings = [];
  rawEntries.forEach((entry) => {
    if (isDayServiceEntry(entry)) {
      allDayBookings.push(toAllDayBooking(entry));
      return;
    }
    timeBookings.push(toTimeBooking(entry));
  });
  return { timeBookings, allDayBookings };
}
function toTimeBooking(entry) {
  let start;
  let end;
  let normalizedStartDateValue;
  let normalizedEndDateValue;
  const startDateValue = normalizeOptionalString(entry.start_date);
  const endDateValue = normalizeOptionalString(entry.end_date);
  const startTimeValue = normalizeOptionalString(entry.start_time);
  const endTimeValue = normalizeOptionalString(entry.end_time);
  const startTimeValValue = normalizeOptionalString(entry.start_time_val);
  const endTimeValValue = normalizeOptionalString(entry.end_time_val);
  const combinedStart = normalizeDateInput(entry.start);
  const combinedEnd = normalizeDateInput(entry.end);
  if (combinedStart && combinedEnd) {
    start = combinedStart;
    end = combinedEnd;
    normalizedStartDateValue = formatDateOnly(start);
    normalizedEndDateValue = formatDateOnly(end);
  } else if (startDateValue && (startTimeValue || startTimeValValue) && (endTimeValue || endTimeValValue)) {
    const startDay = parseDate(startDateValue);
    const endDay = parseDate(endDateValue ?? startDateValue);
    if (startTimeValValue || endTimeValValue) {
      const startParsed = startTimeValValue ? parseExtendedTime(startTimeValValue) : parseTime(startTimeValue ?? "00:00");
      const endParsed = endTimeValValue ? parseExtendedTime(endTimeValValue) : parseTime(endTimeValue ?? startTimeValue ?? "00:00");
      start = applyTimeToDate(startDay, startParsed);
      end = applyTimeToDate(startDay, endParsed);
    } else {
      const startParsed = parseTime(startTimeValue ?? "00:00");
      const endParsed = parseTime(endTimeValue ?? startTimeValue ?? "00:00");
      start = new Date(startDay);
      start.setHours(startParsed.hours, startParsed.minutes, 0, 0);
      end = new Date(endDay);
      end.setHours(endParsed.hours, endParsed.minutes, 0, 0);
      if (formatDateOnly(startDay) === formatDateOnly(endDay) && end.getTime() < start.getTime()) {
        end.setDate(end.getDate() + 1);
      }
    }
    normalizedStartDateValue = formatDateOnly(start);
    normalizedEndDateValue = formatDateOnly(end);
  } else {
    start = /* @__PURE__ */ new Date();
    end = new Date(Date.now() + 36e5);
    normalizedStartDateValue = formatDateOnly(start);
    normalizedEndDateValue = formatDateOnly(end);
  }
  const status = normalizeStatus(entry.status);
  const statusDefinition = getStatusDefinition(status) ?? getStatusDefinition(entry.statusLabel);
  const startDate = normalizedStartDateValue ?? startDateValue ?? formatDateOnly(start);
  const endDate = normalizedEndDateValue ?? endDateValue ?? startDateValue ?? formatDateOnly(end);
  const startTime = startTimeValue ?? formatTimeForDataEntry(start);
  const endTime = endTimeValue ?? formatTimeForDataEntry(end);
  const startTimeVal = startTimeValValue ?? formatTimeForExtendedDataEntry(start, start);
  const endTimeVal = endTimeValValue ?? formatTimeForExtendedDataEntry(end, start);
  const nestedMetadata = isPlainObject$3(entry.metadata) ? entry.metadata : {};
  const serviceSummary = resolveBookingServiceSummary({
    ...entry,
    metadata: nestedMetadata
  });
  const staffSummary = resolveBookingStaffSummary({
    ...entry,
    metadata: nestedMetadata
  });
  const allowResize = normalizeBooleanLike$2(entry.allowResize) ?? normalizeBooleanLike$2(nestedMetadata.allowResize);
  const preventResize = normalizeBooleanLike$2(entry.preventResize) ?? normalizeBooleanLike$2(nestedMetadata.preventResize);
  const isPast = normalizeBooleanLike$2(entry.isPast) ?? normalizeBooleanLike$2(nestedMetadata.isPast);
  const serviceId = serviceSummary.isMultiService ? serviceSummary.items.length === 1 ? serviceSummary.serviceValues[0] : void 0 : serviceSummary.items.length === 1 ? serviceSummary.serviceValues[0] : normalizeFilterOptionValue(entry.serviceId);
  const totalMultiServices = resolveMultiServiceTotal(entry, nestedMetadata, serviceSummary);
  const totalMultiStaff = resolveMultiStaffTotal(entry, nestedMetadata, staffSummary);
  return {
    id: String(entry.id),
    start,
    end,
    isPast,
    start_date: startDate,
    end_date: endDate,
    start_time: startTime,
    end_time: endTime,
    start_time_val: startTimeVal,
    end_time_val: endTimeVal,
    resourceId: `resource-${randomInt(1, 5)}`,
    // In a real API, this would come from the entry
    serviceId,
    serviceName: serviceSummary.isMultiService ? serviceSummary.serviceLabel : serviceSummary.serviceLabel || entry.serviceName,
    ...serviceSummary.isMultiService ? { isMultiService: true } : {},
    ...totalMultiServices !== void 0 ? { totalMultiServices } : {},
    ...staffSummary.staffLabel ? { staffMemberName: staffSummary.staffLabel } : {},
    ...staffSummary.items.length === 1 ? { staffMemberId: staffSummary.staffValues[0] } : {},
    ...staffSummary.items.length > 0 ? { StaffData: cloneStaffAssignments(staffSummary.items) } : {},
    ...staffSummary.isMultiStaff ? { isMultiStaff: true } : {},
    ...totalMultiStaff !== void 0 ? { totalMultiStaff } : {},
    customerName: entry.customerName,
    status,
    ...allowResize !== void 0 ? { allowResize } : {},
    ...preventResize !== void 0 ? { preventResize } : {},
    metadata: buildBookingMetadata(entry, status, statusDefinition),
    theme: entry.theme
  };
}
function normalizeBooleanLike$1(value) {
  if (typeof value === "boolean") {
    return value;
  }
  if (typeof value === "number") {
    if (value === 1) return true;
    if (value === 0) return false;
    return void 0;
  }
  if (typeof value === "string") {
    const normalized = value.trim().toLowerCase();
    if (normalized === "true" || normalized === "1") return true;
    if (normalized === "false" || normalized === "0") return false;
  }
  return void 0;
}
function resolveBookingIsPast(booking) {
  var _a;
  if (typeof booking.isPast === "boolean") {
    return booking.isPast;
  }
  const metadataIsPast = (_a = booking.metadata) == null ? void 0 : _a.isPast;
  if (typeof metadataIsPast === "boolean") {
    return metadataIsPast;
  }
  const start = booking.isPreview ? booking.start : booking.originalStart ?? booking.start;
  if (start instanceof Date && !Number.isNaN(start.getTime())) {
    return start.getTime() < Date.now();
  }
  return false;
}
function resolveTimeBookingIsPast(booking) {
  return resolveBookingIsPast(booking);
}
function resolveTimeBookingResizeEnabled(booking) {
  var _a, _b;
  const allowResize = normalizeBooleanLike$1(booking.allowResize) ?? normalizeBooleanLike$1((_a = booking.metadata) == null ? void 0 : _a.allowResize);
  if (allowResize !== void 0) {
    return allowResize;
  }
  const preventResize = normalizeBooleanLike$1(booking.preventResize) ?? normalizeBooleanLike$1((_b = booking.metadata) == null ? void 0 : _b.preventResize);
  if (preventResize !== void 0) {
    return !preventResize;
  }
  return true;
}
const COOKIE_NAME = "bpa_calendar_filter_settings";
const COOKIE_MAX_AGE_YEARS = 1;
function setCookie(name, value, days) {
  const expires = /* @__PURE__ */ new Date();
  expires.setTime(expires.getTime() + days * 24 * 60 * 60 * 1e3);
  document.cookie = `${name}=${value};expires=${expires.toUTCString()};path=/;SameSite=Lax`;
}
function getCookie(name) {
  const nameEQ = name + "=";
  const ca = document.cookie.split(";");
  for (let i = 0; i < ca.length; i++) {
    let c = ca[i];
    while (c.charAt(0) === " ") c = c.substring(1, c.length);
    if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
  }
  return null;
}
function saveDisplaySettings(settings) {
  setCookie(COOKIE_NAME, JSON.stringify(settings), COOKIE_MAX_AGE_YEARS * 365);
}
const PRIMARY_SECTION = "primary";
const EXTRA_SECTION = "extra";
function buildDisplaySettingsRegistry(labelOverrides = {}) {
  return [
    {
      id: "customerName",
      visible: true,
      label: labelOverrides.customerName ?? "Customer Name",
      section: PRIMARY_SECTION
    },
    {
      id: "dateTime",
      visible: true,
      label: labelOverrides.dateTime ?? "Time Duration",
      section: PRIMARY_SECTION
    },
    {
      id: "serviceName",
      visible: true,
      label: labelOverrides.serviceName ?? "Service Name",
      section: PRIMARY_SECTION
    },
    {
      id: "price",
      visible: true,
      label: labelOverrides.price ?? "Price",
      section: PRIMARY_SECTION
    }
  ];
}
const extraDisplaySettings = [];
function isPlainObject$2(value) {
  return typeof value === "object" && value !== null && !Array.isArray(value);
}
function normalizeDisplaySetting(setting, defaultSection = PRIMARY_SECTION) {
  if (typeof setting === "string" || typeof setting === "number") {
    const normalizedId2 = String(setting).trim();
    if (!normalizedId2) {
      return null;
    }
    return {
      id: normalizedId2,
      visible: true,
      section: defaultSection
    };
  }
  if (!isPlainObject$2(setting)) {
    return null;
  }
  const { id, visible, label, section } = setting;
  const normalizedId = typeof id === "string" || typeof id === "number" ? String(id).trim() : "";
  if (!normalizedId) {
    return null;
  }
  return {
    id: normalizedId,
    visible: typeof visible === "boolean" ? visible : true,
    label: typeof label === "string" && label.trim() ? label.trim() : void 0,
    section: section === EXTRA_SECTION ? EXTRA_SECTION : defaultSection
  };
}
function normalizeDisplaySettings(settings, defaultSection = PRIMARY_SECTION) {
  if (!Array.isArray(settings)) {
    return [];
  }
  return settings.map((setting) => normalizeDisplaySetting(setting, defaultSection)).filter((setting) => setting !== null);
}
function normalizeDisplaySettingsConfig(settings) {
  if (Array.isArray(settings)) {
    return {
      fields: normalizeDisplaySettings(settings, PRIMARY_SECTION),
      extraDisplayFields: []
    };
  }
  if (!isPlainObject$2(settings)) {
    return {
      fields: [],
      extraDisplayFields: []
    };
  }
  if (!("fields" in settings) && !("extraDisplayFields" in settings)) {
    const fields = Object.entries(settings).map(([id, value]) => {
      if (typeof value === "boolean") {
        return {
          id,
          visible: value
        };
      }
      if (typeof value === "string") {
        return {
          id,
          visible: true,
          label: value.trim() || void 0
        };
      }
      if (isPlainObject$2(value)) {
        return {
          id,
          ...value
        };
      }
      if (typeof value === "number") {
        return {
          id,
          visible: Boolean(value)
        };
      }
      return id;
    });
    return {
      fields: normalizeDisplaySettings(fields, PRIMARY_SECTION),
      extraDisplayFields: []
    };
  }
  return {
    fields: normalizeDisplaySettings(settings.fields, PRIMARY_SECTION),
    extraDisplayFields: normalizeDisplaySettings(settings.extraDisplayFields, EXTRA_SECTION)
  };
}
function mergeDisplaySettingsRegistry(...groups) {
  const merged = [];
  const seen = /* @__PURE__ */ new Map();
  groups.forEach((group) => {
    group.forEach((setting) => {
      const existingIndex = seen.get(setting.id);
      if (existingIndex !== void 0) {
        merged[existingIndex] = {
          ...merged[existingIndex],
          ...setting,
          id: merged[existingIndex].id
        };
        return;
      }
      seen.set(setting.id, merged.length);
      merged.push({ ...setting });
    });
  });
  return merged;
}
function getDefaultDisplaySettings(additionalSettings = [], labelOverrides = {}) {
  const normalizedConfig = normalizeDisplaySettingsConfig(additionalSettings);
  return mergeDisplaySettingsRegistry(
    buildDisplaySettingsRegistry(labelOverrides),
    extraDisplaySettings,
    normalizedConfig.fields ?? [],
    normalizedConfig.extraDisplayFields ?? []
  );
}
function getDisplaySettings(additionalSettings = [], labelOverrides = {}) {
  const defaults = getDefaultDisplaySettings(additionalSettings, labelOverrides);
  const saved = getCookie(COOKIE_NAME);
  if (!saved) {
    return defaults;
  }
  try {
    const parsed = JSON.parse(saved);
    const defaultsById = new Map(defaults.map((setting) => [setting.id, setting]));
    const seen = /* @__PURE__ */ new Set();
    const orderedSaved = parsed.map((item) => {
      const defaultSetting = defaultsById.get(item.id);
      if (!defaultSetting || seen.has(item.id)) {
        return null;
      }
      seen.add(item.id);
      return {
        ...defaultSetting,
        visible: typeof item.visible === "boolean" ? item.visible : defaultSetting.visible
      };
    }).filter((item) => item !== null);
    const missingDefaults = defaults.filter((setting) => !seen.has(setting.id));
    return [...orderedSaved, ...missingDefaults];
  } catch (e) {
    console.error("Failed to parse display settings cookie", e);
    return defaults;
  }
}
function formatDisplayFieldLabel(field) {
  if (typeof field.label === "string" && field.label.trim()) {
    return field.label.trim();
  }
  return field.id.replace(/[_-]+/g, " ").replace(/([a-z])([A-Z])/g, "$1 $2").trim().replace(/\s+/g, " ").replace(/\b\w/g, (value) => value.toUpperCase());
}
function normalizeFieldId(fieldId) {
  return fieldId.trim().toLowerCase().replace(/[^a-z0-9]+/g, "");
}
function resolveDisplayFieldIconKind(fieldId) {
  switch (normalizeFieldId(fieldId)) {
    case "customername":
      return "customer";
    case "datetime":
      return "time";
    case "servicename":
    case "serviceid":
    case "serviceids":
    case "servicelabel":
    case "servicesdata":
      return "service";
    case "staffmembername":
      return "staff";
    case "location":
      return "location";
    case "price":
      return "price";
    default:
      return "custom";
  }
}
function normalizeDisplayFieldValue(value) {
  if (typeof value === "string") {
    return value.trim();
  }
  if (typeof value === "number" || typeof value === "boolean") {
    return String(value);
  }
  if (Array.isArray(value)) {
    return value.map((entry) => normalizeDisplayFieldValue(entry)).filter(Boolean).join(", ");
  }
  return "";
}
function isPlainObject$1(value) {
  return typeof value === "object" && value !== null && !Array.isArray(value);
}
function resolveServiceDisplayValue(booking) {
  var _a, _b;
  return resolveBookingServiceLabel({
    serviceName: booking.serviceName,
    serviceId: booking.serviceId,
    servicesData: ((_a = booking.metadata) == null ? void 0 : _a.servicesData) ?? ((_b = booking.metadata) == null ? void 0 : _b.services_data),
    metadata: booking.metadata ?? null
  });
}
function resolveFormFieldDisplayValue(metadata, fieldId) {
  const formFieldEntries = metadata == null ? void 0 : metadata.form_fields;
  if (!Array.isArray(formFieldEntries)) {
    return "";
  }
  const values = formFieldEntries.filter((entry) => isPlainObject$1(entry)).filter((entry) => {
    const entryId = entry.id;
    return typeof entryId === "string" || typeof entryId === "number" ? String(entryId).trim() === fieldId : false;
  }).map((entry) => normalizeDisplayFieldValue(entry.value)).filter(Boolean);
  return values.join(", ");
}
function resolveTimeBookingDisplayFieldValue(booking, fieldId) {
  var _a, _b, _c;
  switch (normalizeFieldId(fieldId)) {
    case "customername":
      return normalizeDisplayFieldValue(booking.customerName);
    case "datetime": {
      const start = booking.isPreview ? booking.start : booking.originalStart ?? booking.start;
      const end = booking.isPreview ? booking.end : booking.originalEnd ?? booking.end;
      return formatTimeRange(start, end);
    }
    case "servicename":
    case "serviceid":
    case "serviceids":
    case "servicelabel":
    case "servicesdata":
      return resolveServiceDisplayValue(booking);
    case "staffmembername":
      return resolveBookingStaffLabel({
        staffMemberName: booking.staffMemberName,
        staffMemberId: booking.staffMemberId,
        StaffData: booking.StaffData,
        isMultiStaff: booking.isMultiStaff,
        metadata: booking.metadata ?? null
      });
    case "location":
      return normalizeDisplayFieldValue((_a = booking.metadata) == null ? void 0 : _a.location) || "—";
    case "price":
      return normalizeDisplayFieldValue((_b = booking.metadata) == null ? void 0 : _b.price);
  }
  const directValue = normalizeDisplayFieldValue(booking[fieldId]);
  if (directValue) {
    return directValue;
  }
  const formFieldValue = resolveFormFieldDisplayValue(booking.metadata, fieldId);
  if (formFieldValue) {
    return formFieldValue;
  }
  return normalizeDisplayFieldValue((_c = booking.metadata) == null ? void 0 : _c[fieldId]);
}
function isPlainObject(value) {
  return typeof value === "object" && value !== null && !Array.isArray(value);
}
function normalizeLookupKey(value) {
  return value.trim().toLowerCase().replace(/[^a-z0-9]+/g, "");
}
function getValueByAliases(source, keys) {
  if (!source) {
    return void 0;
  }
  for (const [entryKey, entryValue] of Object.entries(source)) {
    const normalizedEntryKey = normalizeLookupKey(entryKey);
    if (!normalizedEntryKey) {
      continue;
    }
    if (keys.some((key) => normalizedEntryKey === normalizeLookupKey(key))) {
      return entryValue;
    }
  }
  return void 0;
}
function normalizeBooleanLike(value) {
  if (typeof value === "boolean") {
    return value;
  }
  if (typeof value === "number") {
    if (value === 1) return true;
    if (value === 0) return false;
  }
  if (typeof value === "string") {
    const normalized = value.trim().toLowerCase();
    if (normalized === "true" || normalized === "1") return true;
    if (normalized === "false" || normalized === "0") return false;
  }
  return void 0;
}
function normalizePositiveCount(value) {
  if (typeof value === "number" && Number.isFinite(value)) {
    const count = Math.trunc(value);
    return count > 1 ? count : null;
  }
  if (typeof value === "string") {
    const parsed = Number(value.trim());
    if (Number.isFinite(parsed)) {
      const count = Math.trunc(parsed);
      return count > 1 ? count : null;
    }
  }
  return null;
}
function getExplicitCount(booking, aliases) {
  const bookingRecord = booking;
  const metadata = isPlainObject(booking.metadata) ? booking.metadata : null;
  return normalizePositiveCount(getValueByAliases(bookingRecord, aliases)) ?? normalizePositiveCount(getValueByAliases(metadata, aliases));
}
function getExplicitBoolean(booking, aliases) {
  const bookingRecord = booking;
  const metadata = isPlainObject(booking.metadata) ? booking.metadata : null;
  return normalizeBooleanLike(getValueByAliases(bookingRecord, aliases)) ?? normalizeBooleanLike(getValueByAliases(metadata, aliases));
}
function getMultiStaffCount(booking) {
  const explicitMultiStaff = getExplicitBoolean(booking, ["isMultiStaff", "is_multi_staff"]);
  const explicitCount = getExplicitCount(booking, ["totalMultiStaff", "total_multi_staff"]);
  if (explicitCount && explicitMultiStaff !== false) {
    return explicitCount;
  }
  const staffSummary = resolveBookingStaffSummary(booking);
  const derivedCount = staffSummary.items.length > 1 ? staffSummary.items.length : staffSummary.staffValues.length > 1 ? staffSummary.staffValues.length : null;
  return (explicitMultiStaff ?? staffSummary.isMultiStaff) && derivedCount ? derivedCount : null;
}
function getMultiServiceCount(booking) {
  const explicitMultiService = getExplicitBoolean(booking, ["isMultiService", "is_multi_service"]);
  const explicitCount = getExplicitCount(booking, ["totalMultiServices", "total_multi_services"]);
  if (explicitCount && explicitMultiService !== false) {
    return explicitCount;
  }
  const serviceSummary = resolveBookingServiceSummary(booking);
  const derivedCount = serviceSummary.items.length > 1 ? serviceSummary.items.length : serviceSummary.serviceValues.length > 1 ? serviceSummary.serviceValues.length : null;
  return (explicitMultiService ?? serviceSummary.isMultiService) && derivedCount ? derivedCount : null;
}
function getBookingMultiIndicators(booking) {
  const staffCount = getMultiStaffCount(booking);
  const serviceCount = getMultiServiceCount(booking);
  const indicators = [];
  if (staffCount) {
    indicators.push({ kind: "staff", count: staffCount });
  }
  if (serviceCount) {
    indicators.push({ kind: "service", count: serviceCount });
  }
  return indicators;
}
const staffIconUrl = "data:image/svg+xml,%3csvg%20width='10'%20height='10'%20viewBox='0%200%2010%2010'%20fill='none'%20xmlns='http://www.w3.org/2000/svg'%3e%3cpath%20d='M8.33345%209.28564V8.33329C8.33345%207.82813%208.13278%207.34366%207.77558%206.98646C7.41838%206.62926%206.93391%206.42859%206.42875%206.42859H3.5717C3.06654%206.42859%202.58207%206.62926%202.22487%206.98646C1.86767%207.34366%201.66699%207.82813%201.66699%208.33329V9.28564'%20stroke='black'%20stroke-opacity='0.75'%20stroke-width='1.1'%20stroke-linecap='round'%20stroke-linejoin='round'/%3e%3cpath%20d='M4.99943%204.52376C6.05137%204.52376%206.90413%203.671%206.90413%202.61906C6.90413%201.56712%206.05137%200.714355%204.99943%200.714355C3.94749%200.714355%203.09473%201.56712%203.09473%202.61906C3.09473%203.671%203.94749%204.52376%204.99943%204.52376Z'%20stroke='black'%20stroke-opacity='0.75'%20stroke-width='1.1'%20stroke-linecap='round'%20stroke-linejoin='round'/%3e%3c/svg%3e";
const multiServiceIconUrl = "data:image/svg+xml,%3csvg%20width='14'%20height='14'%20viewBox='0%200%2014%2014'%20fill='none'%20xmlns='http://www.w3.org/2000/svg'%3e%3cpath%20d='M2.21335%207.01698C2.5267%205.45025%202.68337%204.66688%203.20106%204.16233C3.29674%204.06908%203.40025%203.98423%203.51046%203.90869C4.10673%203.5%204.90562%203.5%206.50339%203.5H7.49675C9.09456%203.5%209.89343%203.5%2010.4897%203.90869C10.5999%203.98423%2010.7034%204.06908%2010.7991%204.16233C11.3168%204.66688%2011.4734%205.45025%2011.7868%207.01698C12.2367%209.26631%2012.4616%2010.391%2011.9438%2011.1879C11.85%2011.3322%2011.7406%2011.4657%2011.6175%2011.5859C10.9376%2012.25%209.79065%2012.25%207.49675%2012.25H6.50339C4.2095%2012.25%203.06256%2012.25%202.38265%2011.5859C2.25953%2011.4657%202.1501%2011.3322%202.05635%2011.1879C1.53855%2010.391%201.76348%209.26631%202.21335%207.01698Z'%20stroke='black'%20stroke-opacity='0.75'/%3e%3cpath%20d='M8.74984%205.83329C9.072%205.83329%209.33317%205.57213%209.33317%205.24996C9.33317%204.92779%209.072%204.66663%208.74984%204.66663C8.42767%204.66663%208.1665%204.92779%208.1665%205.24996C8.1665%205.57213%208.42767%205.83329%208.74984%205.83329Z'%20fill='black'%20fill-opacity='0.75'/%3e%3cpath%20d='M5.24984%205.83329C5.572%205.83329%205.83317%205.57213%205.83317%205.24996C5.83317%204.92779%205.572%204.66663%205.24984%204.66663C4.92767%204.66663%204.6665%204.92779%204.6665%205.24996C4.6665%205.57213%204.92767%205.83329%205.24984%205.83329Z'%20fill='black'%20fill-opacity='0.75'/%3e%3cpath%20d='M5.25%203.49996V2.91663C5.25%201.95013%206.03347%201.16663%207%201.16663C7.96652%201.16663%208.75%201.95013%208.75%202.91663V3.49996'%20stroke='black'%20stroke-opacity='0.75'%20stroke-linecap='round'/%3e%3c/svg%3e";
const _hoisted_1$e = ["src"];
const _hoisted_2$c = ["src"];
const _hoisted_3$c = { class: "booking-indicator-count" };
const _sfc_main$g = /* @__PURE__ */ defineComponent({
  __name: "BpaBookingIndicators",
  props: {
    booking: {},
    size: { default: "default" }
  },
  setup(__props) {
    const props = __props;
    const indicators = computed(() => getBookingMultiIndicators(props.booking));
    return (_ctx, _cache) => {
      return indicators.value.length > 0 ? (openBlock(), createElementBlock("span", {
        key: 0,
        class: normalizeClass(["booking-indicators bpa-booking-indicator-wrapper", `is-${__props.size}`]),
        "aria-hidden": "true"
      }, [
        (openBlock(true), createElementBlock(Fragment, null, renderList(indicators.value, (indicator) => {
          return openBlock(), createElementBlock("span", {
            key: indicator.kind,
            class: normalizeClass(["booking-indicator bpa-booking-indicator", [`is-${indicator.kind}`, `bpa-booking-indicator--${indicator.kind}`]])
          }, [
            indicator.kind === "staff" ? (openBlock(), createElementBlock("img", {
              key: 0,
              class: "booking-indicator-icon",
              src: unref(staffIconUrl),
              alt: ""
            }, null, 8, _hoisted_1$e)) : (openBlock(), createElementBlock("img", {
              key: 1,
              class: "booking-indicator-icon",
              src: unref(multiServiceIconUrl),
              alt: ""
            }, null, 8, _hoisted_2$c)),
            createElementVNode("span", _hoisted_3$c, toDisplayString$1(indicator.count), 1)
          ], 2);
        }), 128))
      ], 2)) : createCommentVNode("", true);
    };
  }
});
const _export_sfc = (sfc, props) => {
  const target = sfc.__vccOpts || sfc;
  for (const [key, val] of props) {
    target[key] = val;
  }
  return target;
};
const BpaBookingIndicators = /* @__PURE__ */ _export_sfc(_sfc_main$g, [["__scopeId", "data-v-7aaea9cc"]]);
const _hoisted_1$d = { class: "booking-content" };
const _hoisted_2$b = { class: "booking-header" };
const _hoisted_3$b = {
  key: 1,
  class: "status-icon",
  "aria-hidden": "true"
};
const _hoisted_4$9 = ["src"];
const _hoisted_5$8 = {
  key: 1,
  width: "14",
  height: "14",
  viewBox: "0 0 14 14",
  fill: "none",
  xmlns: "http://www.w3.org/2000/svg"
};
const _hoisted_6$8 = {
  class: "row-icon",
  "aria-hidden": "true"
};
const _hoisted_7$7 = {
  key: 0,
  width: "12",
  height: "12",
  viewBox: "0 0 24 24",
  fill: "none",
  stroke: "currentColor",
  "stroke-width": "2",
  "stroke-linecap": "round",
  "stroke-linejoin": "round"
};
const _hoisted_8$7 = {
  key: 1,
  width: "12",
  height: "12",
  viewBox: "0 0 24 24",
  fill: "none",
  stroke: "currentColor",
  "stroke-width": "2",
  "stroke-linecap": "round",
  "stroke-linejoin": "round"
};
const _hoisted_9$7 = {
  key: 2,
  width: "12",
  height: "12",
  viewBox: "0 0 24 24",
  fill: "none",
  stroke: "currentColor",
  "stroke-width": "2",
  "stroke-linecap": "round",
  "stroke-linejoin": "round"
};
const _hoisted_10$7 = {
  key: 3,
  width: "12",
  height: "12",
  viewBox: "0 0 24 24",
  fill: "none",
  stroke: "currentColor",
  "stroke-width": "2",
  "stroke-linecap": "round",
  "stroke-linejoin": "round"
};
const _hoisted_11$5 = {
  key: 4,
  width: "12",
  height: "12",
  viewBox: "0 0 24 24",
  fill: "none",
  stroke: "currentColor",
  "stroke-width": "2",
  "stroke-linecap": "round",
  "stroke-linejoin": "round"
};
const _hoisted_12$5 = {
  key: 5,
  width: "12",
  height: "12",
  viewBox: "0 0 24 24",
  fill: "none",
  stroke: "currentColor",
  "stroke-width": "2",
  "stroke-linecap": "round",
  "stroke-linejoin": "round"
};
const _hoisted_13$4 = { class: "row-text" };
const _hoisted_14$3 = {
  key: 0,
  class: "extra-fields-separator"
};
const _hoisted_15$3 = { class: "row-text" };
const _sfc_main$f = /* @__PURE__ */ defineComponent({
  __name: "BpaBookingCard",
  props: {
    positioned: {},
    isPreview: { type: Boolean },
    isDragging: { type: Boolean },
    dragTransform: {},
    displaySettings: {},
    dragEnabled: { type: Boolean },
    resizeEnabled: { type: Boolean }
  },
  emits: ["dragStart", "resizeTopStart", "resizeBottomStart", "cardClick"],
  setup(__props, { emit: __emit }) {
    const props = __props;
    const emit = __emit;
    const cardRootRef = ref(null);
    const statusIconSrc = computed(() => {
      var _a;
      return ((_a = getStatusDefinition(props.positioned.booking.status)) == null ? void 0 : _a.iconSrc) ?? "";
    });
    const density = computed(
      () => getDensityConfig(props.positioned.totalLanes)
    );
    const colors = computed(() => props.positioned.booking.theme ?? {
      bg: "var(--bpa-cl-white)",
      border: "var(--bpa-gt-gray-400)",
      text: "var(--bpa-dt-black-400)"
    });
    const isOvernightStart = computed(() => props.positioned.booking.overnightPosition === "start");
    const isOvernightEnd = computed(() => props.positioned.booking.overnightPosition === "end");
    const isPast = computed(() => resolveTimeBookingIsPast(props.positioned.booking));
    const canResize = computed(() => !!props.resizeEnabled && resolveTimeBookingResizeEnabled(props.positioned.booking));
    const style = computed(() => {
      const r = props.positioned.rect;
      const t = props.dragTransform;
      let borderRadius = "6px";
      if (isOvernightStart.value) borderRadius = "6px 6px 0 0";
      else if (isOvernightEnd.value) borderRadius = "0 0 6px 6px";
      return {
        position: "absolute",
        top: `${r.top}px`,
        height: `${r.height}px`,
        left: `${r.left}px`,
        width: `${r.width}px`,
        backgroundColor: colors.value.bg,
        border: `1px solid ${colors.value.border}`,
        color: "#3D3F3F",
        zIndex: props.isDragging ? 100 : 1,
        opacity: props.isPreview ? 0.7 : 1,
        cursor: props.isDragging ? "grabbing" : props.dragEnabled && !isPast.value ? "grab" : "pointer",
        touchAction: props.dragEnabled && !isPast.value ? "none" : "auto",
        transition: props.isDragging ? "none" : "box-shadow 0.15s ease",
        willChange: props.isDragging ? "transform, top, left" : "auto",
        borderRadius,
        ...t && { transform: `translate(${t.x}px, ${t.y}px)` }
      };
    });
    const isSmallCard = computed(() => props.positioned.rect.height < CARD_MIN_HEIGHT);
    const visibleSettings = computed(() => {
      if (!props.displaySettings) return [];
      return props.displaySettings.filter((setting) => setting.visible);
    });
    const titleField = computed(() => visibleSettings.value[0]);
    const otherFields = computed(() => visibleSettings.value.slice(1));
    const titleText = computed(() => {
      const value = titleField.value ? resolveTimeBookingDisplayFieldValue(props.positioned.booking, titleField.value.id) : "";
      return value || props.positioned.booking.customerName;
    });
    const detailFields = computed(() => otherFields.value.map((field) => ({
      field,
      value: resolveTimeBookingDisplayFieldValue(props.positioned.booking, field.id)
    })).filter((entry) => !!entry.value));
    const standardDetailFields = computed(() => detailFields.value.filter(
      (entry) => getFieldIconKind(entry.field.id) !== "custom"
    ));
    const extraDetailFields = computed(() => detailFields.value.filter(
      (entry) => getFieldIconKind(entry.field.id) === "custom"
    ));
    function getFieldIconKind(id) {
      return resolveDisplayFieldIconKind(id);
    }
    let pointerStart = null;
    let didDrag = false;
    let cleanupPendingPointerInteraction = null;
    function onPointerDown(e) {
      if (e.target.classList.contains("resize-handle")) return;
      cancelPendingPointerInteraction();
      pointerStart = { x: e.clientX, y: e.clientY };
      didDrag = false;
      const el = cardRootRef.value ?? e.currentTarget;
      const onMove = (me) => {
        if (!pointerStart) return;
        if (!props.dragEnabled) return;
        const dx = me.clientX - pointerStart.x;
        const dy = me.clientY - pointerStart.y;
        if (Math.sqrt(dx * dx + dy * dy) > 5) {
          didDrag = true;
          if (!isPast.value) {
            cleanup();
            e.preventDefault();
            e.stopPropagation();
            emit("dragStart", e, el);
          }
        }
      };
      const onUp = () => {
        cleanup();
        if (!didDrag && pointerStart) {
          emit("cardClick", props.positioned.booking, el);
        }
        pointerStart = null;
      };
      const onCancel = () => {
        cleanup();
        pointerStart = null;
      };
      const cleanup = () => {
        document.removeEventListener("pointermove", onMove);
        document.removeEventListener("pointerup", onUp);
        document.removeEventListener("pointercancel", onCancel);
        if (typeof window !== "undefined") {
          window.removeEventListener("scroll", onCancel, true);
        }
        if (cleanupPendingPointerInteraction === cleanup) {
          cleanupPendingPointerInteraction = null;
        }
      };
      cleanupPendingPointerInteraction = cleanup;
      document.addEventListener("pointermove", onMove);
      document.addEventListener("pointerup", onUp);
      document.addEventListener("pointercancel", onCancel);
      if (typeof window !== "undefined") {
        window.addEventListener("scroll", onCancel, true);
      }
    }
    function cancelPendingPointerInteraction() {
      cleanupPendingPointerInteraction == null ? void 0 : cleanupPendingPointerInteraction();
    }
    onUnmounted(() => {
      cancelPendingPointerInteraction();
    });
    return (_ctx, _cache) => {
      return openBlock(), createElementBlock("div", {
        ref_key: "cardRootRef",
        ref: cardRootRef,
        class: normalizeClass(["booking-card", { "is-dragging": __props.isDragging, "is-preview": __props.isPreview }]),
        style: normalizeStyle(style.value),
        onPointerdown: onPointerDown
      }, [
        canResize.value && !isOvernightEnd.value && !isPast.value ? (openBlock(), createElementBlock("div", {
          key: 0,
          class: "resize-handle resize-top",
          onPointerdown: _cache[0] || (_cache[0] = withModifiers(($event) => emit("resizeTopStart", $event), ["stop", "prevent"]))
        }, null, 32)) : createCommentVNode("", true),
        createElementVNode("div", _hoisted_1$d, [
          createElementVNode("div", _hoisted_2$b, [
            createElementVNode("span", {
              class: "customer-name",
              style: normalizeStyle({ color: colors.value.text })
            }, toDisplayString$1(titleText.value), 5),
            !isSmallCard.value ? (openBlock(), createBlock(BpaBookingIndicators, {
              key: 0,
              booking: __props.positioned.booking,
              style: normalizeStyle({ "--booking-indicator-text-color": colors.value.text })
            }, null, 8, ["booking", "style"])) : createCommentVNode("", true),
            !isSmallCard.value && density.value.showStatus ? (openBlock(), createElementBlock("span", _hoisted_3$b, [
              statusIconSrc.value ? (openBlock(), createElementBlock("img", {
                key: 0,
                class: "status-icon-image",
                src: statusIconSrc.value,
                alt: ""
              }, null, 8, _hoisted_4$9)) : (openBlock(), createElementBlock("svg", _hoisted_5$8, [..._cache[2] || (_cache[2] = [
                createElementVNode("rect", {
                  width: "14",
                  height: "14",
                  rx: "7",
                  fill: "#94A3B8"
                }, null, -1),
                createElementVNode("circle", {
                  cx: "7",
                  cy: "7",
                  r: "1.3",
                  fill: "white"
                }, null, -1)
              ])]))
            ])) : createCommentVNode("", true)
          ]),
          !isSmallCard.value ? (openBlock(), createElementBlock(Fragment, { key: 0 }, [
            (openBlock(true), createElementBlock(Fragment, null, renderList(standardDetailFields.value, (entry) => {
              return openBlock(), createElementBlock("div", {
                key: entry.field.id,
                class: "booking-row"
              }, [
                createElementVNode("span", _hoisted_6$8, [
                  getFieldIconKind(entry.field.id) === "time" ? (openBlock(), createElementBlock("svg", _hoisted_7$7, [..._cache[3] || (_cache[3] = [
                    createElementVNode("circle", {
                      cx: "12",
                      cy: "12",
                      r: "10"
                    }, null, -1),
                    createElementVNode("polyline", { points: "12 6 12 12 16 14" }, null, -1)
                  ])])) : getFieldIconKind(entry.field.id) === "service" ? (openBlock(), createElementBlock("svg", _hoisted_8$7, [..._cache[4] || (_cache[4] = [
                    createElementVNode("rect", {
                      x: "3",
                      y: "8",
                      width: "18",
                      height: "14",
                      rx: "2",
                      ry: "2"
                    }, null, -1),
                    createElementVNode("path", { d: "M16 8V6a4 4 0 0 0-8 0v2" }, null, -1)
                  ])])) : getFieldIconKind(entry.field.id) === "staff" ? (openBlock(), createElementBlock("svg", _hoisted_9$7, [..._cache[5] || (_cache[5] = [
                    createElementVNode("circle", {
                      cx: "12",
                      cy: "8",
                      r: "7"
                    }, null, -1),
                    createElementVNode("polyline", { points: "8.21 13.89 7 23 12 20 17 23 15.79 13.88" }, null, -1)
                  ])])) : getFieldIconKind(entry.field.id) === "location" ? (openBlock(), createElementBlock("svg", _hoisted_10$7, [..._cache[6] || (_cache[6] = [
                    createElementVNode("path", { d: "M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" }, null, -1),
                    createElementVNode("circle", {
                      cx: "12",
                      cy: "10",
                      r: "3"
                    }, null, -1)
                  ])])) : getFieldIconKind(entry.field.id) === "price" ? (openBlock(), createElementBlock("svg", _hoisted_11$5, [..._cache[7] || (_cache[7] = [
                    createElementVNode("circle", {
                      cx: "12",
                      cy: "12",
                      r: "10"
                    }, null, -1),
                    createElementVNode("path", { d: "M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8" }, null, -1),
                    createElementVNode("path", { d: "M12 18V6" }, null, -1)
                  ])])) : getFieldIconKind(entry.field.id) === "customer" ? (openBlock(), createElementBlock("svg", _hoisted_12$5, [..._cache[8] || (_cache[8] = [
                    createElementVNode("path", { d: "M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" }, null, -1),
                    createElementVNode("circle", {
                      cx: "12",
                      cy: "7",
                      r: "4"
                    }, null, -1)
                  ])])) : createCommentVNode("", true)
                ]),
                createElementVNode("span", _hoisted_13$4, toDisplayString$1(entry.value), 1)
              ]);
            }), 128)),
            extraDetailFields.value.length > 0 ? (openBlock(), createElementBlock("div", _hoisted_14$3)) : createCommentVNode("", true),
            (openBlock(true), createElementBlock(Fragment, null, renderList(extraDetailFields.value, (entry) => {
              return openBlock(), createElementBlock("div", {
                key: entry.field.id,
                class: "booking-row"
              }, [
                _cache[9] || (_cache[9] = createElementVNode("span", {
                  class: "row-icon",
                  "aria-hidden": "true"
                }, [
                  createElementVNode("svg", {
                    width: "6",
                    height: "6",
                    viewBox: "0 0 6 6",
                    fill: "none",
                    xmlns: "http://www.w3.org/2000/svg"
                  }, [
                    createElementVNode("circle", {
                      cx: "3",
                      cy: "3",
                      r: "3",
                      fill: "black",
                      "fill-opacity": "0.75"
                    })
                  ])
                ], -1)),
                createElementVNode("span", _hoisted_15$3, toDisplayString$1(entry.value), 1)
              ]);
            }), 128))
          ], 64)) : createCommentVNode("", true)
        ]),
        canResize.value && !isOvernightStart.value && !isPast.value ? (openBlock(), createElementBlock("div", {
          key: 1,
          class: "resize-handle resize-bottom",
          onPointerdown: _cache[1] || (_cache[1] = withModifiers(($event) => emit("resizeBottomStart", $event), ["stop", "prevent"]))
        }, null, 32)) : createCommentVNode("", true)
      ], 38);
    };
  }
});
const BpaBookingCard = /* @__PURE__ */ _export_sfc(_sfc_main$f, [["__scopeId", "data-v-b84ed44e"]]);
const filterEmployeeIconUrl = "data:image/svg+xml,%3csvg%20width='20'%20height='20'%20viewBox='0%200%2020%2020'%20fill='none'%20xmlns='http://www.w3.org/2000/svg'%3e%3cpath%20d='M16.6669%2018.5713V16.6666C16.6669%2015.6563%2016.2656%2014.6873%2015.5512%2013.9729C14.8368%2013.2585%2013.8678%2012.8572%2012.8575%2012.8572H7.14339C6.13307%2012.8572%205.16414%2013.2585%204.44973%2013.9729C3.73533%2014.6873%203.33398%2015.6563%203.33398%2016.6666V18.5713'%20stroke='%23535D71'%20stroke-width='1.1'%20stroke-linecap='round'%20stroke-linejoin='round'/%3e%3cpath%20d='M9.99984%209.04752C12.1037%209.04752%2013.8092%207.34199%2013.8092%205.23812C13.8092%203.13424%2012.1037%201.42871%209.99984%201.42871C7.89596%201.42871%206.19043%203.13424%206.19043%205.23812C6.19043%207.34199%207.89596%209.04752%209.99984%209.04752Z'%20stroke='%23535D71'%20stroke-width='1.1'%20stroke-linecap='round'%20stroke-linejoin='round'/%3e%3c/svg%3e";
const filterLocationIconUrl = "data:image/svg+xml,%3csvg%20width='20'%20height='20'%20viewBox='0%200%2020%2020'%20fill='none'%20xmlns='http://www.w3.org/2000/svg'%3e%3cpath%20d='M16.8582%208.28567C16.8582%2012.5658%2012.11%2017.0234%2010.5156%2018.4001C10.367%2018.5118%2010.1862%2018.5722%2010.0004%2018.5722C9.81455%2018.5722%209.63374%2018.5118%209.4852%2018.4001C7.89076%2017.0234%203.14258%2012.5658%203.14258%208.28567C3.14258%206.46687%203.8651%204.72255%205.15119%203.43646C6.43728%202.15037%208.18159%201.42786%2010.0004%201.42786C11.8192%201.42786%2013.5635%202.15037%2014.8496%203.43646C16.1357%204.72255%2016.8582%206.46687%2016.8582%208.28567Z'%20stroke='%23535D71'%20stroke-width='1.1'%20stroke-linecap='round'%20stroke-linejoin='round'/%3e%3cpath%20d='M9.99941%2010.8573C11.4197%2010.8573%2012.5711%209.70597%2012.5711%208.28567C12.5711%206.86537%2011.4197%205.71399%209.99941%205.71399C8.57911%205.71399%207.42773%206.86537%207.42773%208.28567C7.42773%209.70597%208.57911%2010.8573%209.99941%2010.8573Z'%20stroke='%23535D71'%20stroke-width='1.1'%20stroke-linecap='round'%20stroke-linejoin='round'/%3e%3c/svg%3e";
const filterServiceIconUrl = "data:image/svg+xml,%3csvg%20width='20'%20height='20'%20viewBox='0%200%2020%2020'%20fill='none'%20xmlns='http://www.w3.org/2000/svg'%3e%3cpath%20d='M2.97967%2010.4526C3.43909%208.15554%203.6688%207.00698%204.42782%206.26723C4.56811%206.13051%204.71988%206.0061%204.88145%205.89535C5.75569%205.29614%206.92699%205.29614%209.26959%205.29614H10.726C13.0687%205.29614%2014.2399%205.29614%2015.1142%205.89535C15.2758%206.0061%2015.4275%206.13051%2015.5678%206.26723C16.3268%207.00698%2016.5565%208.15554%2017.016%2010.4526C17.6755%2013.7505%2018.0053%2015.3995%2017.2461%2016.5679C17.1087%2016.7795%2016.9482%2016.9752%2016.7678%2017.1515C15.7709%2018.1251%2014.0893%2018.1251%2010.726%2018.1251H9.26959C5.90637%2018.1251%204.22476%2018.1251%203.22789%2017.1515C3.04739%2016.9752%202.88694%2016.7795%202.74949%2016.5679C1.9903%2015.3995%202.32009%2013.7505%202.97967%2010.4526Z'%20stroke='%23535D71'/%3e%3cpath%20d='M12.5633%208.71736C13.0356%208.71736%2013.4185%208.33445%2013.4185%207.8621C13.4185%207.38975%2013.0356%207.00684%2012.5633%207.00684C12.0909%207.00684%2011.708%207.38975%2011.708%207.8621C11.708%208.33445%2012.0909%208.71736%2012.5633%208.71736Z'%20fill='%23535D71'/%3e%3cpath%20d='M7.43144%208.71761C7.90378%208.71761%208.2867%208.33469%208.2867%207.86234C8.2867%207.38999%207.90378%207.00708%207.43144%207.00708C6.95909%207.00708%206.57617%207.38999%206.57617%207.86234C6.57617%208.33469%206.95909%208.71761%207.43144%208.71761Z'%20fill='%23535D71'/%3e%3cpath%20d='M7.43164%205.29605V4.44079C7.43164%203.02375%208.58034%201.875%209.99743%201.875C11.4145%201.875%2012.5632%203.02375%2012.5632%204.44079V5.29605'%20stroke='%23535D71'%20stroke-linecap='round'/%3e%3c/svg%3e";
const filterStatusIconUrl = "data:image/svg+xml,%3csvg%20width='20'%20height='20'%20viewBox='0%200%2020%2020'%20fill='none'%20xmlns='http://www.w3.org/2000/svg'%3e%3ccircle%20cx='10'%20cy='10'%20r='8'%20stroke='%23535D71'%20stroke-width='1.1'/%3e%3ccircle%20cx='10'%20cy='10'%20r='4'%20stroke='%23535D71'%20stroke-width='1.1'/%3e%3c/svg%3e";
const _hoisted_1$c = { class: "popover-header" };
const _hoisted_2$a = { class: "popover-title" };
const _hoisted_3$a = ["aria-label"];
const _hoisted_4$8 = { class: "filters-popover-body" };
const _hoisted_5$7 = { class: "filter-field-shell" };
const _hoisted_6$7 = {
  class: "filter-field-icon",
  "aria-hidden": "true"
};
const _hoisted_7$6 = ["src"];
const _hoisted_8$6 = {
  key: 1,
  width: "20",
  height: "20",
  viewBox: "0 0 20 20",
  fill: "none",
  xmlns: "http://www.w3.org/2000/svg"
};
const _hoisted_9$6 = {
  key: 2,
  width: "18",
  height: "18",
  viewBox: "0 0 24 24",
  fill: "none"
};
const _hoisted_10$6 = { class: "popover-footer" };
const _sfc_main$e = /* @__PURE__ */ defineComponent({
  __name: "BpaBookingFiltersDialog",
  props: {
    modelValue: { type: Boolean },
    filters: {},
    config: {}
  },
  emits: ["update:modelValue", "update:filters", "apply"],
  setup(__props, { emit: __emit }) {
    const props = __props;
    const emit = __emit;
    const draftFilters = reactive({
      service: null,
      status: null,
      employee: null,
      location: null,
      category: null
    });
    const uiText = useCalendarText();
    const fields = computed(() => {
      const fieldOrder = ["category", "service", "employee", "location", "status"];
      return fieldOrder.map((fieldId) => {
        const fieldConfig = props.config[fieldId];
        if (!fieldConfig || !fieldConfig.visible) {
          return null;
        }
        return {
          id: fieldId,
          label: fieldConfig.label ?? getDefaultLabel(fieldId),
          placeholder: fieldConfig.placeholder ?? getDefaultPlaceholder(fieldId),
          options: fieldConfig.options ?? [],
          multiple: fieldConfig.multiple ?? false
        };
      }).filter((field) => field !== null);
    });
    watch(
      () => props.filters,
      (value) => {
        draftFilters.service = value.service;
        draftFilters.status = value.status;
        draftFilters.employee = value.employee;
        draftFilters.location = value.location;
        draftFilters.category = value.category;
      },
      { immediate: true, deep: true }
    );
    watch(
      () => props.modelValue,
      (isOpen) => {
        if (isOpen) {
          draftFilters.service = props.filters.service;
          draftFilters.status = props.filters.status;
          draftFilters.employee = props.filters.employee;
          draftFilters.location = props.filters.location;
          draftFilters.category = props.filters.category;
        }
      }
    );
    function closeDialog() {
      emit("update:modelValue", false);
    }
    function applyFilters() {
      const nextFilters = {
        service: draftFilters.service,
        status: draftFilters.status,
        employee: draftFilters.employee,
        location: draftFilters.location,
        category: draftFilters.category
      };
      emit("update:filters", nextFilters);
      emit("apply", nextFilters);
      closeDialog();
    }
    function updateField(fieldId, value) {
      draftFilters[fieldId] = value ?? null;
    }
    function getDefaultLabel(fieldId) {
      switch (fieldId) {
        case "service":
          return uiText.value.bookingFilters.fields.service;
        case "status":
          return uiText.value.bookingFilters.fields.status;
        case "employee":
          return uiText.value.bookingFilters.fields.employee;
        case "location":
          return uiText.value.bookingFilters.fields.location;
        case "category":
          return uiText.value.bookingFilters.fields.category;
      }
    }
    function getDefaultPlaceholder(fieldId) {
      switch (fieldId) {
        case "service":
          return uiText.value.bookingFilters.placeholders.service;
        case "status":
          return uiText.value.bookingFilters.placeholders.status;
        case "employee":
          return uiText.value.bookingFilters.placeholders.employee;
        case "location":
          return uiText.value.bookingFilters.placeholders.location;
        case "category":
          return uiText.value.bookingFilters.placeholders.category;
      }
    }
    function getFilterIconSrc(fieldId) {
      switch (fieldId) {
        case "service":
          return filterServiceIconUrl;
        case "status":
          return filterStatusIconUrl;
        case "employee":
          return filterEmployeeIconUrl;
        case "location":
          return filterLocationIconUrl;
        default:
          return "";
      }
    }
    return (_ctx, _cache) => {
      const _component_BpUiSelect = resolveComponent("BpUiSelect");
      const _component_BpUiButton = resolveComponent("BpUiButton");
      return openBlock(), createElementBlock("div", {
        class: "filters-popover",
        onMousedown: _cache[0] || (_cache[0] = withModifiers(() => {
        }, ["stop"])),
        onClick: _cache[1] || (_cache[1] = withModifiers(() => {
        }, ["stop"]))
      }, [
        createElementVNode("div", _hoisted_1$c, [
          createElementVNode("div", _hoisted_2$a, toDisplayString$1(unref(uiText).bookingFilters.title), 1),
          createElementVNode("button", {
            class: "close-btn",
            type: "button",
            "aria-label": unref(uiText).shell.close,
            onClick: closeDialog
          }, [..._cache[2] || (_cache[2] = [
            createElementVNode("svg", {
              width: "18",
              height: "18",
              viewBox: "0 0 24 24",
              fill: "none"
            }, [
              createElementVNode("path", {
                d: "M18 6 6 18",
                stroke: "currentColor",
                "stroke-width": "1.8",
                "stroke-linecap": "round"
              }),
              createElementVNode("path", {
                d: "m6 6 12 12",
                stroke: "currentColor",
                "stroke-width": "1.8",
                "stroke-linecap": "round"
              })
            ], -1)
          ])], 8, _hoisted_3$a)
        ]),
        createElementVNode("div", _hoisted_4$8, [
          (openBlock(true), createElementBlock(Fragment, null, renderList(fields.value, (field) => {
            return openBlock(), createElementBlock("div", {
              key: field.id,
              class: "filter-field"
            }, [
              createElementVNode("div", _hoisted_5$7, [
                createElementVNode("div", _hoisted_6$7, [
                  getFilterIconSrc(field.id) ? (openBlock(), createElementBlock("img", {
                    key: 0,
                    class: "filter-field-icon-image",
                    src: getFilterIconSrc(field.id),
                    alt: ""
                  }, null, 8, _hoisted_7$6)) : field.id === "category" ? (openBlock(), createElementBlock("svg", _hoisted_8$6, [..._cache[3] || (_cache[3] = [
                    createStaticVNode('<path d="M7.5 2.5H3.33333C2.8731 2.5 2.5 2.8731 2.5 3.33333V7.5C2.5 7.96024 2.8731 8.33333 3.33333 8.33333H7.5C7.96024 8.33333 8.33333 7.96024 8.33333 7.5V3.33333C8.33333 2.8731 7.96024 2.5 7.5 2.5Z" stroke="#535D71" stroke-width="1.1" stroke-linecap="round" stroke-linejoin="round" data-v-b7893eb3></path><path d="M7.5 11.6666H3.33333C2.8731 11.6666 2.5 12.0397 2.5 12.5V16.6666C2.5 17.1269 2.8731 17.5 3.33333 17.5H7.5C7.96024 17.5 8.33333 17.1269 8.33333 16.6666V12.5C8.33333 12.0397 7.96024 11.6666 7.5 11.6666Z" stroke="#535D71" stroke-width="1.1" stroke-linecap="round" stroke-linejoin="round" data-v-b7893eb3></path><path d="M11.667 3.33337H17.5003" stroke="#535D71" stroke-width="1.1" stroke-linecap="round" stroke-linejoin="round" data-v-b7893eb3></path><path d="M11.667 7.5H17.5003" stroke="#535D71" stroke-width="1.1" stroke-linecap="round" stroke-linejoin="round" data-v-b7893eb3></path><path d="M11.667 12.5H17.5003" stroke="#535D71" stroke-width="1.1" stroke-linecap="round" stroke-linejoin="round" data-v-b7893eb3></path><path d="M11.667 16.6666H17.5003" stroke="#535D71" stroke-width="1.1" stroke-linecap="round" stroke-linejoin="round" data-v-b7893eb3></path>', 6)
                  ])])) : (openBlock(), createElementBlock("svg", _hoisted_9$6, [..._cache[4] || (_cache[4] = [
                    createElementVNode("circle", {
                      cx: "12",
                      cy: "12",
                      r: "7",
                      stroke: "currentColor",
                      "stroke-width": "1.7"
                    }, null, -1),
                    createElementVNode("circle", {
                      cx: "12",
                      cy: "12",
                      r: "2.5",
                      stroke: "currentColor",
                      "stroke-width": "1.7"
                    }, null, -1)
                  ])]))
                ]),
                createVNode(_component_BpUiSelect, {
                  class: "filter-select",
                  "model-value": draftFilters[field.id],
                  options: field.options,
                  placeholder: field.placeholder,
                  "aria-label": field.label,
                  multiple: field.multiple,
                  "collapse-tags": "",
                  "max-collapse-tags": 1,
                  "collapse-tags-tooltip": "",
                  teleported: false,
                  clearable: "",
                  "onUpdate:modelValue": ($event) => updateField(field.id, $event)
                }, null, 8, ["model-value", "options", "placeholder", "aria-label", "multiple", "onUpdate:modelValue"])
              ])
            ]);
          }), 128))
        ]),
        createElementVNode("div", _hoisted_10$6, [
          createVNode(_component_BpUiButton, {
            class: "apply-btn",
            type: "primary",
            onClick: applyFilters
          }, {
            default: withCtx(() => [
              createTextVNode(toDisplayString$1(unref(uiText).bookingFilters.apply), 1)
            ]),
            _: 1
          })
        ])
      ], 32);
    };
  }
});
const BpaBookingFiltersDialog = /* @__PURE__ */ _export_sfc(_sfc_main$e, [["__scopeId", "data-v-b7893eb3"]]);
const _hoisted_1$b = { class: "filter-popover" };
const _hoisted_2$9 = { class: "popover-header" };
const _hoisted_3$9 = ["aria-label"];
const _hoisted_4$7 = { class: "popover-body" };
const _hoisted_5$6 = ["onDragover", "onDrop"];
const _hoisted_6$6 = { class: "field-label" };
const _hoisted_7$5 = ["aria-label", "onDragstart"];
const _hoisted_8$5 = {
  key: 0,
  class: "section-separator"
};
const _hoisted_9$5 = ["onDragover", "onDrop"];
const _hoisted_10$5 = { class: "field-label" };
const _hoisted_11$4 = ["aria-label", "onDragstart"];
const _hoisted_12$4 = { class: "popover-footer" };
const _sfc_main$d = /* @__PURE__ */ defineComponent({
  __name: "BpaFilterPopover",
  props: {
    modelValue: {},
    open: { type: Boolean }
  },
  emits: ["update:modelValue", "close"],
  setup(__props, { emit: __emit }) {
    const uiText = useCalendarText();
    const props = __props;
    const emit = __emit;
    const draggingSettingId = ref(null);
    const draftSettings = ref([]);
    const primarySectionEntries = computed(() => getSectionEntries("primary"));
    const extraSectionEntries = computed(() => getSectionEntries("extra"));
    function cloneSettings(settings) {
      return settings.map((setting) => ({ ...setting }));
    }
    function isExtraSection(setting) {
      return setting.section === "extra";
    }
    function getSectionEntries(section) {
      return draftSettings.value.map((item, index) => ({ item, index })).filter(({ item }) => section === "extra" ? isExtraSection(item) : !isExtraSection(item));
    }
    function moveDraggedSetting(targetId, insertAfter) {
      const sourceId = draggingSettingId.value;
      if (!sourceId || sourceId === targetId) {
        return;
      }
      const settings = [...draftSettings.value];
      const sourceIndex = settings.findIndex((setting) => setting.id === sourceId);
      const targetIndex = settings.findIndex((setting) => setting.id === targetId);
      if (sourceIndex < 0 || targetIndex < 0) {
        return;
      }
      const sourceSetting = settings[sourceIndex];
      const targetSetting = settings[targetIndex];
      if (!sourceSetting || !targetSetting || isExtraSection(sourceSetting) !== isExtraSection(targetSetting)) {
        return;
      }
      const [removed] = settings.splice(sourceIndex, 1);
      if (!removed) {
        return;
      }
      let insertionIndex = targetIndex;
      if (sourceIndex < targetIndex) {
        insertionIndex -= 1;
      }
      if (insertAfter) {
        insertionIndex += 1;
      }
      insertionIndex = Math.max(0, Math.min(insertionIndex, settings.length));
      if (insertionIndex === sourceIndex) {
        return;
      }
      settings.splice(insertionIndex, 0, removed);
      draftSettings.value = settings;
    }
    function syncDraftFromProps() {
      draftSettings.value = cloneSettings(props.modelValue);
      draggingSettingId.value = null;
    }
    watch(
      () => props.open,
      (isOpen) => {
        if (isOpen) {
          syncDraftFromProps();
        }
      },
      { immediate: true }
    );
    watch(
      () => props.modelValue,
      () => {
        if (!props.open) {
          syncDraftFromProps();
        }
      },
      { deep: true }
    );
    function onDragStart(item, event) {
      draggingSettingId.value = item.id;
      if (event.dataTransfer) {
        event.dataTransfer.effectAllowed = "move";
        event.dataTransfer.setData("text/plain", item.id);
      }
    }
    function onDragOver(target, event) {
      event.preventDefault();
      if (event.dataTransfer) {
        event.dataTransfer.dropEffect = "move";
      }
      const targetEl = event.currentTarget;
      if (!targetEl) {
        return;
      }
      const rect = targetEl.getBoundingClientRect();
      moveDraggedSetting(target.id, event.clientY > rect.top + rect.height / 2);
    }
    function onDrop(target, event) {
      event.preventDefault();
      const targetEl = event.currentTarget;
      if (targetEl) {
        const rect = targetEl.getBoundingClientRect();
        moveDraggedSetting(target.id, event.clientY > rect.top + rect.height / 2);
      }
      draggingSettingId.value = null;
    }
    function onDragEnd() {
      draggingSettingId.value = null;
    }
    function toggleVisibility(index, visible) {
      const settings = [...draftSettings.value];
      const setting = settings[index];
      if (!setting) {
        return;
      }
      settings[index] = { ...setting, visible };
      draftSettings.value = settings;
    }
    function applyChanges() {
      emit("update:modelValue", cloneSettings(draftSettings.value));
      emit("close");
    }
    function getFieldLabel(item) {
      return formatDisplayFieldLabel(item);
    }
    return (_ctx, _cache) => {
      const _component_BpUiCheckbox = resolveComponent("BpUiCheckbox");
      const _component_BpUiScrollbar = resolveComponent("BpUiScrollbar");
      const _component_BpUiButton = resolveComponent("BpUiButton");
      return openBlock(), createElementBlock("div", _hoisted_1$b, [
        createElementVNode("div", _hoisted_2$9, [
          createElementVNode("h3", null, toDisplayString$1(unref(uiText).displaySettings.title), 1),
          createElementVNode("button", {
            type: "button",
            class: "close-btn",
            "aria-label": unref(uiText).shell.close,
            onClick: _cache[0] || (_cache[0] = ($event) => emit("close"))
          }, [..._cache[1] || (_cache[1] = [
            createElementVNode("svg", {
              width: "18",
              height: "18",
              viewBox: "0 0 18 18",
              fill: "none",
              xmlns: "http://www.w3.org/2000/svg"
            }, [
              createElementVNode("path", {
                d: "M4.5 4.5L13.5 13.5M13.5 4.5L4.5 13.5",
                stroke: "currentColor",
                "stroke-width": "1.6",
                "stroke-linecap": "round"
              })
            ], -1)
          ])], 8, _hoisted_3$9)
        ]),
        createVNode(_component_BpUiScrollbar, {
          class: "popover-scroll",
          "max-height": 320
        }, {
          default: withCtx(() => [
            createElementVNode("div", _hoisted_4$7, [
              createVNode(TransitionGroup, {
                name: "filter-sort",
                tag: "div",
                class: "section-list"
              }, {
                default: withCtx(() => [
                  (openBlock(true), createElementBlock(Fragment, null, renderList(primarySectionEntries.value, (entry) => {
                    return openBlock(), createElementBlock("div", {
                      key: entry.item.id,
                      class: normalizeClass(["filter-item", { "is-dragging": draggingSettingId.value === entry.item.id }]),
                      onDragover: ($event) => onDragOver(entry.item, $event),
                      onDrop: ($event) => onDrop(entry.item, $event)
                    }, [
                      createVNode(_component_BpUiCheckbox, {
                        class: "field-checkbox",
                        "model-value": entry.item.visible,
                        "onUpdate:modelValue": ($event) => toggleVisibility(entry.index, Boolean($event))
                      }, {
                        default: withCtx(() => [
                          createElementVNode("span", _hoisted_6$6, toDisplayString$1(getFieldLabel(entry.item)), 1)
                        ]),
                        _: 2
                      }, 1032, ["model-value", "onUpdate:modelValue"]),
                      createElementVNode("div", {
                        class: "drag-handle",
                        draggable: "true",
                        "aria-label": `${unref(uiText).displaySettings.reorder} ${getFieldLabel(entry.item)}`,
                        onDragstart: ($event) => onDragStart(entry.item, $event),
                        onDragend: onDragEnd
                      }, [..._cache[2] || (_cache[2] = [
                        createElementVNode("svg", {
                          width: "16",
                          height: "16",
                          viewBox: "0 0 16 16",
                          fill: "none",
                          xmlns: "http://www.w3.org/2000/svg"
                        }, [
                          createElementVNode("circle", {
                            cx: "5",
                            cy: "4",
                            r: "1",
                            fill: "currentColor"
                          }),
                          createElementVNode("circle", {
                            cx: "11",
                            cy: "4",
                            r: "1",
                            fill: "currentColor"
                          }),
                          createElementVNode("circle", {
                            cx: "5",
                            cy: "8",
                            r: "1",
                            fill: "currentColor"
                          }),
                          createElementVNode("circle", {
                            cx: "11",
                            cy: "8",
                            r: "1",
                            fill: "currentColor"
                          }),
                          createElementVNode("circle", {
                            cx: "5",
                            cy: "12",
                            r: "1",
                            fill: "currentColor"
                          }),
                          createElementVNode("circle", {
                            cx: "11",
                            cy: "12",
                            r: "1",
                            fill: "currentColor"
                          })
                        ], -1)
                      ])], 40, _hoisted_7$5)
                    ], 42, _hoisted_5$6);
                  }), 128))
                ]),
                _: 1
              }),
              extraSectionEntries.value.length ? (openBlock(), createElementBlock("div", _hoisted_8$5)) : createCommentVNode("", true),
              createVNode(TransitionGroup, {
                name: "filter-sort",
                tag: "div",
                class: "section-list"
              }, {
                default: withCtx(() => [
                  (openBlock(true), createElementBlock(Fragment, null, renderList(extraSectionEntries.value, (entry) => {
                    return openBlock(), createElementBlock("div", {
                      key: `extra-${entry.item.id}`,
                      class: normalizeClass(["filter-item", { "is-dragging": draggingSettingId.value === entry.item.id }]),
                      onDragover: ($event) => onDragOver(entry.item, $event),
                      onDrop: ($event) => onDrop(entry.item, $event)
                    }, [
                      createVNode(_component_BpUiCheckbox, {
                        class: "field-checkbox",
                        "model-value": entry.item.visible,
                        "onUpdate:modelValue": ($event) => toggleVisibility(entry.index, Boolean($event))
                      }, {
                        default: withCtx(() => [
                          createElementVNode("span", _hoisted_10$5, toDisplayString$1(getFieldLabel(entry.item)), 1)
                        ]),
                        _: 2
                      }, 1032, ["model-value", "onUpdate:modelValue"]),
                      createElementVNode("div", {
                        class: "drag-handle",
                        draggable: "true",
                        "aria-label": `${unref(uiText).displaySettings.reorder} ${getFieldLabel(entry.item)}`,
                        onDragstart: ($event) => onDragStart(entry.item, $event),
                        onDragend: onDragEnd
                      }, [..._cache[3] || (_cache[3] = [
                        createElementVNode("svg", {
                          width: "16",
                          height: "16",
                          viewBox: "0 0 16 16",
                          fill: "none",
                          xmlns: "http://www.w3.org/2000/svg"
                        }, [
                          createElementVNode("circle", {
                            cx: "5",
                            cy: "4",
                            r: "1",
                            fill: "currentColor"
                          }),
                          createElementVNode("circle", {
                            cx: "11",
                            cy: "4",
                            r: "1",
                            fill: "currentColor"
                          }),
                          createElementVNode("circle", {
                            cx: "5",
                            cy: "8",
                            r: "1",
                            fill: "currentColor"
                          }),
                          createElementVNode("circle", {
                            cx: "11",
                            cy: "8",
                            r: "1",
                            fill: "currentColor"
                          }),
                          createElementVNode("circle", {
                            cx: "5",
                            cy: "12",
                            r: "1",
                            fill: "currentColor"
                          }),
                          createElementVNode("circle", {
                            cx: "11",
                            cy: "12",
                            r: "1",
                            fill: "currentColor"
                          })
                        ], -1)
                      ])], 40, _hoisted_11$4)
                    ], 42, _hoisted_9$5);
                  }), 128))
                ]),
                _: 1
              })
            ])
          ]),
          _: 1
        }),
        createElementVNode("div", _hoisted_12$4, [
          createVNode(_component_BpUiButton, {
            class: "apply-btn",
            type: "primary",
            onClick: applyChanges
          }, {
            default: withCtx(() => [
              createTextVNode(toDisplayString$1(unref(uiText).displaySettings.save), 1)
            ]),
            _: 1
          })
        ])
      ]);
    };
  }
});
const BpaFilterPopover = /* @__PURE__ */ _export_sfc(_sfc_main$d, [["__scopeId", "data-v-30c5cc89"]]);
const _hoisted_1$a = { class: "header-left" };
const _hoisted_2$8 = { class: "logo" };
const _hoisted_3$8 = { class: "logo-text" };
const _hoisted_4$6 = {
  key: 0,
  class: "mobile-top-actions"
};
const _hoisted_5$5 = { class: "nav-controls-leading" };
const _hoisted_6$5 = {
  key: 0,
  class: "nav-controls-trailing"
};
const _hoisted_7$4 = { class: "header-center" };
const _hoisted_8$4 = { class: "title" };
const _hoisted_9$4 = { class: "header-right" };
const _hoisted_10$4 = {
  key: 0,
  class: "bpa-calenar-filter-btn-text"
};
const _hoisted_11$3 = ["aria-label"];
const _hoisted_12$3 = ["aria-label", "aria-pressed"];
const _hoisted_13$3 = ["aria-label", "aria-pressed"];
const SIDEBAR_STATE_EVENT_NAME = "bookingpress:calendar-sidebar-state-change";
const _sfc_main$c = /* @__PURE__ */ defineComponent({
  __name: "BpaCalendarHeader",
  props: {
    title: {},
    currentView: {},
    isMobile: { type: Boolean },
    displaySettings: {},
    bookingFilters: {},
    bookingFilterConfig: {},
    showAddAppointmentButton: { type: Boolean }
  },
  emits: ["today", "prev", "next", "addNew", "mobileMenu", "viewChange", "update:displaySettings", "update:bookingFilters"],
  setup(__props, { emit: __emit }) {
    const uiText = useCalendarText();
    const props = __props;
    const emit = __emit;
    const showDisplaySettings = ref(false);
    const showBookingFilters = ref(false);
    const toggleMenuBar = ref(false);
    const viewOptions = computed(() => [
      { label: uiText.value.header.month, value: "month" },
      { label: uiText.value.header.week, value: "week" },
      { label: uiText.value.header.day, value: "day" }
    ]);
    const selectedView = computed(() => props.currentView === "timeline" ? "week" : props.currentView);
    const showWeekTimelineToggle = computed(() => !props.isMobile && (props.currentView === "week" || props.currentView === "timeline"));
    const isTimelineViewActive = computed(() => props.currentView === "timeline");
    const bookingFiltersPopoverWidth = computed(() => props.isMobile ? 304 : 390);
    function handleUpdateSettings(settings) {
      emit("update:displaySettings", settings);
    }
    function handleUpdateFilters(filters) {
      emit("update:bookingFilters", filters);
    }
    function handleWeekTimelineToggle(view) {
      if (props.currentView === view) {
        return;
      }
      emit("viewChange", view);
    }
    function handleMobileMenuClick() {
      showBookingFilters.value = false;
      showDisplaySettings.value = false;
      const nextState = !toggleMenuBar.value;
      toggleMenuBar.value = nextState;
      emit("mobileMenu", nextState);
    }
    function handleSidebarStateChange(event) {
      const detail = event.detail;
      if (typeof (detail == null ? void 0 : detail.open) === "boolean") {
        toggleMenuBar.value = detail.open;
      }
    }
    watch(
      () => props.isMobile,
      (isMobile) => {
        if (!isMobile) {
          toggleMenuBar.value = false;
        }
      }
    );
    onMounted(() => {
      if (typeof window === "undefined") {
        return;
      }
      window.addEventListener(SIDEBAR_STATE_EVENT_NAME, handleSidebarStateChange);
    });
    onUnmounted(() => {
      if (typeof window === "undefined") {
        return;
      }
      window.removeEventListener(SIDEBAR_STATE_EVENT_NAME, handleSidebarStateChange);
    });
    return (_ctx, _cache) => {
      const _component_BpUiButton = resolveComponent("BpUiButton");
      const _component_BpUiSelect = resolveComponent("BpUiSelect");
      const _component_BpUiPopover = resolveComponent("BpUiPopover");
      return openBlock(), createElementBlock("header", {
        class: normalizeClass(["calendar-header", { "is-mobile": props.isMobile }])
      }, [
        createElementVNode("div", _hoisted_1$a, [
          createElementVNode("div", _hoisted_2$8, [
            _cache[16] || (_cache[16] = createStaticVNode('<div class="logo-icon" data-v-8a986cd5><svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg" data-v-8a986cd5><rect width="40" height="40" rx="10" style="fill:var(--bpa-pt-main-green);" data-v-8a986cd5></rect><path d="M22.9153 19.9304C22.9153 19.9304 23.3791 19.4636 23.3791 18.0631C23.3791 15.7318 21.9846 15.265 20.141 15.265H15.9454V25.0596H18.7346V27.8577H20.141V25.0596C21.7586 25.0596 22.8825 25.0596 23.7092 24.0249C24.1051 23.5154 24.315 22.8859 24.3039 22.2408C24.3085 21.9333 24.2685 21.6268 24.1849 21.3309C23.9798 20.8046 23.843 20.3943 22.9153 19.9304ZM21.5207 23.6621H20.141V22.2616C20.1422 21.986 20.225 21.717 20.3789 21.4884C20.5329 21.2599 20.7511 21.0821 21.006 20.9774C21.2609 20.8728 21.5411 20.846 21.8113 20.9005C22.0814 20.955 22.3293 21.0883 22.5237 21.2835C22.7182 21.4788 22.8504 21.7273 22.9037 21.9977C22.9571 22.268 22.9291 22.5481 22.8234 22.8026C22.7177 23.0571 22.5389 23.2745 22.3097 23.4275C22.0805 23.5805 21.8111 23.6621 21.5356 23.6621H21.5207ZM20.3908 19.7966C20.1528 19.9108 19.9267 20.0483 19.7158 20.2069C19.3903 20.4356 19.129 20.7441 18.9572 21.1029C18.7853 21.4617 18.7087 21.8587 18.7346 22.2556V23.6562H17.34V16.6655H20.141C21.2531 16.6655 21.9994 17.1294 21.9994 18.0631C21.9846 18.9194 21.3453 19.3298 20.3908 19.8025V19.7966Z" fill="white" data-v-8a986cd5></path><path d="M31.1426 11.8606V29.5855C31.1434 30.2025 30.8993 30.7945 30.4638 31.2316C30.0284 31.6687 29.4373 31.9151 28.8203 31.9167H11.1638C10.5469 31.9151 9.95575 31.6687 9.52032 31.2316C9.08489 30.7945 8.84076 30.2025 8.84155 29.5855V11.8606C8.84076 11.2437 9.08489 10.6516 9.52032 10.2145C9.95575 9.77741 10.5469 9.53101 11.1638 9.52944H14.5566V8.8277C14.5503 8.7323 14.5637 8.63664 14.596 8.54663C14.6282 8.45662 14.6785 8.37419 14.7439 8.30442C14.8093 8.23465 14.8882 8.17903 14.9759 8.14101C15.0637 8.10299 15.1582 8.08337 15.2538 8.08337C15.3494 8.08337 15.444 8.10299 15.5317 8.14101C15.6195 8.17903 15.6984 8.23465 15.7638 8.30442C15.8292 8.37419 15.8795 8.45662 15.9117 8.54663C15.944 8.63664 15.9574 8.7323 15.9511 8.8277V11.6257C15.9574 11.7211 15.944 11.8168 15.9117 11.9068C15.8795 11.9968 15.8292 12.0792 15.7638 12.149C15.6984 12.2188 15.6195 12.2744 15.5317 12.3124C15.444 12.3504 15.3494 12.3701 15.2538 12.3701C15.1582 12.3701 15.0637 12.3504 14.9759 12.3124C14.8882 12.2744 14.8093 12.2188 14.7439 12.149C14.6785 12.0792 14.6282 11.9968 14.596 11.9068C14.5637 11.8168 14.5503 11.7211 14.5566 11.6257V10.927H11.6277C11.2576 10.9285 10.9032 11.0769 10.6423 11.3394C10.3814 11.602 10.2353 11.9573 10.2361 12.3275V29.1187C10.2357 29.3018 10.2714 29.4832 10.3411 29.6526C10.4109 29.8219 10.5132 29.9759 10.6425 30.1056C10.7717 30.2354 10.9252 30.3385 11.0942 30.4089C11.2633 30.4794 11.4446 30.5158 11.6277 30.5162H28.3535C28.5369 30.5162 28.7185 30.48 28.8878 30.4098C29.0572 30.3395 29.2111 30.2365 29.3406 30.1067C29.4702 29.9769 29.5728 29.8228 29.6427 29.6533C29.7126 29.4837 29.7484 29.3021 29.748 29.1187V12.3275C29.7484 12.1439 29.7127 11.9621 29.6428 11.7924C29.5729 11.6227 29.4703 11.4684 29.3408 11.3384C29.2113 11.2083 29.0575 11.1051 28.8881 11.0345C28.7187 10.9639 28.537 10.9274 28.3535 10.927H25.4276V11.6257C25.4338 11.7211 25.4204 11.8168 25.3882 11.9068C25.356 11.9968 25.3056 12.0792 25.2403 12.149C25.1749 12.2188 25.0959 12.2744 25.0082 12.3124C24.9205 12.3504 24.8259 12.3701 24.7303 12.3701C24.6347 12.3701 24.5401 12.3504 24.4524 12.3124C24.3647 12.2744 24.2857 12.2188 24.2203 12.149C24.155 12.0792 24.1046 11.9968 24.0724 11.9068C24.0402 11.8168 24.0268 11.7211 24.033 11.6257V10.927H21.3866C21.2013 10.927 21.0236 10.8533 20.8925 10.7223C20.7615 10.5913 20.6879 10.4135 20.6879 10.2282C20.6879 10.1364 20.7059 10.0456 20.7411 9.9608C20.7762 9.87602 20.8276 9.79899 20.8925 9.7341C20.9574 9.66921 21.0344 9.61774 21.1192 9.58263C21.204 9.54751 21.2949 9.52944 21.3866 9.52944H24.033V8.8277C24.0268 8.7323 24.0402 8.63664 24.0724 8.54663C24.1046 8.45662 24.155 8.37419 24.2203 8.30442C24.2857 8.23465 24.3647 8.17903 24.4524 8.14101C24.5401 8.10299 24.6347 8.08337 24.7303 8.08337C24.8259 8.08337 24.9205 8.10299 25.0082 8.14101C25.0959 8.17903 25.1749 8.23465 25.2403 8.30442C25.3056 8.37419 25.356 8.45662 25.3882 8.54663C25.4204 8.63664 25.4338 8.7323 25.4276 8.8277V9.52944H28.8203C29.4373 9.53101 30.0284 9.77741 30.4638 10.2145C30.8993 10.6516 31.1434 11.2437 31.1426 11.8606Z" fill="white" data-v-8a986cd5></path></svg></div>', 1)),
            createElementVNode("span", _hoisted_3$8, toDisplayString$1(unref(uiText).header.logo), 1)
          ])
        ]),
        props.isMobile ? (openBlock(), createElementBlock("div", _hoisted_4$6, [
          props.showAddAppointmentButton ? (openBlock(), createBlock(_component_BpUiButton, {
            key: 0,
            class: "add-btn add-btn-mobile",
            type: "primary",
            "icon-only": "",
            "aria-label": unref(uiText).header.addNew,
            onClick: _cache[0] || (_cache[0] = ($event) => emit("addNew"))
          }, {
            default: withCtx(() => [..._cache[17] || (_cache[17] = [
              createElementVNode("svg", {
                width: "16",
                height: "16",
                viewBox: "0 0 16 16",
                fill: "none",
                xmlns: "http://www.w3.org/2000/svg"
              }, [
                createElementVNode("path", {
                  d: "M8 3.33337V12.6667",
                  stroke: "currentColor",
                  "stroke-width": "1.7",
                  "stroke-linecap": "round"
                }),
                createElementVNode("path", {
                  d: "M3.3335 8H12.6668",
                  stroke: "currentColor",
                  "stroke-width": "1.7",
                  "stroke-linecap": "round"
                })
              ], -1)
            ])]),
            _: 1
          }, 8, ["aria-label"])) : createCommentVNode("", true),
          createVNode(_component_BpUiButton, {
            class: normalizeClass(["mobile-menu-btn bpa-navbar-nav no-border", toggleMenuBar.value ? "is-active" : ""]),
            plain: "",
            "icon-only": "",
            "aria-label": toggleMenuBar.value ? unref(uiText).shell.closeSidebar : unref(uiText).shell.openSidebar,
            "aria-pressed": toggleMenuBar.value,
            onClick: handleMobileMenuClick
          }, {
            default: withCtx(() => [
              createElementVNode("div", {
                id: "bpa-menu-toggle",
                class: normalizeClass(["bpa-menu-toggle", { "is-active": toggleMenuBar.value }])
              }, [..._cache[18] || (_cache[18] = [
                createElementVNode("span", { class: "bpa-mm-bar" }, null, -1),
                createElementVNode("span", { class: "bpa-mm-bar" }, null, -1),
                createElementVNode("span", { class: "bpa-mm-bar" }, null, -1)
              ])], 2)
            ]),
            _: 1
          }, 8, ["aria-label", "aria-pressed", "class"])
        ])) : createCommentVNode("", true),
        createElementVNode("div", {
          class: normalizeClass(["nav-controls", { "nav-controls-mobile": props.isMobile }])
        }, [
          createElementVNode("div", _hoisted_5$5, [
            createVNode(_component_BpUiButton, {
              class: "header-btn bpa-today-btn",
              plain: "",
              onClick: _cache[1] || (_cache[1] = ($event) => emit("today"))
            }, {
              default: withCtx(() => [
                createTextVNode(toDisplayString$1(unref(uiText).header.today), 1)
              ]),
              _: 1
            }),
            !props.isMobile ? (openBlock(), createBlock(_component_BpUiSelect, {
              key: 0,
              class: "view-select bpa-calendar-view-selector",
              "model-value": selectedView.value,
              options: viewOptions.value,
              clearable: false,
              teleported: false,
              "onUpdate:modelValue": _cache[2] || (_cache[2] = ($event) => emit("viewChange", $event))
            }, null, 8, ["model-value", "options"])) : createCommentVNode("", true)
          ]),
          props.isMobile ? (openBlock(), createElementBlock("div", _hoisted_6$5, [
            createVNode(_component_BpUiPopover, {
              "model-value": showBookingFilters.value,
              placement: "bottom-end",
              width: bookingFiltersPopoverWidth.value,
              trigger: "click",
              offset: 14,
              persistent: true,
              "show-arrow": false,
              "popper-class": "bpa-booking-filters-popper",
              "onUpdate:modelValue": _cache[4] || (_cache[4] = ($event) => showBookingFilters.value = $event)
            }, {
              reference: withCtx(() => [
                createVNode(_component_BpUiButton, {
                  class: "header-btn bpa-calendar-filter-btn bpa-mobile-toolbar-btn",
                  plain: "",
                  "icon-only": "",
                  "aria-label": unref(uiText).header.filter
                }, {
                  default: withCtx(() => [..._cache[19] || (_cache[19] = [
                    createElementVNode("svg", {
                      width: "16",
                      height: "14",
                      viewBox: "0 0 16 14",
                      fill: "none",
                      xmlns: "http://www.w3.org/2000/svg"
                    }, [
                      createElementVNode("path", {
                        d: "M12.5499 0.650024H2.7499C1.75996 0.650024 1.26498 0.650024 0.95744 0.924824C0.649902 1.19962 0.649902 1.6419 0.649902 2.52646V2.98634C0.649902 3.67823 0.649902 4.02418 0.831622 4.31097C1.01334 4.59775 1.34533 4.77574 2.00932 5.13172L4.04843 6.22495C4.49392 6.46375 4.71667 6.58322 4.87616 6.71508C5.2083 6.98968 5.41276 7.31235 5.50541 7.70815C5.5499 7.89815 5.5499 8.12055 5.5499 8.56528V10.3449C5.5499 10.9513 5.5499 11.2545 5.72625 11.4909C5.9026 11.7272 6.2158 11.8438 6.84224 12.0771C8.15726 12.5667 8.81477 12.8115 9.28237 12.5329C9.7499 12.2544 9.7499 11.6179 9.7499 10.3449V8.56528C9.7499 8.12055 9.7499 7.89815 9.79442 7.70815C9.88703 7.31235 10.0915 6.98968 10.4237 6.71508C10.5831 6.58322 10.8059 6.46375 11.2514 6.22495L13.2905 5.13172C13.9545 4.77574 14.2865 4.59775 14.4682 4.31097C14.6499 4.02418 14.6499 3.67823 14.6499 2.98634V2.52646C14.6499 1.6419 14.6499 1.19962 14.3424 0.924824C14.0348 0.650024 13.5398 0.650024 12.5499 0.650024Z",
                        stroke: "currentColor",
                        "stroke-width": "1.3"
                      })
                    ], -1)
                  ])]),
                  _: 1
                }, 8, ["aria-label"])
              ]),
              default: withCtx(() => [
                createVNode(BpaBookingFiltersDialog, {
                  "model-value": showBookingFilters.value,
                  filters: props.bookingFilters,
                  config: props.bookingFilterConfig,
                  "onUpdate:modelValue": _cache[3] || (_cache[3] = ($event) => showBookingFilters.value = $event),
                  "onUpdate:filters": handleUpdateFilters
                }, null, 8, ["model-value", "filters", "config"])
              ]),
              _: 1
            }, 8, ["model-value", "width"]),
            createVNode(_component_BpUiPopover, {
              "model-value": showDisplaySettings.value,
              placement: "bottom-end",
              width: 304,
              trigger: "click",
              offset: 14,
              "show-arrow": false,
              "popper-class": "bpa-display-settings-popper",
              "onUpdate:modelValue": _cache[6] || (_cache[6] = ($event) => showDisplaySettings.value = $event)
            }, {
              reference: withCtx(() => [
                createVNode(_component_BpUiButton, {
                  class: "header-btn bpa-mobile-toolbar-btn bpa-mobile-card-fields-btn",
                  plain: "",
                  "icon-only": "",
                  "aria-label": unref(uiText).displaySettings.title
                }, {
                  default: withCtx(() => [..._cache[20] || (_cache[20] = [
                    createElementVNode("svg", {
                      width: "16",
                      height: "16",
                      viewBox: "0 0 16 16",
                      fill: "none",
                      xmlns: "http://www.w3.org/2000/svg"
                    }, [
                      createElementVNode("circle", {
                        cx: "8",
                        cy: "3.5",
                        r: "1.25",
                        fill: "currentColor"
                      }),
                      createElementVNode("circle", {
                        cx: "8",
                        cy: "8",
                        r: "1.25",
                        fill: "currentColor"
                      }),
                      createElementVNode("circle", {
                        cx: "8",
                        cy: "12.5",
                        r: "1.25",
                        fill: "currentColor"
                      })
                    ], -1)
                  ])]),
                  _: 1
                }, 8, ["aria-label"])
              ]),
              default: withCtx(() => [
                createVNode(BpaFilterPopover, {
                  "model-value": __props.displaySettings,
                  open: showDisplaySettings.value,
                  "onUpdate:modelValue": handleUpdateSettings,
                  onClose: _cache[5] || (_cache[5] = ($event) => showDisplaySettings.value = false)
                }, null, 8, ["model-value", "open"])
              ]),
              _: 1
            }, 8, ["model-value"])
          ])) : createCommentVNode("", true)
        ], 2),
        createElementVNode("div", _hoisted_7$4, [
          createVNode(_component_BpUiButton, {
            class: "nav-btn no-border",
            plain: "",
            "icon-only": "",
            onClick: _cache[7] || (_cache[7] = ($event) => emit("prev"))
          }, {
            default: withCtx(() => [..._cache[21] || (_cache[21] = [
              createElementVNode("svg", {
                width: "8",
                height: "14",
                viewBox: "0 0 8 14",
                fill: "none",
                xmlns: "http://www.w3.org/2000/svg"
              }, [
                createElementVNode("path", {
                  d: "M6.80005 12.8L0.800049 6.80005L6.80005 0.800049",
                  stroke: "currentColor",
                  "stroke-width": "1.6",
                  "stroke-linecap": "round",
                  "stroke-linejoin": "round"
                })
              ], -1)
            ])]),
            _: 1
          }),
          createElementVNode("h2", _hoisted_8$4, toDisplayString$1(__props.title), 1),
          createVNode(_component_BpUiButton, {
            class: "nav-btn no-border",
            plain: "",
            "icon-only": "",
            onClick: _cache[8] || (_cache[8] = ($event) => emit("next"))
          }, {
            default: withCtx(() => [..._cache[22] || (_cache[22] = [
              createElementVNode("svg", {
                width: "8",
                height: "14",
                viewBox: "0 0 8 14",
                fill: "none",
                xmlns: "http://www.w3.org/2000/svg"
              }, [
                createElementVNode("path", {
                  d: "M0.800049 12.8L6.80005 6.80005L0.800049 0.800049",
                  stroke: "currentColor",
                  "stroke-width": "1.6",
                  "stroke-linecap": "round",
                  "stroke-linejoin": "round"
                })
              ], -1)
            ])]),
            _: 1
          })
        ]),
        createElementVNode("div", _hoisted_9$4, [
          !props.isMobile ? (openBlock(), createBlock(_component_BpUiPopover, {
            key: 0,
            "model-value": showBookingFilters.value,
            placement: "bottom-end",
            width: bookingFiltersPopoverWidth.value,
            trigger: "click",
            offset: 14,
            persistent: true,
            "show-arrow": false,
            "popper-class": "bpa-booking-filters-popper",
            "onUpdate:modelValue": _cache[10] || (_cache[10] = ($event) => showBookingFilters.value = $event)
          }, {
            reference: withCtx(() => [
              createVNode(_component_BpUiButton, {
                class: normalizeClass(["header-btn bpa-calendar-filter-btn", { "bpa-calendar-filter-btn-mobile": props.isMobile }]),
                plain: "",
                "icon-only": props.isMobile,
                "aria-label": unref(uiText).header.filter
              }, {
                default: withCtx(() => [
                  _cache[23] || (_cache[23] = createElementVNode("svg", {
                    width: "16",
                    height: "14",
                    viewBox: "0 0 16 14",
                    fill: "none",
                    xmlns: "http://www.w3.org/2000/svg"
                  }, [
                    createElementVNode("path", {
                      d: "M12.5499 0.650024H2.7499C1.75996 0.650024 1.26498 0.650024 0.95744 0.924824C0.649902 1.19962 0.649902 1.6419 0.649902 2.52646V2.98634C0.649902 3.67823 0.649902 4.02418 0.831622 4.31097C1.01334 4.59775 1.34533 4.77574 2.00932 5.13172L4.04843 6.22495C4.49392 6.46375 4.71667 6.58322 4.87616 6.71508C5.2083 6.98968 5.41276 7.31235 5.50541 7.70815C5.5499 7.89815 5.5499 8.12055 5.5499 8.56528V10.3449C5.5499 10.9513 5.5499 11.2545 5.72625 11.4909C5.9026 11.7272 6.2158 11.8438 6.84224 12.0771C8.15726 12.5667 8.81477 12.8115 9.28237 12.5329C9.7499 12.2544 9.7499 11.6179 9.7499 10.3449V8.56528C9.7499 8.12055 9.7499 7.89815 9.79442 7.70815C9.88703 7.31235 10.0915 6.98968 10.4237 6.71508C10.5831 6.58322 10.8059 6.46375 11.2514 6.22495L13.2905 5.13172C13.9545 4.77574 14.2865 4.59775 14.4682 4.31097C14.6499 4.02418 14.6499 3.67823 14.6499 2.98634V2.52646C14.6499 1.6419 14.6499 1.19962 14.3424 0.924824C14.0348 0.650024 13.5398 0.650024 12.5499 0.650024Z",
                      stroke: "currentColor",
                      "stroke-width": "1.3"
                    })
                  ], -1)),
                  !props.isMobile ? (openBlock(), createElementBlock("span", _hoisted_10$4, toDisplayString$1(unref(uiText).header.filter), 1)) : createCommentVNode("", true)
                ]),
                _: 1
              }, 8, ["class", "icon-only", "aria-label"])
            ]),
            default: withCtx(() => [
              createVNode(BpaBookingFiltersDialog, {
                "model-value": showBookingFilters.value,
                filters: props.bookingFilters,
                config: props.bookingFilterConfig,
                "onUpdate:modelValue": _cache[9] || (_cache[9] = ($event) => showBookingFilters.value = $event),
                "onUpdate:filters": handleUpdateFilters
              }, null, 8, ["model-value", "filters", "config"])
            ]),
            _: 1
          }, 8, ["model-value", "width"])) : createCommentVNode("", true),
          showWeekTimelineToggle.value ? (openBlock(), createElementBlock("div", {
            key: 1,
            class: "week-layout-toggle",
            role: "group",
            "aria-label": unref(uiText).header.weekLayoutToggle
          }, [
            createElementVNode("button", {
              type: "button",
              class: normalizeClass(["week-layout-toggle-btn", { active: isTimelineViewActive.value }]),
              "aria-label": unref(uiText).header.timeline,
              "aria-pressed": isTimelineViewActive.value,
              onClick: _cache[11] || (_cache[11] = ($event) => handleWeekTimelineToggle("timeline"))
            }, [..._cache[24] || (_cache[24] = [
              createStaticVNode('<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" data-v-8a986cd5><rect x="2" y="2" width="4" height="4" rx="1" stroke="currentColor" stroke-width="1.5" data-v-8a986cd5></rect><rect x="2" y="10" width="4" height="4" rx="1" stroke="currentColor" stroke-width="1.5" data-v-8a986cd5></rect><path d="M9 4H14" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" data-v-8a986cd5></path><path d="M9 12H14" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" data-v-8a986cd5></path></svg>', 1)
            ])], 10, _hoisted_12$3),
            createElementVNode("button", {
              type: "button",
              class: normalizeClass(["week-layout-toggle-btn", { active: !isTimelineViewActive.value }]),
              "aria-label": unref(uiText).header.week,
              "aria-pressed": !isTimelineViewActive.value,
              onClick: _cache[12] || (_cache[12] = ($event) => handleWeekTimelineToggle("week"))
            }, [..._cache[25] || (_cache[25] = [
              createStaticVNode('<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" data-v-8a986cd5><rect x="14" y="2" width="4" height="4" rx="1" transform="rotate(90 14 2)" stroke="currentColor" stroke-width="1.5" data-v-8a986cd5></rect><rect x="14" y="10" width="4" height="4" rx="1" transform="rotate(90 14 10)" stroke="currentColor" stroke-width="1.5" data-v-8a986cd5></rect><rect x="6" y="2" width="4" height="4" rx="1" transform="rotate(90 6 2)" stroke="currentColor" stroke-width="1.5" data-v-8a986cd5></rect><rect x="6" y="10" width="4" height="4" rx="1" transform="rotate(90 6 10)" stroke="currentColor" stroke-width="1.5" data-v-8a986cd5></rect></svg>', 1)
            ])], 10, _hoisted_13$3)
          ], 8, _hoisted_11$3)) : createCommentVNode("", true),
          props.showAddAppointmentButton ? (openBlock(), createBlock(_component_BpUiButton, {
            key: 2,
            class: "add-btn",
            type: "primary",
            onClick: _cache[13] || (_cache[13] = ($event) => emit("addNew"))
          }, {
            default: withCtx(() => [
              createTextVNode(" + " + toDisplayString$1(unref(uiText).header.addNew), 1)
            ]),
            _: 1
          })) : createCommentVNode("", true),
          !props.isMobile ? (openBlock(), createBlock(_component_BpUiPopover, {
            key: 3,
            "model-value": showDisplaySettings.value,
            placement: "bottom-end",
            width: 304,
            trigger: "click",
            offset: 14,
            "show-arrow": false,
            "popper-class": "bpa-display-settings-popper",
            "onUpdate:modelValue": _cache[15] || (_cache[15] = ($event) => showDisplaySettings.value = $event)
          }, {
            reference: withCtx(() => [
              createVNode(_component_BpUiButton, {
                class: "no-border bpa-sub-filter-btn",
                plain: "",
                "icon-only": "",
                "aria-label": unref(uiText).displaySettings.title
              }, {
                default: withCtx(() => [..._cache[26] || (_cache[26] = [
                  createElementVNode("svg", {
                    width: "20",
                    height: "20",
                    viewBox: "0 0 20 20",
                    fill: "none",
                    xmlns: "http://www.w3.org/2000/svg"
                  }, [
                    createElementVNode("path", {
                      d: "M10 5.5C9.17155 5.5 8.5 4.82843 8.5 4C8.5 3.17157 9.17155 2.5 10 2.5C10.8284 2.5 11.5 3.17157 11.5 4C11.5 4.82843 10.8284 5.5 10 5.5Z",
                      fill: "currentColor"
                    }),
                    createElementVNode("path", {
                      d: "M10 11.5C9.17155 11.5 8.5 10.8285 8.5 10C8.5 9.17155 9.17155 8.5 10 8.5C10.8284 8.5 11.5 9.17155 11.5 10C11.5 10.8285 10.8284 11.5 10 11.5Z",
                      fill: "currentColor"
                    }),
                    createElementVNode("path", {
                      d: "M10 17.5C9.17155 17.5 8.5 16.8285 8.5 16C8.5 15.1715 9.17155 14.5 10 14.5C10.8284 14.5 11.5 15.1716 11.5 16C11.5 16.8285 10.8284 17.5 10 17.5Z",
                      fill: "currentColor"
                    })
                  ], -1)
                ])]),
                _: 1
              }, 8, ["aria-label"])
            ]),
            default: withCtx(() => [
              createVNode(BpaFilterPopover, {
                "model-value": __props.displaySettings,
                open: showDisplaySettings.value,
                "onUpdate:modelValue": handleUpdateSettings,
                onClose: _cache[14] || (_cache[14] = ($event) => showDisplaySettings.value = false)
              }, null, 8, ["model-value", "open"])
            ]),
            _: 1
          }, 8, ["model-value"])) : createCommentVNode("", true)
        ])
      ], 2);
    };
  }
});
const BpaCalendarHeader = /* @__PURE__ */ _export_sfc(_sfc_main$c, [["__scopeId", "data-v-8a986cd5"]]);
const _hoisted_1$9 = { class: "header-left" };
const _hoisted_2$7 = { class: "day-number" };
const _hoisted_3$7 = { class: "day-name" };
const _sfc_main$b = /* @__PURE__ */ defineComponent({
  __name: "BpaDayHeader",
  props: {
    column: {},
    expanded: { type: Boolean },
    hasOverflow: { type: Boolean },
    showExpandToggle: { type: Boolean, default: true }
  },
  emits: ["toggleExpand"],
  setup(__props, { emit: __emit }) {
    const emit = __emit;
    const uiText = useCalendarText();
    return (_ctx, _cache) => {
      const _component_BpUiButton = resolveComponent("BpUiButton");
      const _component_BpUiTooltip = resolveComponent("BpUiTooltip");
      return openBlock(), createElementBlock("div", {
        class: normalizeClass(["day-header", { "is-today": __props.column.isToday }])
      }, [
        createElementVNode("div", _hoisted_1$9, [
          createElementVNode("span", _hoisted_2$7, toDisplayString$1(__props.column.label), 1),
          createElementVNode("span", _hoisted_3$7, toDisplayString$1(__props.column.dayOfWeek), 1)
        ]),
        __props.showExpandToggle !== false ? (openBlock(), createBlock(_component_BpUiTooltip, {
          key: 0,
          content: __props.expanded ? unref(uiText).dayHeader.collapse : unref(uiText).dayHeader.expand,
          disabled: !__props.hasOverflow
        }, {
          default: withCtx(() => [
            createVNode(_component_BpUiButton, {
              class: normalizeClass(["expand-btn no-border", { rotated: __props.expanded }]),
              plain: "",
              "icon-only": "",
              disabled: !__props.hasOverflow,
              onClick: _cache[0] || (_cache[0] = ($event) => __props.hasOverflow && emit("toggleExpand"))
            }, {
              default: withCtx(() => [..._cache[1] || (_cache[1] = [
                createElementVNode("svg", {
                  width: "24",
                  height: "24",
                  viewBox: "0 0 24 24",
                  fill: "none",
                  xmlns: "http://www.w3.org/2000/svg"
                }, [
                  createElementVNode("path", {
                    d: "M9.71436 16L13.7144 12L9.71436 8",
                    stroke: "currentColor",
                    "stroke-width": "1.3",
                    "stroke-linecap": "round",
                    "stroke-linejoin": "round"
                  })
                ], -1)
              ])]),
              _: 1
            }, 8, ["disabled", "class"])
          ]),
          _: 1
        }, 8, ["content", "disabled"])) : createCommentVNode("", true)
      ], 2);
    };
  }
});
const BpaDayHeader = /* @__PURE__ */ _export_sfc(_sfc_main$b, [["__scopeId", "data-v-b7208575"]]);
const timeIconUrl = "data:image/svg+xml,%3csvg%20width='16'%20height='16'%20viewBox='0%200%2016%2016'%20fill='none'%20xmlns='http://www.w3.org/2000/svg'%3e%3cg%20clip-path='url(%23clip0_1319_33595)'%3e%3cpath%20d='M8.00016%2014.6673C11.6821%2014.6673%2014.6668%2011.6825%2014.6668%208.00065C14.6668%204.31875%2011.6821%201.33398%208.00016%201.33398C4.31826%201.33398%201.3335%204.31875%201.3335%208.00065C1.3335%2011.6825%204.31826%2014.6673%208.00016%2014.6673Z'%20stroke='white'%20stroke-opacity='0.85'/%3e%3cpath%20d='M8%205.33398V8.00065L9.66667%209.66732'%20stroke='white'%20stroke-opacity='0.85'%20stroke-linecap='round'%20stroke-linejoin='round'/%3e%3c/g%3e%3cdefs%3e%3cclipPath%20id='clip0_1319_33595'%3e%3crect%20width='16'%20height='16'%20fill='white'/%3e%3c/clipPath%3e%3c/defs%3e%3c/svg%3e";
const serviceIconUrl = "data:image/svg+xml,%3csvg%20width='16'%20height='16'%20viewBox='0%200%2016%2016'%20fill='none'%20xmlns='http://www.w3.org/2000/svg'%3e%3cpath%20d='M2.52933%208.0194C2.88744%206.22886%203.0665%205.33357%203.65814%204.75695C3.7675%204.65038%203.8858%204.5534%204.01174%204.46707C4.6932%204%205.60621%204%207.43224%204H8.5675C10.3936%204%2011.3066%204%2011.988%204.46707C12.114%204.5534%2012.2322%204.65038%2012.3416%204.75695C12.9332%205.33357%2013.1123%206.22886%2013.4704%208.0194C13.9846%2010.5901%2014.2416%2011.8754%2013.6498%2012.7862C13.5427%2012.9511%2013.4176%2013.1037%2013.277%2013.2411C12.4999%2014%2011.1891%2014%208.5675%2014H7.43224C4.81065%2014%203.49986%2014%202.72282%2013.2411C2.58212%2013.1037%202.45705%2012.9511%202.3499%2012.7862C1.75813%2011.8754%202.0152%2010.5901%202.52933%208.0194Z'%20stroke='white'%20stroke-opacity='0.85'/%3e%3cpath%20d='M9.99967%206.66732C10.3679%206.66732%2010.6663%206.36884%2010.6663%206.00065C10.6663%205.63246%2010.3679%205.33398%209.99967%205.33398C9.63148%205.33398%209.33301%205.63246%209.33301%206.00065C9.33301%206.36884%209.63148%206.66732%209.99967%206.66732Z'%20fill='white'%20fill-opacity='0.85'/%3e%3cpath%20d='M5.99967%206.66732C6.36786%206.66732%206.66634%206.36884%206.66634%206.00065C6.66634%205.63246%206.36786%205.33398%205.99967%205.33398C5.63148%205.33398%205.33301%205.63246%205.33301%206.00065C5.33301%206.36884%205.63148%206.66732%205.99967%206.66732Z'%20fill='white'%20fill-opacity='0.85'/%3e%3cpath%20d='M6%204.00065V3.33398C6%202.22942%206.8954%201.33398%208%201.33398C9.1046%201.33398%2010%202.22942%2010%203.33398V4.00065'%20stroke='white'%20stroke-opacity='0.85'%20stroke-linecap='round'/%3e%3c/svg%3e";
const locationIconUrl = "data:image/svg+xml,%3csvg%20width='16'%20height='16'%20viewBox='0%200%2016%2016'%20fill='none'%20xmlns='http://www.w3.org/2000/svg'%3e%3cpath%20d='M2.6665%206.76285C2.6665%203.76457%205.05432%201.33398%207.99984%201.33398C10.9454%201.33398%2013.3332%203.76457%2013.3332%206.76285C13.3332%209.73765%2011.631%2013.2089%208.9751%2014.4503C8.35604%2014.7397%207.64364%2014.7397%207.02457%2014.4503C4.36872%2013.2089%202.6665%209.73765%202.6665%206.76285Z'%20stroke='white'%20stroke-opacity='0.85'/%3e%3cpath%20d='M8%208.66797C9.10457%208.66797%2010%207.77254%2010%206.66797C10%205.5634%209.10457%204.66797%208%204.66797C6.89543%204.66797%206%205.5634%206%206.66797C6%207.77254%206.89543%208.66797%208%208.66797Z'%20stroke='white'%20stroke-opacity='0.85'/%3e%3c/svg%3e";
const emailIconUrl = "data:image/svg+xml,%3csvg%20width='16'%20height='16'%20viewBox='0%200%2016%2016'%20fill='none'%20xmlns='http://www.w3.org/2000/svg'%3e%3cpath%20d='M14.6668%204.66602L8.67283%208.48402C8.46942%208.60216%208.23839%208.66439%208.00316%208.66439C7.76794%208.66439%207.5369%208.60216%207.3335%208.48402L1.3335%204.66602'%20stroke='white'%20stroke-opacity='0.85'%20stroke-linecap='round'%20stroke-linejoin='round'/%3e%3cpath%20d='M13.3335%202.66602H2.66683C1.93045%202.66602%201.3335%203.26297%201.3335%203.99935V11.9993C1.3335%2012.7357%201.93045%2013.3327%202.66683%2013.3327H13.3335C14.0699%2013.3327%2014.6668%2012.7357%2014.6668%2011.9993V3.99935C14.6668%203.26297%2014.0699%202.66602%2013.3335%202.66602Z'%20stroke='white'%20stroke-opacity='0.85'%20stroke-linecap='round'%20stroke-linejoin='round'/%3e%3c/svg%3e";
const phoneIconUrl = "data:image/svg+xml,%3csvg%20width='15'%20height='15'%20viewBox='0%200%2015%2015'%20fill='none'%20xmlns='http://www.w3.org/2000/svg'%3e%3cpath%20d='M8.388%2010.212C8.52568%2010.2752%208.6808%2010.2897%208.82779%2010.253C8.97479%2010.2162%209.10489%2010.1305%209.19667%2010.01L9.43333%209.7C9.55753%209.5344%209.71857%209.4%209.90372%209.30743C10.0889%209.21486%2010.293%209.16667%2010.5%209.16667H12.5C12.8536%209.16667%2013.1928%209.30714%2013.4428%209.55719C13.6929%209.80724%2013.8333%2010.1464%2013.8333%2010.5V12.5C13.8333%2012.8536%2013.6929%2013.1928%2013.4428%2013.4428C13.1928%2013.6929%2012.8536%2013.8333%2012.5%2013.8333C9.3174%2013.8333%206.26515%2012.5691%204.01472%2010.3186C1.76428%208.06818%200.5%205.01593%200.5%201.83333C0.5%201.47971%200.640476%201.14057%200.890524%200.890524C1.14057%200.640476%201.47971%200.5%201.83333%200.5H3.83333C4.18696%200.5%204.52609%200.640476%204.77614%200.890524C5.02619%201.14057%205.16667%201.47971%205.16667%201.83333V3.83333C5.16667%204.04033%205.11847%204.24448%205.0259%204.42962C4.93333%204.61476%204.79893%204.7758%204.63333%204.9L4.32133%205.134C4.19894%205.22745%204.11268%205.36039%204.07719%205.51023C4.04171%205.66008%204.05919%205.81758%204.12667%205.956C5.03779%207.80658%206.53628%209.3032%208.388%2010.212Z'%20stroke='white'%20stroke-opacity='0.85'%20stroke-linecap='round'%20stroke-linejoin='round'/%3e%3c/svg%3e";
const priceIconUrl = "data:image/svg+xml,%3csvg%20width='16'%20height='16'%20viewBox='0%200%2016%2016'%20fill='none'%20xmlns='http://www.w3.org/2000/svg'%3e%3cg%20clip-path='url(%23clip0_1319_33625)'%3e%3cpath%20d='M8.00016%2011.334C9.10476%2011.334%2010.0002%2010.5878%2010.0002%209.66732C10.0002%208.74685%209.10476%208.00065%208.00016%208.00065C6.89556%208.00065%206.00016%207.25445%206.00016%206.33398C6.00016%205.41351%206.89556%204.66732%208.00016%204.66732M8.00016%2011.334C6.89556%2011.334%206.00016%2010.5878%206.00016%209.66732M8.00016%2011.334V12.0007M8.00016%204.00065V4.66732M8.00016%204.66732C9.10476%204.66732%2010.0002%205.41351%2010.0002%206.33398M14.6668%208.00065C14.6668%2011.6826%2011.6821%2014.6673%208.00016%2014.6673C4.31826%2014.6673%201.3335%2011.6826%201.3335%208.00065C1.3335%204.31875%204.31826%201.33398%208.00016%201.33398C11.6821%201.33398%2014.6668%204.31875%2014.6668%208.00065Z'%20stroke='white'%20stroke-opacity='0.85'%20stroke-linecap='round'/%3e%3c/g%3e%3cdefs%3e%3cclipPath%20id='clip0_1319_33625'%3e%3crect%20width='16'%20height='16'%20fill='white'/%3e%3c/clipPath%3e%3c/defs%3e%3c/svg%3e";
const _hoisted_1$8 = {
  key: 0,
  class: "event-drawer-topbar"
};
const _hoisted_2$6 = ["aria-label"];
const _hoisted_3$6 = { class: "popover-body" };
const _hoisted_4$5 = {
  key: 0,
  class: "popover-customer"
};
const _hoisted_5$4 = {
  key: 1,
  class: "popover-details"
};
const _hoisted_6$4 = {
  class: "popover-row-icon",
  "aria-hidden": "true"
};
const _hoisted_7$3 = ["src"];
const _hoisted_8$3 = {
  key: 1,
  width: "14",
  height: "14",
  viewBox: "0 0 14 14",
  fill: "none",
  xmlns: "http://www.w3.org/2000/svg"
};
const _hoisted_9$3 = {
  key: 2,
  width: "14",
  height: "14",
  viewBox: "0 0 14 14",
  fill: "none",
  xmlns: "http://www.w3.org/2000/svg"
};
const _hoisted_10$3 = {
  key: 3,
  width: "14",
  height: "14",
  viewBox: "0 0 14 14",
  fill: "none",
  xmlns: "http://www.w3.org/2000/svg"
};
const _hoisted_11$2 = { class: "popover-row-text" };
const _hoisted_12$2 = {
  key: 2,
  class: "popover-extra"
};
const _hoisted_13$2 = { class: "popover-row-text" };
const _hoisted_14$2 = {
  class: "status-pill-icon",
  "aria-hidden": "true"
};
const _hoisted_15$2 = ["src"];
const _hoisted_16$1 = {
  key: 1,
  width: "10",
  height: "10",
  viewBox: "0 0 10 10",
  fill: "currentColor"
};
const _hoisted_17$1 = {
  key: 0,
  class: "status-menu"
};
const _hoisted_18$1 = ["disabled", "onClick"];
const _hoisted_19$1 = { class: "status-menu-item-content" };
const _hoisted_20$1 = ["src"];
const _hoisted_21$1 = { class: "status-menu-item-label" };
const _hoisted_22$1 = {
  key: 0,
  width: "12",
  height: "12",
  viewBox: "0 0 12 12",
  fill: "none",
  stroke: "currentColor",
  "stroke-width": "1.8",
  "stroke-linecap": "round",
  "stroke-linejoin": "round"
};
const EDIT_EVENT_NAME = "bookingpress:appointment-popover-edit";
const RESCHEDULE_EVENT_NAME = "bookingpress:appointment-popover-reschedule";
const STATUS_CHANGE_EVENT_NAME = "bookingpress:appointment-popover-status-change";
const MOBILE_EVENT_DRAWER_BREAKPOINT = 591;
const TIMELINE_POPOVER_WIDTH = 284;
const TIMELINE_POPOVER_EDGE_GAP = 16;
const _sfc_main$a = /* @__PURE__ */ defineComponent({
  __name: "BpaEventPopover",
  props: {
    booking: {},
    anchorEl: {},
    config: {},
    statusOptions: {},
    mobile: { type: Boolean, default: false }
  },
  emits: ["close"],
  setup(__props, { emit: __emit }) {
    const props = __props;
    const emit = __emit;
    const uiText = useCalendarText();
    const metadata = computed(() => {
      const data = props.booking.metadata;
      return data ? data : null;
    });
    const bookingRecord = computed(() => isPlainObject2(props.booking) ? props.booking : null);
    const statusDropdownRef = ref(null);
    const currentStatusValue = ref(null);
    const showStatusMenu = ref(false);
    const drawerOpen = ref(true);
    const isCompactDrawerViewport = ref(false);
    const customerLabel = computed(() => "customerName" in props.booking ? props.booking.customerName : props.booking.title);
    const isPastAppointment = computed(() => resolveBookingIsPast(props.booking));
    function isPlainObject2(value) {
      return typeof value === "object" && value !== null && !Array.isArray(value);
    }
    function normalizeTextValue2(value) {
      if (typeof value === "string") {
        return value.trim();
      }
      if (typeof value === "number" || typeof value === "boolean") {
        return String(value);
      }
      if (Array.isArray(value)) {
        return value.map((entry) => normalizeTextValue2(entry)).filter(Boolean).join(", ");
      }
      return "";
    }
    function normalizeLookupText(value) {
      return value.trim().toLowerCase().replace(/[^a-z0-9]+/g, "");
    }
    const SERVICE_LOOKUP_KEYS = /* @__PURE__ */ new Set([
      "servicename",
      "serviceid",
      "serviceids",
      "servicenames",
      "servicelabel",
      "servicesdata"
    ]);
    const CATEGORY_LOOKUP_KEYS = /* @__PURE__ */ new Set([
      "category",
      "categoryname",
      "categoryid",
      "categoryids",
      "categorynames",
      "categorylabel",
      "servicecategoryname",
      "servicecategorynames",
      "servicecategorylabel",
      "servicecategoryid",
      "servicecategoryids"
    ]);
    function isServiceLookupKey(key) {
      return SERVICE_LOOKUP_KEYS.has(normalizeLookupText(key));
    }
    function isCategoryLookupKey(key) {
      return CATEGORY_LOOKUP_KEYS.has(normalizeLookupText(key));
    }
    const bookingServiceSummary = computed(() => {
      var _a, _b;
      return resolveBookingServiceSummary({
        serviceName: typeof props.booking.serviceName === "string" ? props.booking.serviceName : void 0,
        serviceId: "serviceId" in props.booking ? props.booking.serviceId : void 0,
        servicesData: ((_a = bookingRecord.value) == null ? void 0 : _a.servicesData) ?? ((_b = bookingRecord.value) == null ? void 0 : _b.services_data),
        metadata: metadata.value
      });
    });
    function getMetadataText(keyOrKeys, fallback = "") {
      var _a;
      const keys = Array.isArray(keyOrKeys) ? keyOrKeys : [keyOrKeys];
      const data = metadata.value;
      const formFields = Array.isArray(data == null ? void 0 : data.form_fields) ? data.form_fields : [];
      for (const key of keys) {
        if (isServiceLookupKey(key)) {
          const serviceLabel = bookingServiceSummary.value.serviceLabel;
          if (serviceLabel) {
            return serviceLabel;
          }
        }
        if (isCategoryLookupKey(key)) {
          const categoryLabel = bookingServiceSummary.value.categoryLabel;
          if (categoryLabel) {
            return categoryLabel;
          }
        }
        const normalizedKey = normalizeLookupText(key);
        const directValue = normalizeTextValue2((_a = bookingRecord.value) == null ? void 0 : _a[key]);
        if (directValue) {
          return directValue;
        }
        const metadataValue = normalizeTextValue2(data == null ? void 0 : data[key]);
        if (metadataValue) {
          return metadataValue;
        }
        if (!normalizedKey) {
          continue;
        }
        for (const entry of formFields) {
          if (!isPlainObject2(entry)) {
            continue;
          }
          const entryId = typeof entry.id === "string" || typeof entry.id === "number" ? normalizeLookupText(String(entry.id)) : "";
          const entryLabel = typeof entry.label === "string" ? normalizeLookupText(entry.label) : "";
          if (entryId === normalizedKey || entryLabel === normalizedKey) {
            const value = normalizeTextValue2(entry.value);
            if (value) {
              return value;
            }
          }
        }
      }
      return fallback;
    }
    const bookingRange = computed(() => {
      if ("start" in props.booking && "end" in props.booking) {
        return {
          start: props.booking.originalStart ?? props.booking.start,
          end: props.booking.originalEnd ?? props.booking.end
        };
      }
      return null;
    });
    const isDayServiceAppointment = computed(() => {
      var _a;
      return "startDate" in props.booking || normalizeBooleanLike$2((_a = metadata.value) == null ? void 0 : _a.isDayService) === true;
    });
    const bookingDateText = computed(() => {
      if ("startDate" in props.booking) {
        if (isSameDay(props.booking.startDate, props.booking.endDate)) {
          return formatFullDate(props.booking.startDate);
        }
        return `${formatFullDate(props.booking.startDate)} - ${formatFullDate(props.booking.endDate)}`;
      }
      if (!bookingRange.value) {
        return "";
      }
      if (isSameDay(bookingRange.value.start, bookingRange.value.end)) {
        return formatFullDate(bookingRange.value.start);
      }
      return `${formatFullDate(bookingRange.value.start)} - ${formatFullDate(bookingRange.value.end)}`;
    });
    const bookingTimeText = computed(() => {
      if (!bookingRange.value || isDayServiceAppointment.value) {
        return "";
      }
      return formatTimeRange(bookingRange.value.start, bookingRange.value.end);
    });
    const bookingServiceName = computed(() => {
      const serviceLabel = bookingServiceSummary.value.serviceLabel;
      if (serviceLabel) {
        return serviceLabel;
      }
      if ("serviceName" in props.booking && typeof props.booking.serviceName === "string") {
        const trimmed = props.booking.serviceName.trim();
        if (trimmed) {
          return trimmed;
        }
      }
      return getMetadataText("serviceName");
    });
    const bookingCustomerEmail = computed(() => getMetadataText([
      "customerEmail",
      "email",
      "emailAddress",
      "email address"
    ]));
    const bookingCustomerPhone = computed(() => getMetadataText([
      "customerPhone",
      "phone",
      "phoneNumber",
      "phone number",
      "mobile",
      "mobile number"
    ]));
    const bookingPartySize = computed(() => getMetadataText([
      "totalAttendees",
      "total_attendees",
      "total attendee",
      "total attendees",
      "numberOfPerson",
      "numberOfPeople",
      "numberOfPersons",
      "personCount",
      "guestCount",
      "partySize",
      "people",
      "number of person",
      "number of people"
    ]));
    function getDetailIconSrc(icon) {
      switch (icon) {
        case "clock":
          return timeIconUrl;
        case "service":
          return serviceIconUrl;
        case "staff":
          return staffIconUrl;
        case "location":
          return locationIconUrl;
        case "email":
          return emailIconUrl;
        case "phone":
          return phoneIconUrl;
        case "price":
          return priceIconUrl;
        default:
          return "";
      }
    }
    watch(
      () => "status" in props.booking ? props.booking.status : null,
      (value) => {
        currentStatusValue.value = value ?? null;
      },
      { immediate: true }
    );
    const detailRows = computed(() => {
      const rows = [];
      const staffMemberName = resolveBookingStaffLabel({
        staffMemberName: "staffMemberName" in props.booking ? props.booking.staffMemberName : void 0,
        staffMemberId: "staffMemberId" in props.booking ? props.booking.staffMemberId : void 0,
        StaffData: "StaffData" in props.booking ? props.booking.StaffData : void 0,
        isMultiStaff: "isMultiStaff" in props.booking ? props.booking.isMultiStaff : void 0,
        metadata: metadata.value
      });
      const hasExplicitLocationInput = !!getMetadataText([
        "locationId",
        "location_id",
        "locationName",
        "location_name"
      ]);
      const location = getMetadataText([
        "locationName",
        "location_name",
        "location",
        "locationId",
        "location_id",
        "branch",
        "branchName",
        "branch_name"
      ]);
      const price = getMetadataText("price");
      if (props.config.serviceName && bookingServiceName.value) {
        rows.push({
          key: "service",
          icon: "service",
          text: bookingServiceName.value
        });
      }
      if (props.config.dateTime) {
        const dateText = bookingDateText.value;
        const timeText = bookingTimeText.value;
        if (dateText) {
          rows.push({
            key: "date",
            icon: "calendar",
            text: dateText
          });
        }
        if (timeText) {
          rows.push({
            key: "time",
            icon: "clock",
            text: timeText
          });
        }
      }
      if (props.config.customerEmail && bookingCustomerEmail.value) {
        rows.push({
          key: "email",
          icon: "email",
          text: bookingCustomerEmail.value
        });
      }
      if (props.config.customerPhone && bookingCustomerPhone.value) {
        rows.push({
          key: "phone",
          icon: "phone",
          text: bookingCustomerPhone.value
        });
      }
      if (props.config.staffMemberName && staffMemberName) {
        rows.push({
          key: "staff",
          icon: "staff",
          text: staffMemberName
        });
      }
      if ((props.config.location || hasExplicitLocationInput) && location) {
        rows.push({
          key: "location",
          icon: "location",
          text: location
        });
      }
      if (props.config.numberOfPerson && bookingPartySize.value) {
        rows.push({
          key: "numberOfPerson",
          icon: "people",
          text: bookingPartySize.value
        });
      }
      if (props.config.price && price) {
        rows.push({
          key: "price",
          icon: "price",
          text: price
        });
      }
      return rows;
    });
    const additionalDetails = computed(() => props.config.additionalDetails.filter((detail) => detail.visible !== false).map((detail, index) => {
      const label = typeof detail.label === "string" && detail.label.trim() ? detail.label.trim() : typeof detail.id === "string" || typeof detail.id === "number" ? String(detail.id).trim() : "";
      const lookupKeys = [
        typeof detail.id === "string" || typeof detail.id === "number" ? String(detail.id) : "",
        typeof detail.label === "string" ? detail.label : ""
      ].filter(Boolean);
      const value = getMetadataText(lookupKeys);
      if (!label && !value) {
        return null;
      }
      return {
        key: `${label || value}-${index}`,
        text: value ? `${label || value}: ${value}` : label || value
      };
    }).filter((detail) => detail !== null));
    const currentStatusDefinition = computed(() => {
      var _a;
      return getStatusDefinition(currentStatusValue.value) ?? getStatusDefinition(((_a = currentStatusOption.value) == null ? void 0 : _a.label) ?? "");
    });
    const statusTone = computed(() => {
      var _a;
      return ((_a = currentStatusDefinition.value) == null ? void 0 : _a.tone) ?? DEFAULT_STATUS_TONE;
    });
    const currentStatusOption = computed(() => props.statusOptions.find((option) => String(option.value) === String(currentStatusValue.value ?? "")) ?? null);
    const currentStatusIconSrc = computed(() => {
      var _a, _b;
      return ((_a = currentStatusDefinition.value) == null ? void 0 : _a.iconSrc) ?? ((_b = currentStatusOption.value) == null ? void 0 : _b.iconSrc) ?? "";
    });
    const useDrawerOverlay = computed(() => props.mobile && isCompactDrawerViewport.value);
    const isStatusDropdownDisabled = computed(() => !props.config.enableStatusDropdown || !props.statusOptions.length);
    function isTimelineBookingAnchor() {
      return props.anchorEl.classList.contains("timeline-booking");
    }
    const popoverPlacement = computed(() => {
      if (!("start" in props.booking)) {
        return "bottom";
      }
      if (!isTimelineBookingAnchor() || typeof window === "undefined") {
        return "right-start";
      }
      const anchorRect = props.anchorEl.getBoundingClientRect();
      const availableRightSpace = window.innerWidth - anchorRect.right;
      const availableLeftSpace = anchorRect.left;
      const requiredSpace = TIMELINE_POPOVER_WIDTH + TIMELINE_POPOVER_EDGE_GAP;
      if (availableRightSpace >= requiredSpace) {
        return "right-start";
      }
      if (availableLeftSpace >= requiredSpace) {
        return "left-start";
      }
      return "auto-start";
    });
    const overlayBindings = computed(() => useDrawerOverlay.value ? {
      modelValue: drawerOpen.value,
      "onUpdate:modelValue": (value) => {
        drawerOpen.value = value;
      },
      class: "bpa-event-details-drawer",
      direction: "btt",
      size: "60vh",
      withHeader: false,
      appendToBody: true,
      destroyOnClose: true,
      modalClass: "bpa-event-details-drawer-modal",
      contentClass: "bpa-event-details-drawer__content",
      onClosed: () => emit("close")
    } : {
      modelValue: true,
      virtualRef: props.anchorEl,
      placement: popoverPlacement.value,
      trigger: "click",
      width: TIMELINE_POPOVER_WIDTH,
      offset: 10,
      persistent: true,
      showArrow: false,
      popperClass: "bpa-event-details-popper",
      popperOptions: isTimelineBookingAnchor() ? { strategy: "fixed" } : void 0
    });
    function syncDrawerViewport() {
      if (typeof window === "undefined") {
        isCompactDrawerViewport.value = false;
        return;
      }
      isCompactDrawerViewport.value = window.innerWidth <= MOBILE_EVENT_DRAWER_BREAKPOINT;
    }
    function requestClose() {
      if (useDrawerOverlay.value) {
        drawerOpen.value = false;
        return;
      }
      emit("close");
    }
    function dispatchPopoverAction(eventName, nativeEvent) {
      if (typeof window === "undefined") {
        return;
      }
      window.dispatchEvent(new CustomEvent(eventName, {
        detail: {
          booking: props.booking,
          anchorEl: props.anchorEl,
          nativeEvent
        }
      }));
    }
    function toggleStatusMenu() {
      if (isStatusDropdownDisabled.value) {
        return;
      }
      showStatusMenu.value = !showStatusMenu.value;
    }
    function closeStatusMenu() {
      showStatusMenu.value = false;
    }
    function isSameStatusValue(left, right) {
      return String(left ?? "") === String(right);
    }
    function onStatusSelect(option) {
      if (option.disabled || isStatusDropdownDisabled.value) {
        return;
      }
      const previousValue = currentStatusValue.value;
      if (isSameStatusValue(previousValue, option.value)) {
        closeStatusMenu();
        return;
      }
      currentStatusValue.value = option.value;
      closeStatusMenu();
      if (typeof window === "undefined") {
        return;
      }
      window.dispatchEvent(new CustomEvent(STATUS_CHANGE_EVENT_NAME, {
        detail: {
          booking: props.booking,
          anchorEl: props.anchorEl,
          previousValue,
          value: option.value,
          option
        }
      }));
    }
    function onEditClick(event) {
      closeStatusMenu();
      if (isPastAppointment.value || !props.config.enableEditAppointmentButton) {
        return;
      }
      dispatchPopoverAction(EDIT_EVENT_NAME, event);
    }
    function onRescheduleClick(event) {
      closeStatusMenu();
      if (isPastAppointment.value || !props.config.enableRescheduleAppointmentButton) {
        return;
      }
      dispatchPopoverAction(RESCHEDULE_EVENT_NAME, event);
    }
    let clickListenerTimeout = null;
    function onKeyDown(event) {
      if (event.key === "Escape") {
        if (showStatusMenu.value) {
          closeStatusMenu();
          return;
        }
        requestClose();
      }
    }
    function onDocumentClick(event) {
      const target = event.target;
      if (!target) {
        return;
      }
      if (statusDropdownRef.value && !statusDropdownRef.value.contains(target)) {
        closeStatusMenu();
      }
      if (useDrawerOverlay.value) {
        return;
      }
      if (target.closest(".bpa-event-details-popper")) {
        return;
      }
      if (props.anchorEl.contains(target)) {
        return;
      }
      if (target.closest(".booking-card") || target.closest(".timeline-booking")) {
        return;
      }
      requestClose();
    }
    onMounted(() => {
      syncDrawerViewport();
      document.addEventListener("keydown", onKeyDown);
      window.addEventListener("resize", syncDrawerViewport);
      clickListenerTimeout = window.setTimeout(() => {
        document.addEventListener("click", onDocumentClick, true);
      }, 0);
    });
    onUnmounted(() => {
      if (clickListenerTimeout) {
        window.clearTimeout(clickListenerTimeout);
        clickListenerTimeout = null;
      }
      document.removeEventListener("keydown", onKeyDown);
      document.removeEventListener("click", onDocumentClick, true);
      window.removeEventListener("resize", syncDrawerViewport);
    });
    watch(
      () => props.mobile,
      () => {
        syncDrawerViewport();
      },
      { immediate: true }
    );
    watch(
      () => [props.config.showStatusDropdown, isStatusDropdownDisabled.value],
      ([showStatusDropdown, dropdownDisabled]) => {
        if (!showStatusDropdown || dropdownDisabled) {
          closeStatusMenu();
        }
      },
      { immediate: true }
    );
    return (_ctx, _cache) => {
      const _component_BpUiButton = resolveComponent("BpUiButton");
      return openBlock(), createBlock(resolveDynamicComponent(useDrawerOverlay.value ? "BpUiDrawer" : "BpUiPopover"), normalizeProps(guardReactiveProps(overlayBindings.value)), {
        default: withCtx(() => [
          createElementVNode("div", {
            class: normalizeClass(["event-popover", { "is-mobile": useDrawerOverlay.value }])
          }, [
            useDrawerOverlay.value ? (openBlock(), createElementBlock("div", _hoisted_1$8, [
              _cache[1] || (_cache[1] = createElementVNode("div", {
                class: "event-drawer-handle",
                "aria-hidden": "true"
              }, null, -1)),
              createElementVNode("button", {
                type: "button",
                class: "event-drawer-close",
                "aria-label": unref(uiText).eventPopover.close,
                onClick: requestClose
              }, [..._cache[0] || (_cache[0] = [
                createElementVNode("svg", {
                  width: "18",
                  height: "18",
                  viewBox: "0 0 18 18",
                  fill: "none",
                  xmlns: "http://www.w3.org/2000/svg",
                  "aria-hidden": "true"
                }, [
                  createElementVNode("path", {
                    d: "M4.5 4.5L13.5 13.5M13.5 4.5L4.5 13.5",
                    stroke: "currentColor",
                    "stroke-width": "1.6",
                    "stroke-linecap": "round"
                  })
                ], -1)
              ])], 8, _hoisted_2$6)
            ])) : createCommentVNode("", true),
            createElementVNode("div", _hoisted_3$6, [
              __props.config.customerName ? (openBlock(), createElementBlock("div", _hoisted_4$5, toDisplayString$1(customerLabel.value), 1)) : createCommentVNode("", true),
              detailRows.value.length ? (openBlock(), createElementBlock("div", _hoisted_5$4, [
                (openBlock(true), createElementBlock(Fragment, null, renderList(detailRows.value, (row) => {
                  return openBlock(), createElementBlock("div", {
                    key: row.key,
                    class: "popover-row"
                  }, [
                    createElementVNode("span", _hoisted_6$4, [
                      getDetailIconSrc(row.icon) ? (openBlock(), createElementBlock("img", {
                        key: 0,
                        class: normalizeClass(["popover-row-icon-image", { "is-staff-icon": row.icon === "staff" }]),
                        src: getDetailIconSrc(row.icon),
                        alt: ""
                      }, null, 10, _hoisted_7$3)) : row.icon === "calendar" ? (openBlock(), createElementBlock("svg", _hoisted_8$3, [..._cache[2] || (_cache[2] = [
                        createElementVNode("rect", {
                          x: "2.25",
                          y: "2.75",
                          width: "9.5",
                          height: "9",
                          rx: "2",
                          stroke: "white",
                          "stroke-opacity": "0.85"
                        }, null, -1),
                        createElementVNode("path", {
                          d: "M4.5 1.75V4M9.5 1.75V4M2.25 5.25H11.75",
                          stroke: "white",
                          "stroke-opacity": "0.85",
                          "stroke-linecap": "round"
                        }, null, -1)
                      ])])) : row.icon === "people" ? (openBlock(), createElementBlock("svg", _hoisted_9$3, [..._cache[3] || (_cache[3] = [
                        createElementVNode("path", {
                          d: "M4.08333 6.4165C5.09985 6.4165 5.91667 5.59968 5.91667 4.58317C5.91667 3.56665 5.09985 2.74984 4.08333 2.74984C3.06682 2.74984 2.25 3.56665 2.25 4.58317C2.25 5.59968 3.06682 6.4165 4.08333 6.4165Z",
                          stroke: "white",
                          "stroke-opacity": "0.85"
                        }, null, -1),
                        createElementVNode("path", {
                          d: "M9.91667 6.4165C10.9332 6.4165 11.75 5.59968 11.75 4.58317C11.75 3.56665 10.9332 2.74984 9.91667 2.74984C8.90015 2.74984 8.08333 3.56665 8.08333 4.58317C8.08333 5.59968 8.90015 6.4165 9.91667 6.4165Z",
                          stroke: "white",
                          "stroke-opacity": "0.85"
                        }, null, -1),
                        createElementVNode("path", {
                          d: "M1.75 11.0832C1.75 9.27824 3.20314 7.83317 4.99992 7.83317C5.81254 7.83317 6.55567 8.1288 7.125 8.61756C7.69433 8.1288 8.43746 7.83317 9.25008 7.83317C11.0469 7.83317 12.5 9.27824 12.5 11.0832",
                          stroke: "white",
                          "stroke-opacity": "0.85",
                          "stroke-linecap": "round"
                        }, null, -1)
                      ])])) : (openBlock(), createElementBlock("svg", _hoisted_10$3, [..._cache[4] || (_cache[4] = [
                        createElementVNode("path", {
                          d: "M6.99996 9.9165C7.96648 9.9165 8.74996 9.26358 8.74996 8.45817C8.74996 7.65276 7.96648 6.99984 6.99996 6.99984C6.03343 6.99984 5.24996 6.34691 5.24996 5.5415C5.24996 4.73609 6.03343 4.08317 6.99996 4.08317M6.99996 9.9165C6.03343 9.9165 5.24996 9.26358 5.24996 8.45817M6.99996 9.9165V10.4998M6.99996 3.49984V4.08317M6.99996 4.08317C7.96648 4.08317 8.74996 4.73609 8.74996 5.5415M12.8333 6.99984C12.8333 10.2215 10.2216 12.8332 6.99996 12.8332C3.7783 12.8332 1.16663 10.2215 1.16663 6.99984C1.16663 3.77818 3.7783 1.1665 6.99996 1.1665C10.2216 1.1665 12.8333 3.77818 12.8333 6.99984Z",
                          stroke: "white",
                          "stroke-opacity": "0.85",
                          "stroke-linecap": "round"
                        }, null, -1)
                      ])]))
                    ]),
                    createElementVNode("span", _hoisted_11$2, toDisplayString$1(row.text), 1)
                  ]);
                }), 128))
              ])) : createCommentVNode("", true),
              additionalDetails.value.length ? (openBlock(), createElementBlock("div", _hoisted_12$2, [
                (openBlock(true), createElementBlock(Fragment, null, renderList(additionalDetails.value, (detail) => {
                  return openBlock(), createElementBlock("div", {
                    key: detail.key,
                    class: "popover-extra-row"
                  }, [
                    _cache[5] || (_cache[5] = createElementVNode("span", {
                      class: "popover-extra-marker",
                      "aria-hidden": "true"
                    }, [
                      createElementVNode("svg", {
                        width: "14",
                        height: "14",
                        viewBox: "0 0 14 14",
                        fill: "none",
                        xmlns: "http://www.w3.org/2000/svg"
                      }, [
                        createElementVNode("circle", {
                          cx: "7",
                          cy: "7",
                          r: "3",
                          fill: "white",
                          "fill-opacity": "0.85"
                        })
                      ])
                    ], -1)),
                    createElementVNode("span", _hoisted_13$2, toDisplayString$1(detail.text), 1)
                  ]);
                }), 128))
              ])) : createCommentVNode("", true)
            ]),
            createElementVNode("div", {
              class: normalizeClass(["popover-actions", { "is-mobile": useDrawerOverlay.value }])
            }, [
              __props.config.showStatusDropdown ? (openBlock(), createElementBlock("div", {
                key: 0,
                ref_key: "statusDropdownRef",
                ref: statusDropdownRef,
                class: "status-dropdown"
              }, [
                createVNode(_component_BpUiButton, {
                  class: normalizeClass(["action-btn action-btn-status", { "is-open": showStatusMenu.value, "is-disabled": isStatusDropdownDisabled.value }]),
                  plain: "",
                  "aria-label": unref(uiText).eventPopover.bookingStatus,
                  style: normalizeStyle({
                    "--status-fill": statusTone.value.fill,
                    "--status-bg": statusTone.value.background,
                    "--status-border": statusTone.value.border
                  }),
                  disabled: isStatusDropdownDisabled.value,
                  onClick: toggleStatusMenu
                }, {
                  default: withCtx(() => [
                    createElementVNode("span", _hoisted_14$2, [
                      currentStatusIconSrc.value ? (openBlock(), createElementBlock("img", {
                        key: 0,
                        class: "status-pill-icon-image",
                        src: currentStatusIconSrc.value,
                        alt: ""
                      }, null, 8, _hoisted_15$2)) : (openBlock(), createElementBlock("svg", _hoisted_16$1, [..._cache[6] || (_cache[6] = [
                        createElementVNode("circle", {
                          cx: "5",
                          cy: "5",
                          r: "2"
                        }, null, -1)
                      ])]))
                    ]),
                    _cache[7] || (_cache[7] = createElementVNode("svg", {
                      class: "status-chevron",
                      width: "12",
                      height: "12",
                      viewBox: "0 0 24 24",
                      fill: "none",
                      stroke: "currentColor",
                      "stroke-width": "2.5",
                      "stroke-linecap": "round",
                      "stroke-linejoin": "round"
                    }, [
                      createElementVNode("polyline", { points: "6 9 12 15 18 9" })
                    ], -1))
                  ]),
                  _: 1
                }, 8, ["aria-label", "class", "style", "disabled"]),
                showStatusMenu.value ? (openBlock(), createElementBlock("div", _hoisted_17$1, [
                  (openBlock(true), createElementBlock(Fragment, null, renderList(__props.statusOptions, (option) => {
                    return openBlock(), createElementBlock("button", {
                      key: String(option.value),
                      type: "button",
                      class: normalizeClass(["status-menu-item", {
                        "is-active": currentStatusOption.value && String(currentStatusOption.value.value) === String(option.value),
                        "is-disabled": option.disabled
                      }]),
                      disabled: option.disabled,
                      onClick: ($event) => onStatusSelect(option)
                    }, [
                      createElementVNode("span", _hoisted_19$1, [
                        option.iconSrc ? (openBlock(), createElementBlock("img", {
                          key: 0,
                          class: "status-menu-item-icon",
                          src: option.iconSrc,
                          alt: ""
                        }, null, 8, _hoisted_20$1)) : createCommentVNode("", true),
                        createElementVNode("span", _hoisted_21$1, toDisplayString$1(option.label), 1)
                      ]),
                      currentStatusOption.value && String(currentStatusOption.value.value) === String(option.value) ? (openBlock(), createElementBlock("svg", _hoisted_22$1, [..._cache[8] || (_cache[8] = [
                        createElementVNode("path", { d: "M2.5 6.2 4.9 8.5 9.5 3.8" }, null, -1)
                      ])])) : createCommentVNode("", true)
                    ], 10, _hoisted_18$1);
                  }), 128))
                ])) : createCommentVNode("", true)
              ], 512)) : createCommentVNode("", true),
              __props.config.showEditAppointmentButton ? (openBlock(), createBlock(_component_BpUiButton, {
                key: 1,
                class: "action-btn",
                plain: "",
                "icon-only": "",
                "aria-label": unref(uiText).eventPopover.editAppointment,
                disabled: isPastAppointment.value || !__props.config.enableEditAppointmentButton,
                onClick: onEditClick
              }, {
                default: withCtx(() => [..._cache[9] || (_cache[9] = [
                  createElementVNode("svg", {
                    width: "16",
                    height: "16",
                    viewBox: "0 0 16 16",
                    fill: "none",
                    xmlns: "http://www.w3.org/2000/svg"
                  }, [
                    createElementVNode("path", {
                      d: "M9.41604 3.2472L9.97217 2.69106C10.8935 1.76965 12.3875 1.76965 13.3089 2.69106C14.2303 3.61249 14.2303 5.10641 13.3089 6.02783L12.7528 6.58396M9.41604 3.2472C9.41604 3.2472 9.48549 4.42897 10.5283 5.47171C11.571 6.51445 12.7528 6.58396 12.7528 6.58396M9.41604 3.2472L4.30329 8.35993C3.95699 8.7062 3.78384 8.87938 3.63493 9.07033C3.45928 9.29548 3.30868 9.53915 3.1858 9.797C3.08164 10.0155 3.0042 10.2479 2.84933 10.7125L2.19309 12.6813M12.7528 6.58396L7.64005 11.6967C7.29378 12.043 7.1206 12.2161 6.92966 12.3651C6.70451 12.5407 6.46079 12.6913 6.20297 12.8142C5.9844 12.9183 5.7521 12.9958 5.28749 13.1506L3.31875 13.8069M2.19309 12.6813L2.03267 13.1625C1.95646 13.3912 2.01596 13.6432 2.18638 13.8136C2.3568 13.984 2.60886 14.0436 2.8375 13.9673L3.31875 13.8069M2.19309 12.6813L3.31875 13.8069",
                      stroke: "white",
                      "stroke-width": "1.2"
                    })
                  ], -1)
                ])]),
                _: 1
              }, 8, ["aria-label", "disabled"])) : createCommentVNode("", true),
              __props.config.showRescheduleAppointmentButton ? (openBlock(), createBlock(_component_BpUiButton, {
                key: 2,
                class: "action-btn",
                plain: "",
                "icon-only": "",
                "aria-label": unref(uiText).eventPopover.rescheduleAppointment,
                disabled: isPastAppointment.value || !__props.config.enableRescheduleAppointmentButton,
                onClick: onRescheduleClick
              }, {
                default: withCtx(() => [..._cache[10] || (_cache[10] = [
                  createElementVNode("svg", {
                    width: "16",
                    height: "16",
                    viewBox: "0 0 16 16",
                    fill: "none",
                    xmlns: "http://www.w3.org/2000/svg"
                  }, [
                    createElementVNode("path", {
                      d: "M8 5V7.33333L9.45833 8.79167",
                      stroke: "white",
                      "stroke-width": "1.2",
                      "stroke-linecap": "round",
                      "stroke-linejoin": "round"
                    }),
                    createElementVNode("path", {
                      d: "M2.00098 7.25015C2.18472 5.79214 2.89745 4.45223 4.00398 3.48453C5.11051 2.51684 6.53389 1.98866 8.00417 2.00018C8.97522 2.00122 9.93152 2.23767 10.7911 2.68925C11.6506 3.14084 12.3877 3.79408 12.9391 4.59294C13.4905 5.3918 13.8399 6.31243 13.9571 7.27586C14.0743 8.2393 13.9559 9.21677 13.6121 10.1244C13.2683 11.0321 12.7094 11.8429 11.9832 12.4872C11.257 13.1315 10.3852 13.5901 9.44273 13.8237C8.50022 14.0573 7.51509 14.0589 6.57183 13.8284C5.62857 13.5979 4.75534 13.1421 4.02705 12.5001L2.00098 10.7001",
                      stroke: "white",
                      "stroke-width": "1.2",
                      "stroke-linecap": "round",
                      "stroke-linejoin": "round"
                    }),
                    createElementVNode("path", {
                      d: "M5.75297 10.25H2.00098V14",
                      stroke: "white",
                      "stroke-width": "1.2",
                      "stroke-linecap": "round",
                      "stroke-linejoin": "round"
                    })
                  ], -1)
                ])]),
                _: 1
              }, 8, ["aria-label", "disabled"])) : createCommentVNode("", true)
            ], 2)
          ], 2)
        ]),
        _: 1
      }, 16);
    };
  }
});
const BpaEventPopover = /* @__PURE__ */ _export_sfc(_sfc_main$a, [["__scopeId", "data-v-4eef5e88"]]);
const dayServiceIconUrl = "data:image/svg+xml,%3csvg%20width='12'%20height='12'%20viewBox='0%200%2012%2012'%20fill='none'%20xmlns='http://www.w3.org/2000/svg'%3e%3cpath%20d='M7.49805%201.49963H10.4972V4.49882'%20stroke='black'%20stroke-opacity='0.75'%20stroke-width='0.999729'%20stroke-linecap='round'%20stroke-linejoin='round'/%3e%3cpath%20d='M4.49919%2010.4971H1.5V7.49792'%20stroke='black'%20stroke-opacity='0.75'%20stroke-width='0.999729'%20stroke-linecap='round'%20stroke-linejoin='round'/%3e%3cpath%20d='M10.4971%201.49963L6.99805%204.99869'%20stroke='black'%20stroke-opacity='0.75'%20stroke-width='0.999729'%20stroke-linecap='round'%20stroke-linejoin='round'/%3e%3cpath%20d='M1.5%2010.4971L4.99905%206.99805'%20stroke='black'%20stroke-opacity='0.75'%20stroke-width='0.999729'%20stroke-linecap='round'%20stroke-linejoin='round'/%3e%3c/svg%3e";
function computeMonthLayout(timeBookings, allDayBookings, monthDays, maxRowsPerWeek = 3) {
  const weeks = [];
  for (let i = 0; i < monthDays.length; i += 7) {
    const weekDays = monthDays.slice(i, i + 7);
    const weekStart = startOfDay$1(weekDays[0]).getTime();
    const weekEnd = startOfDay$1(weekDays[weekDays.length - 1]).getTime() + 864e5;
    const intersectingAllDay = allDayBookings.filter((b) => {
      const bStart = startOfDay$1(b.startDate).getTime();
      const bEnd = startOfDay$1(b.endDate).getTime() + 864e5;
      return bStart < weekEnd && bEnd > weekStart;
    }).map((b) => ({
      ...b,
      _isAllDay: true,
      _start: startOfDay$1(b.startDate),
      // Treat all-day end dates as inclusive so single-day day-service bookings
      // occupy their calendar day in month view.
      _end: new Date(startOfDay$1(b.endDate).getTime() + 864e5)
    }));
    const intersectingTime = timeBookings.filter((b) => {
      const bStart = b.start.getTime();
      const bEnd = b.end.getTime();
      return bStart < weekEnd && bEnd > weekStart;
    }).map((b) => ({ ...b, _isAllDay: false, _start: b.start, _end: b.end }));
    const allItems = [...intersectingAllDay, ...intersectingTime].sort((a, b) => {
      const aStartDay = startOfDay$1(a._start).getTime();
      const bStartDay = startOfDay$1(b._start).getTime();
      if (aStartDay !== bStartDay) return aStartDay - bStartDay;
      const aDuration = a._end.getTime() - a._start.getTime();
      const bDuration = b._end.getTime() - b._start.getTime();
      if (aDuration >= 864e5 && bDuration < 864e5) return -1;
      if (bDuration >= 864e5 && aDuration < 864e5) return 1;
      return a._start.getTime() - b._start.getTime();
    });
    const positions = [];
    const rowEndCols = [];
    const overflowCounts = [0, 0, 0, 0, 0, 0, 0];
    for (const item of allItems) {
      const bStart = item._start.getTime();
      const bEnd = item._end.getTime();
      let startCol = -1;
      let endCol = -1;
      for (let d = 0; d < weekDays.length; d++) {
        const dayStart = startOfDay$1(weekDays[d]).getTime();
        const dayEnd = dayStart + 864e5;
        if (bStart < dayEnd && bEnd > dayStart) {
          if (startCol === -1) startCol = d;
          endCol = d;
        }
      }
      if (startCol === -1) continue;
      let assignedRow = -1;
      for (let r = 0; r < rowEndCols.length; r++) {
        if (rowEndCols[r] < startCol) {
          assignedRow = r;
          break;
        }
      }
      if (assignedRow === -1) {
        assignedRow = rowEndCols.length;
        rowEndCols.push(-1);
      }
      rowEndCols[assignedRow] = endCol;
      if (assignedRow < maxRowsPerWeek) {
        positions.push({
          id: item.id,
          booking: item,
          isAllDay: item._isAllDay,
          startCol,
          spanCols: endCol - startCol + 1,
          row: assignedRow,
          isStart: bStart >= startOfDay$1(weekDays[startCol]).getTime(),
          isEnd: bEnd <= startOfDay$1(weekDays[endCol]).getTime() + 864e5
        });
      } else {
        for (let c = startCol; c <= endCol; c++) {
          overflowCounts[c]++;
        }
      }
    }
    weeks.push({
      weekIndex: i / 7,
      days: weekDays,
      positions,
      overflowCounts
    });
  }
  return weeks;
}
const CARD_COLORS = [
  { bg: "var(--bpa-pt-fuchsia-purple-alpha-08)", border: "var(--bpa-pt-fuchsia-purple)" },
  { bg: "var(--bpa-pt-secondary-orange-alpha-08)", border: "var(--bpa-pt-secondary-orange)" },
  { bg: "var(--bpa-pt-main-green-alpha-08)", border: "var(--bpa-sc-success)" },
  { bg: "var(--bpa-sc-danger-alpha-08)", border: "var(--bpa-sc-danger)" },
  { bg: "var(--bpa-pt-blue-alpha-08)", border: "var(--bpa-pt-blue)" },
  { bg: "var(--bpa-pt-royal-blue-alpha-08)", border: "var(--bpa-pt-royal-blue)" }
];
function getAllDayColor(index) {
  const { bg, border } = CARD_COLORS[index % CARD_COLORS.length];
  return { bg, border, text: "var(--bpa-dt-black-400)" };
}
const _hoisted_1$7 = { class: "month-day-cells" };
const _hoisted_2$5 = ["onClick", "onDblclick"];
const _hoisted_3$5 = ["onClick"];
const _hoisted_4$4 = { class: "month-events-layer" };
const _hoisted_5$3 = ["onPointerdown", "onClick"];
const _hoisted_6$3 = {
  key: 0,
  class: "month-event-pending-indicator",
  "aria-hidden": "true"
};
const _hoisted_7$2 = ["src"];
const _hoisted_8$2 = {
  key: 1,
  class: "event-icon"
};
const _hoisted_9$2 = ["title"];
const _hoisted_10$2 = ["title"];
const DEFAULT_VISIBLE_ROWS = 3;
const EXPANDED_VISIBLE_ROWS = 4;
const EVENT_ROW_HEIGHT = 32;
const EVENT_ROW_GAP = 4;
const EVENTS_TOP_OFFSET = 40;
const OVERFLOW_LABEL_RESERVED_SPACE = 20;
const DAY_CELL_MIN_HEIGHT = 185;
const DRAG_ACTIVATION_DISTANCE = 5;
const RECENT_DRAG_CLICK_SUPPRESSION_MS = 250;
const _sfc_main$9 = /* @__PURE__ */ defineComponent({
  __name: "BpaMonthView",
  props: {
    timeBookings: {},
    allDayBookings: {},
    monthDays: {},
    displaySettings: {},
    dragEnabled: { type: Boolean }
  },
  emits: ["card-click", "day-click", "day-dblclick", "more-click", "drag-begin", "drag-end"],
  setup(__props, { emit: __emit }) {
    const props = __props;
    const emit = __emit;
    const monthWeeksRef = ref(null);
    const monthWeeksHeight = ref(0);
    const monthScrollbarWidth = ref(0);
    const monthDayCellRefs = ref([]);
    const activeDrag = ref(null);
    const recentDraggedBookingId = ref(null);
    const recentDraggedAt = ref(0);
    const uiText = useCalendarText();
    let resizeObserver = null;
    onBeforeUpdate(() => {
      monthDayCellRefs.value = [];
    });
    const weekHeaders = computed(() => {
      if (props.monthDays.length < 7) return [];
      return props.monthDays.slice(0, 7).map((d) => {
        var _a;
        return new Intl.DateTimeFormat(((_a = i18n.global.locale) == null ? void 0 : _a.value) ?? "en", { weekday: "short" }).format(d).toUpperCase();
      });
    });
    const weekCount = computed(() => Math.max(Math.ceil(props.monthDays.length / 7), 1));
    const weekRowHeight = computed(() => {
      if (monthWeeksHeight.value <= 0) {
        return DAY_CELL_MIN_HEIGHT;
      }
      return Math.max(DAY_CELL_MIN_HEIGHT, monthWeeksHeight.value / weekCount.value);
    });
    const maxRows = computed(() => {
      const requiredHeightForFourRows = EVENTS_TOP_OFFSET + EXPANDED_VISIBLE_ROWS * EVENT_ROW_HEIGHT + (EXPANDED_VISIBLE_ROWS - 1) * EVENT_ROW_GAP + OVERFLOW_LABEL_RESERVED_SPACE;
      return weekRowHeight.value >= requiredHeightForFourRows ? EXPANDED_VISIBLE_ROWS : DEFAULT_VISIBLE_ROWS;
    });
    const monthLayout = computed(() => {
      return computeMonthLayout(props.timeBookings, props.allDayBookings, props.monthDays, maxRows.value);
    });
    const visibleSettings = computed(() => props.displaySettings.filter((setting) => setting.visible));
    const titleField = computed(() => visibleSettings.value[0] ?? null);
    function setMonthDayCellRef(index, el) {
      monthDayCellRefs.value[index] = el;
    }
    function isTimeBooking(booking) {
      return "start" in booking && "end" in booking;
    }
    function isBookingInPast(booking) {
      var _a;
      if (isTimeBooking(booking)) {
        return resolveBookingIsPast(booking);
      }
      if (typeof booking.isPast === "boolean") {
        return booking.isPast;
      }
      const metadataIsPast = (_a = booking.metadata) == null ? void 0 : _a.isPast;
      if (typeof metadataIsPast === "boolean") {
        return metadataIsPast;
      }
      return startOfDay$1(booking.endDate).getTime() < startOfDay$1(/* @__PURE__ */ new Date()).getTime();
    }
    function getBookingAnchorDate(booking) {
      return isTimeBooking(booking) ? new Date(booking.originalStart ?? booking.start) : new Date(booking.startDate);
    }
    function getMonthDayIndexForDate(date) {
      const targetTime = startOfDay$1(date).getTime();
      const match = props.monthDays.findIndex((day) => startOfDay$1(day).getTime() === targetTime);
      return match >= 0 ? match : 0;
    }
    function getNearestDayIndex(clientX, clientY, fallbackIndex) {
      const indexedCells = monthDayCellRefs.value.map((el, index) => ({ el, index })).filter((entry) => !!entry.el);
      if (!indexedCells.length) {
        return fallbackIndex;
      }
      const match = indexedCells.find(({ el }) => {
        const rect = el.getBoundingClientRect();
        return clientX >= rect.left && clientX <= rect.right && clientY >= rect.top && clientY <= rect.bottom;
      });
      if (match) {
        return match.index;
      }
      let nearest = indexedCells[0].index;
      let nearestDistance = Number.POSITIVE_INFINITY;
      indexedCells.forEach(({ el, index }) => {
        const rect = el.getBoundingClientRect();
        const centerX = rect.left + rect.width / 2;
        const centerY = rect.top + rect.height / 2;
        const distance = Math.hypot(clientX - centerX, clientY - centerY);
        if (distance < nearestDistance) {
          nearest = index;
          nearestDistance = distance;
        }
      });
      return nearest;
    }
    function onEventClick(booking, event) {
      event.stopPropagation();
      if (recentDraggedBookingId.value === booking.id && Date.now() - recentDraggedAt.value < RECENT_DRAG_CLICK_SUPPRESSION_MS) {
        return;
      }
      emit("card-click", booking, event.currentTarget);
    }
    function buildDragPreviewStyle(state, theme) {
      const dx = state.currentX - state.originX;
      const dy = state.currentY - state.originY;
      return {
        position: "fixed",
        left: `${state.initialCardRect.left}px`,
        top: `${state.initialCardRect.top}px`,
        width: `${state.initialCardRect.width}px`,
        height: `${state.initialCardRect.height}px`,
        transform: `translate(${dx}px, ${dy}px)`,
        backgroundColor: theme.bg,
        border: `1px solid ${theme.border}`,
        color: "#3D3F3F",
        borderRadius: "4px",
        zIndex: "1000",
        pointerEvents: "none",
        opacity: "0.96",
        boxShadow: "var(--bpa-app-shadow-md)"
      };
    }
    function getEventTheme(item) {
      const hash = item.id.split("").reduce((acc, char) => acc + char.charCodeAt(0), 0);
      const fallbackTheme = getAllDayColor(hash);
      return item.booking.theme ?? fallbackTheme;
    }
    function getEventStyle(item, rowIndex, segmentKey) {
      var _a, _b;
      const theme = getEventTheme(item);
      const isDraggingSegment = ((_a = activeDrag.value) == null ? void 0 : _a.segmentKey) === segmentKey;
      const isSameBooking = ((_b = activeDrag.value) == null ? void 0 : _b.bookingId) === item.id;
      if (isDraggingSegment && activeDrag.value) {
        return buildDragPreviewStyle(activeDrag.value, theme);
      }
      if (isSameBooking && activeDrag.value) {
        return {
          top: `${EVENTS_TOP_OFFSET + rowIndex * (EVENT_ROW_HEIGHT + EVENT_ROW_GAP)}px`,
          left: `calc(${item.startCol / 7 * 100}% + 4px)`,
          width: `calc(${item.spanCols / 7 * 100}% - 8px)`,
          backgroundColor: theme.bg,
          border: `1px solid ${theme.border}`,
          color: "#3D3F3F",
          borderRadius: "4px",
          height: `${EVENT_ROW_HEIGHT}px`,
          opacity: "0",
          pointerEvents: "none"
        };
      }
      return {
        top: `${EVENTS_TOP_OFFSET + rowIndex * (EVENT_ROW_HEIGHT + EVENT_ROW_GAP)}px`,
        left: `calc(${item.startCol / 7 * 100}% + 4px)`,
        width: `calc(${item.spanCols / 7 * 100}% - 8px)`,
        backgroundColor: theme.bg,
        border: `1px solid ${theme.border}`,
        color: "#3D3F3F",
        borderRadius: "4px",
        height: `${EVENT_ROW_HEIGHT}px`
      };
    }
    function getMonthSegmentKey(weekIndex, position) {
      return `${weekIndex}:${position.id}:${position.row}`;
    }
    function clearActiveDrag() {
      activeDrag.value = null;
    }
    function finishDrag(targetDate) {
      if (!activeDrag.value) {
        return;
      }
      const { booking } = activeDrag.value;
      recentDraggedBookingId.value = booking.id;
      recentDraggedAt.value = Date.now();
      clearActiveDrag();
      if (!targetDate) {
        return;
      }
      const anchorDate = getBookingAnchorDate(booking);
      if (isSameDay(anchorDate, targetDate)) {
        return;
      }
      emit("drag-end", booking, targetDate);
    }
    function onEventPointerDown(booking, event, segmentKey) {
      const canDrag = canDragMonthBooking(booking);
      if (!canDrag || activeDrag.value) {
        return;
      }
      const pointerStart = { x: event.clientX, y: event.clientY };
      const cardEl = event.currentTarget;
      const initialCardRect = cardEl.getBoundingClientRect();
      let dragStarted = false;
      const fallbackIndex = getMonthDayIndexForDate(getBookingAnchorDate(booking));
      let latestDayIndex = fallbackIndex;
      const cleanupPending = () => {
        document.removeEventListener("pointermove", onMove);
        document.removeEventListener("pointerup", onUp);
      };
      const onMove = (moveEvent) => {
        if (!canDrag) {
          return;
        }
        const dx = moveEvent.clientX - pointerStart.x;
        const dy = moveEvent.clientY - pointerStart.y;
        if (Math.sqrt(dx * dx + dy * dy) <= DRAG_ACTIVATION_DISTANCE) {
          return;
        }
        if (!dragStarted) {
          dragStarted = true;
          if (isBookingInPast(booking)) {
            return;
          }
          moveEvent.preventDefault();
          moveEvent.stopPropagation();
          latestDayIndex = getNearestDayIndex(moveEvent.clientX, moveEvent.clientY, fallbackIndex);
          activeDrag.value = {
            bookingId: booking.id,
            segmentKey,
            booking,
            originX: pointerStart.x,
            originY: pointerStart.y,
            currentX: moveEvent.clientX,
            currentY: moveEvent.clientY,
            initialCardRect: {
              left: initialCardRect.left,
              top: initialCardRect.top,
              width: initialCardRect.width,
              height: initialCardRect.height
            },
            previewDayIndex: latestDayIndex
          };
          emit("drag-begin", booking);
          return;
        }
        if (!activeDrag.value) {
          return;
        }
        latestDayIndex = getNearestDayIndex(moveEvent.clientX, moveEvent.clientY, latestDayIndex);
        activeDrag.value = {
          ...activeDrag.value,
          currentX: moveEvent.clientX,
          currentY: moveEvent.clientY,
          previewDayIndex: latestDayIndex
        };
        moveEvent.preventDefault();
        moveEvent.stopPropagation();
      };
      const onUp = (upEvent) => {
        var _a;
        cleanupPending();
        if (!dragStarted) {
          return;
        }
        const targetIndex = ((_a = activeDrag.value) == null ? void 0 : _a.previewDayIndex) ?? latestDayIndex;
        const targetDate = props.monthDays[targetIndex] ?? null;
        finishDrag(targetDate);
        upEvent.preventDefault();
        upEvent.stopPropagation();
      };
      document.addEventListener("pointermove", onMove);
      document.addEventListener("pointerup", onUp);
    }
    function isPlainObject2(value) {
      return typeof value === "object" && value !== null && !Array.isArray(value);
    }
    function normalizeMonthFieldId(fieldId) {
      return fieldId.trim().toLowerCase().replace(/[^a-z0-9]+/g, "");
    }
    function normalizeMonthFieldValue(value) {
      if (typeof value === "string") {
        return value.trim();
      }
      if (typeof value === "number" || typeof value === "boolean") {
        return String(value);
      }
      if (Array.isArray(value)) {
        return value.map((entry) => normalizeMonthFieldValue(entry)).filter(Boolean).join(", ");
      }
      return "";
    }
    function resolveMonthFormFieldValue(metadata, fieldId) {
      const formFieldEntries = metadata == null ? void 0 : metadata.form_fields;
      if (!Array.isArray(formFieldEntries)) {
        return "";
      }
      const normalizedFieldId = fieldId.trim().toLowerCase();
      const values = formFieldEntries.filter((entry) => isPlainObject2(entry)).filter((entry) => {
        const entryId = entry.id;
        const entryLabel = entry.label;
        return (typeof entryId === "string" || typeof entryId === "number") && String(entryId).trim().toLowerCase() === normalizedFieldId || typeof entryLabel === "string" && entryLabel.trim().toLowerCase() === normalizedFieldId;
      }).map((entry) => normalizeMonthFieldValue(entry.value)).filter(Boolean);
      return values.join(", ");
    }
    function formatMonthShortDateLabel(date) {
      var _a;
      return new Intl.DateTimeFormat(((_a = i18n.global.locale) == null ? void 0 : _a.value) ?? "en", {
        day: "numeric",
        month: "short"
      }).format(date);
    }
    function getServiceText(booking) {
      return resolveBookingServiceLabel({
        serviceName: booking.serviceName,
        serviceId: booking.serviceId,
        metadata: booking.metadata ?? null
      });
    }
    function getMonthDateTimeText(booking) {
      if (isTimeBooking(booking)) {
        return resolveTimeBookingDisplayFieldValue(booking, "dateTime");
      }
      if (isSameDay(booking.startDate, booking.endDate)) {
        return uiText.value.allDay.fullDay;
      }
      return `${formatMonthShortDateLabel(booking.startDate)} - ${formatMonthShortDateLabel(booking.endDate)}`;
    }
    function getMonthAllDayFieldValue(booking, fieldId) {
      var _a, _b, _c;
      switch (normalizeMonthFieldId(fieldId)) {
        case "customername":
          return normalizeMonthFieldValue(booking.customerName || booking.title);
        case "datetime":
          return getMonthDateTimeText(booking);
        case "servicename":
        case "serviceid":
        case "serviceids":
        case "servicelabel":
        case "servicesdata":
          return getServiceText(booking);
        case "staffmembername":
          return resolveBookingStaffLabel({
            staffMemberName: booking.staffMemberName,
            staffMemberId: booking.staffMemberId,
            StaffData: booking.StaffData,
            isMultiStaff: booking.isMultiStaff,
            metadata: booking.metadata ?? null
          }) || "—";
        case "location":
          return normalizeMonthFieldValue((_a = booking.metadata) == null ? void 0 : _a.location) || "—";
        case "price":
          return normalizeMonthFieldValue((_b = booking.metadata) == null ? void 0 : _b.price);
      }
      const directValue = normalizeMonthFieldValue(booking[fieldId]);
      if (directValue) {
        return directValue;
      }
      const formFieldValue = resolveMonthFormFieldValue(booking.metadata, fieldId);
      if (formFieldValue) {
        return formFieldValue;
      }
      return normalizeMonthFieldValue((_c = booking.metadata) == null ? void 0 : _c[fieldId]);
    }
    function getMonthBookingFieldValue(booking, fieldId) {
      if (isTimeBooking(booking)) {
        return resolveTimeBookingDisplayFieldValue(booking, fieldId);
      }
      const value = getMonthAllDayFieldValue(booking, fieldId);
      return value === "—" || value === "â€”" ? "" : value;
    }
    function getMonthEventTitle(booking) {
      if (titleField.value) {
        const value = getMonthBookingFieldValue(booking, titleField.value.id);
        if (value) {
          return value;
        }
      }
      return booking.title || getServiceText(booking) || booking.customerName || "";
    }
    function getMonthEventDetailTexts(booking) {
      return visibleSettings.value.slice(1).map((setting) => getMonthBookingFieldValue(booking, setting.id)).filter((value) => !!value);
    }
    function getMonthEventInlineParts(booking) {
      const parts = [];
      const titleText = getMonthEventTitle(booking);
      if (titleText) {
        parts.push({
          key: "title",
          text: titleText,
          variant: "title"
        });
      }
      getMonthEventDetailTexts(booking).forEach((text, index) => {
        parts.push({
          key: `detail-${index}`,
          text,
          variant: "detail"
        });
      });
      return parts;
    }
    function getMonthEventInlineText(booking) {
      return getMonthEventInlineParts(booking).map((part) => part.text).join(" | ");
    }
    function isPendingStatusBooking(booking) {
      var _a, _b;
      const statusDefinition = getStatusDefinition(booking.status) ?? getStatusDefinition((_a = booking.metadata) == null ? void 0 : _a.status) ?? getStatusDefinition((_b = booking.metadata) == null ? void 0 : _b.statusLabel);
      return (statusDefinition == null ? void 0 : statusDefinition.key) === "pending";
    }
    function isDayServiceBooking(booking) {
      var _a;
      const value = (_a = booking.metadata) == null ? void 0 : _a.isDayService;
      return value === true || value === 1 || value === "1" || value === "true";
    }
    function isMultiServiceBooking(booking) {
      var _a, _b, _c;
      return resolveBookingServiceSummary({
        serviceName: booking.serviceName,
        serviceId: booking.serviceId,
        servicesData: ((_a = booking.metadata) == null ? void 0 : _a.servicesData) ?? ((_b = booking.metadata) == null ? void 0 : _b.services_data),
        isMultiService: (_c = booking.metadata) == null ? void 0 : _c.isMultiService,
        metadata: booking.metadata ?? null
      }).isMultiService;
    }
    function canDragMonthBooking(booking) {
      return !!props.dragEnabled && !isBookingInPast(booking) && !isMultiServiceBooking(booking);
    }
    function updateMonthMetrics() {
      const weeksEl = monthWeeksRef.value;
      monthWeeksHeight.value = (weeksEl == null ? void 0 : weeksEl.clientHeight) ?? 0;
      monthScrollbarWidth.value = weeksEl ? Math.max(weeksEl.offsetWidth - weeksEl.clientWidth, 0) : 0;
    }
    onMounted(() => {
      updateMonthMetrics();
      if (typeof ResizeObserver !== "undefined") {
        resizeObserver = new ResizeObserver(() => updateMonthMetrics());
        if (monthWeeksRef.value) {
          resizeObserver.observe(monthWeeksRef.value);
        }
        return;
      }
      window.addEventListener("resize", updateMonthMetrics);
    });
    onUnmounted(() => {
      resizeObserver == null ? void 0 : resizeObserver.disconnect();
      resizeObserver = null;
      window.removeEventListener("resize", updateMonthMetrics);
    });
    return (_ctx, _cache) => {
      return openBlock(), createElementBlock("div", {
        class: normalizeClass(["bpa-month-view", { "is-dragging": !!activeDrag.value }])
      }, [
        createElementVNode("div", {
          class: "month-header-row",
          style: normalizeStyle({ paddingInlineEnd: `${monthScrollbarWidth.value}px` })
        }, [
          (openBlock(true), createElementBlock(Fragment, null, renderList(weekHeaders.value, (day) => {
            return openBlock(), createElementBlock("div", {
              key: day,
              class: "month-header-cell"
            }, toDisplayString$1(day), 1);
          }), 128))
        ], 4),
        createElementVNode("div", {
          ref_key: "monthWeeksRef",
          ref: monthWeeksRef,
          class: "month-weeks"
        }, [
          (openBlock(true), createElementBlock(Fragment, null, renderList(monthLayout.value, (week) => {
            return openBlock(), createElementBlock("div", {
              key: week.weekIndex,
              class: "month-week-row",
              style: normalizeStyle({ height: `${weekRowHeight.value}px`, minHeight: `${weekRowHeight.value}px` })
            }, [
              createElementVNode("div", _hoisted_1$7, [
                (openBlock(true), createElementBlock(Fragment, null, renderList(week.days, (day, dIndex) => {
                  var _a, _b;
                  return openBlock(), createElementBlock("div", {
                    key: day.toISOString(),
                    ref_for: true,
                    ref: (el) => setMonthDayCellRef(week.weekIndex * 7 + dIndex, el),
                    class: normalizeClass(["month-day-cell", {
                      "is-today": unref(isToday)(day),
                      "is-outside-month": day.getMonth() !== ((_a = __props.monthDays[15]) == null ? void 0 : _a.getMonth()),
                      "is-drop-target": ((_b = activeDrag.value) == null ? void 0 : _b.previewDayIndex) === week.weekIndex * 7 + dIndex
                    }]),
                    onClick: ($event) => emit("day-click", day),
                    onDblclick: ($event) => emit("day-dblclick", day)
                  }, [
                    createElementVNode("span", {
                      class: normalizeClass(["day-number", { "today-number": unref(isToday)(day) }])
                    }, toDisplayString$1(day.getDate()), 3),
                    week.overflowCounts[dIndex] > 0 ? (openBlock(), createElementBlock("button", {
                      key: 0,
                      type: "button",
                      class: "overflow-indicator",
                      onClick: withModifiers(($event) => emit("more-click", day), ["stop"])
                    }, " +" + toDisplayString$1(week.overflowCounts[dIndex]) + " " + toDisplayString$1(unref(uiText).month.more), 9, _hoisted_3$5)) : createCommentVNode("", true)
                  ], 42, _hoisted_2$5);
                }), 128))
              ]),
              createElementVNode("div", _hoisted_4$4, [
                (openBlock(true), createElementBlock(Fragment, null, renderList(week.positions, (pos) => {
                  var _a;
                  return openBlock(), createElementBlock("div", {
                    key: getMonthSegmentKey(week.weekIndex, pos),
                    class: normalizeClass(["month-event-bar", {
                      "is-all-day": pos.isAllDay,
                      "is-time": !pos.isAllDay,
                      "is-dragging": ((_a = activeDrag.value) == null ? void 0 : _a.segmentKey) === getMonthSegmentKey(week.weekIndex, pos)
                    }]),
                    style: normalizeStyle(getEventStyle(pos, pos.row, getMonthSegmentKey(week.weekIndex, pos))),
                    onPointerdown: ($event) => onEventPointerDown(pos.booking, $event, getMonthSegmentKey(week.weekIndex, pos)),
                    onClick: ($event) => onEventClick(pos.booking, $event)
                  }, [
                    isPendingStatusBooking(pos.booking) ? (openBlock(), createElementBlock("span", _hoisted_6$3)) : createCommentVNode("", true),
                    pos.isAllDay || pos.booking._end.getTime() - pos.booking._start.getTime() >= 864e5 ? (openBlock(), createElementBlock(Fragment, { key: 1 }, [
                      isDayServiceBooking(pos.booking) ? (openBlock(), createElementBlock("img", {
                        key: 0,
                        class: "event-icon-image",
                        src: unref(dayServiceIconUrl),
                        alt: "",
                        "aria-hidden": "true"
                      }, null, 8, _hoisted_7$2)) : (openBlock(), createElementBlock("span", _hoisted_8$2, "↗")),
                      createElementVNode("span", {
                        class: "month-event-content",
                        title: getMonthEventInlineText(pos.booking)
                      }, toDisplayString$1(getMonthEventInlineText(pos.booking)), 9, _hoisted_9$2)
                    ], 64)) : (openBlock(), createElementBlock("span", {
                      key: 2,
                      class: "month-event-content",
                      title: getMonthEventInlineText(pos.booking)
                    }, toDisplayString$1(getMonthEventInlineText(pos.booking)), 9, _hoisted_10$2)),
                    createVNode(BpaBookingIndicators, {
                      booking: pos.booking,
                      size: "compact",
                      style: normalizeStyle({ "--booking-indicator-text-color": getEventTheme(pos).text })
                    }, null, 8, ["booking", "style"])
                  ], 46, _hoisted_5$3);
                }), 128))
              ])
            ], 4);
          }), 128))
        ], 512)
      ], 2);
    };
  }
});
const BpaMonthView = /* @__PURE__ */ _export_sfc(_sfc_main$9, [["__scopeId", "data-v-9979b650"]]);
const _hoisted_1$6 = { class: "column-wrapper" };
const _sfc_main$8 = /* @__PURE__ */ defineComponent({
  __name: "BpaTimeColumn",
  props: {
    dayIndex: {},
    positionedBookings: {},
    gridConfig: {},
    isToday: { type: Boolean },
    dragBookingId: {},
    previewDayIndex: {},
    previewLayout: {},
    originalDayIndex: {},
    originalPreviewLayout: {},
    dragTransform: {},
    previewInOverlay: { type: Boolean },
    expanded: { type: Boolean },
    requiredWidth: {},
    cappedWidth: {},
    flexStyle: {},
    dragInteractionType: {},
    displaySettings: {},
    dragEnabled: { type: Boolean },
    resizeEnabled: { type: Boolean }
  },
  emits: ["dragStart", "resizeTopStart", "resizeBottomStart", "cardClick"],
  setup(__props, { emit: __emit }) {
    const props = __props;
    const emit = __emit;
    const totalHeight = computed(
      () => getGridTotalMinutes(props.gridConfig) * props.gridConfig.hourHeight / 60
    );
    const gridLines = computed(() => {
      const startMinute = getGridStartMinute(props.gridConfig);
      const pixelsPerMinute = props.gridConfig.hourHeight / 60;
      const lines = [];
      for (const minute of getGridLineMinutes(props.gridConfig)) {
        lines.push({
          top: (minute - startMinute) * pixelsPerMinute,
          isHour: minute === startMinute || minute % 60 === 0
        });
      }
      return lines;
    });
    const visibleBookings = computed(() => {
      var _a;
      const isResize = (_a = props.dragInteractionType) == null ? void 0 : _a.startsWith("resize");
      return props.positionedBookings.filter(
        (pb) => isResize || pb.booking.id !== props.dragBookingId
      );
    });
    function canResizeBooking(booking) {
      return !!props.resizeEnabled && resolveTimeBookingResizeEnabled(booking);
    }
    function onResizeTopStart(booking, e, dayIndex) {
      if (!canResizeBooking(booking)) {
        return;
      }
      emit("resizeTopStart", booking, e, dayIndex);
    }
    function onResizeBottomStart(booking, e, dayIndex) {
      if (!canResizeBooking(booking)) {
        return;
      }
      emit("resizeBottomStart", booking, e, dayIndex);
    }
    const needsInternalScroll = computed(() => {
      return props.requiredWidth > Math.max(props.cappedWidth, props.gridConfig.columnMinWidth);
    });
    const innerWidth = computed(() => `${props.requiredWidth}px`);
    const bookingsRef = ref(null);
    const stickyBarRef = ref(null);
    let syncing = false;
    function onBookingsScroll() {
      if (syncing) return;
      syncing = true;
      if (bookingsRef.value && stickyBarRef.value) {
        stickyBarRef.value.scrollLeft = bookingsRef.value.scrollLeft;
      }
      syncing = false;
    }
    function onStickyBarScroll() {
      if (syncing) return;
      syncing = true;
      if (stickyBarRef.value && bookingsRef.value) {
        bookingsRef.value.scrollLeft = stickyBarRef.value.scrollLeft;
      }
      syncing = false;
    }
    const nowLineTop = ref(0);
    let nowInterval = null;
    function updateNowLine() {
      const now2 = /* @__PURE__ */ new Date();
      const minutes = timeToMinutesOfDay(now2) - getGridStartMinute(props.gridConfig);
      nowLineTop.value = minutes * props.gridConfig.hourHeight / 60;
    }
    onMounted(() => {
      if (props.isToday) {
        updateNowLine();
        nowInterval = setInterval(updateNowLine, 6e4);
      }
    });
    onUnmounted(() => {
      if (nowInterval) clearInterval(nowInterval);
    });
    return (_ctx, _cache) => {
      return openBlock(), createElementBlock("div", {
        class: "time-column-container",
        style: normalizeStyle(__props.flexStyle)
      }, [
        createElementVNode("div", _hoisted_1$6, [
          createElementVNode("div", {
            class: normalizeClass(["time-column", { "is-today": __props.isToday }])
          }, [
            createElementVNode("div", {
              class: "gridlines-layer",
              style: normalizeStyle({ height: `${totalHeight.value}px` })
            }, [
              (openBlock(true), createElementBlock(Fragment, null, renderList(gridLines.value, (line, i) => {
                return openBlock(), createElementBlock("div", {
                  key: i,
                  class: normalizeClass(["grid-line", { "hour-line": line.isHour, "half-hour-line": !line.isHour }]),
                  style: normalizeStyle({ top: `${line.top}px` })
                }, null, 6);
              }), 128))
            ], 4),
            __props.isToday ? (openBlock(), createElementBlock("div", {
              key: 0,
              class: "now-indicator",
              style: normalizeStyle({ top: `${nowLineTop.value}px` })
            }, [..._cache[1] || (_cache[1] = [
              createElementVNode("div", { class: "now-dot" }, null, -1),
              createElementVNode("div", { class: "now-line" }, null, -1)
            ])], 4)) : createCommentVNode("", true),
            createElementVNode("div", {
              ref_key: "bookingsRef",
              ref: bookingsRef,
              class: normalizeClass(["bookings-scroll-wrapper", { scrollable: needsInternalScroll.value }]),
              style: normalizeStyle({ height: `${totalHeight.value}px` }),
              onScroll: onBookingsScroll
            }, [
              createElementVNode("div", {
                class: "bookings-layer",
                style: normalizeStyle({ width: innerWidth.value, height: `${totalHeight.value}px` })
              }, [
                (openBlock(true), createElementBlock(Fragment, null, renderList(visibleBookings.value, (pb) => {
                  return openBlock(), createBlock(BpaBookingCard, {
                    key: pb.booking.sliceKey ?? pb.booking.id,
                    positioned: pb,
                    "is-dragging": false,
                    "display-settings": __props.displaySettings,
                    "drag-enabled": __props.dragEnabled,
                    "resize-enabled": __props.resizeEnabled,
                    onDragStart: (e, cardEl) => emit("dragStart", pb.booking, e, __props.dayIndex, cardEl),
                    onResizeTopStart: ($event) => onResizeTopStart(pb.booking, $event, __props.dayIndex),
                    onResizeBottomStart: ($event) => onResizeBottomStart(pb.booking, $event, __props.dayIndex),
                    onCardClick: _cache[0] || (_cache[0] = (booking, el) => emit("cardClick", booking, el))
                  }, null, 8, ["positioned", "display-settings", "drag-enabled", "resize-enabled", "onDragStart", "onResizeTopStart", "onResizeBottomStart"]);
                }), 128)),
                !__props.previewInOverlay && __props.previewLayout && __props.dragInteractionType !== "none" && __props.dayIndex === __props.previewDayIndex ? (openBlock(), createBlock(BpaBookingCard, {
                  key: 0,
                  positioned: __props.previewLayout,
                  "is-dragging": true,
                  "is-preview": true,
                  "display-settings": __props.displaySettings,
                  "resize-enabled": __props.resizeEnabled
                }, null, 8, ["positioned", "display-settings", "resize-enabled"])) : createCommentVNode("", true)
              ], 4)
            ], 38)
          ], 2)
        ]),
        needsInternalScroll.value ? (openBlock(), createElementBlock("div", {
          key: 0,
          ref_key: "stickyBarRef",
          ref: stickyBarRef,
          class: "sticky-scrollbar",
          onScrollPassive: onStickyBarScroll
        }, [
          createElementVNode("div", {
            style: normalizeStyle({ width: innerWidth.value, height: "1px" })
          }, null, 4)
        ], 544)) : createCommentVNode("", true)
      ], 4);
    };
  }
});
const BpaTimeColumn = /* @__PURE__ */ _export_sfc(_sfc_main$8, [["__scopeId", "data-v-0f2a0497"]]);
const _sfc_main$7 = /* @__PURE__ */ defineComponent({
  __name: "BpaTimeGutter",
  props: {
    gridConfig: {}
  },
  setup(__props) {
    const props = __props;
    const hours = computed(() => {
      const startMinute = getGridStartMinute(props.gridConfig);
      const h2 = [];
      const pixelsPerMinute = props.gridConfig.hourHeight / 60;
      const markers = getHourMarkerMinutes(props.gridConfig);
      markers.forEach((minute, index) => {
        h2.push({
          minute,
          label: formatMinuteLabel(minute),
          top: (minute - startMinute) * pixelsPerMinute,
          isFirst: index === 0,
          isLast: index === markers.length - 1
        });
      });
      return h2;
    });
    const totalHeight = computed(() => {
      return getGridTotalMinutes(props.gridConfig) * props.gridConfig.hourHeight / 60;
    });
    return (_ctx, _cache) => {
      return openBlock(), createElementBlock("div", {
        class: "time-gutter",
        style: normalizeStyle({ height: `${totalHeight.value}px` })
      }, [
        (openBlock(true), createElementBlock(Fragment, null, renderList(hours.value, (h2) => {
          return openBlock(), createElementBlock("div", {
            key: h2.minute,
            class: normalizeClass(["hour-label", { "hour-label-first": h2.isFirst, "hour-label-last": h2.isLast }]),
            style: normalizeStyle({ top: `${h2.top}px` })
          }, toDisplayString$1(h2.label), 7);
        }), 128))
      ], 4);
    };
  }
});
const BpaTimeGutter = /* @__PURE__ */ _export_sfc(_sfc_main$7, [["__scopeId", "data-v-58c1c7b0"]]);
const _hoisted_1$5 = { class: "timeline-header" };
const _hoisted_2$4 = { class: "hour-marker-label" };
const _hoisted_3$4 = { class: "timeline-body" };
const _hoisted_4$3 = { class: "dl-header" };
const _hoisted_5$2 = { class: "dl-content" };
const _hoisted_6$2 = { class: "dl-day" };
const _hoisted_7$1 = { class: "dl-full" };
const _hoisted_8$1 = ["disabled", "onClick", "title"];
const _hoisted_9$1 = {
  key: 0,
  width: "12",
  height: "12",
  viewBox: "0 0 24 24",
  fill: "none",
  stroke: "currentColor",
  "stroke-width": "2.5",
  "stroke-linecap": "round",
  "stroke-linejoin": "round"
};
const _hoisted_10$1 = {
  key: 1,
  width: "12",
  height: "12",
  viewBox: "0 0 24 24",
  fill: "none",
  stroke: "currentColor",
  "stroke-width": "2.5",
  "stroke-linecap": "round",
  "stroke-linejoin": "round"
};
const _hoisted_11$1 = ["onPointerdown"];
const _hoisted_12$1 = ["onPointerdown"];
const _hoisted_13$1 = { class: "tl-card-header" };
const _hoisted_14$1 = ["src"];
const _hoisted_15$1 = {
  class: "tl-name",
  style: { color: "var(--title-color)" }
};
const _hoisted_16 = { class: "tl-fields-container" };
const _hoisted_17 = {
  key: 0,
  width: "12",
  height: "12",
  viewBox: "0 0 24 24",
  fill: "none",
  stroke: "currentColor",
  "stroke-width": "2",
  "stroke-linecap": "round",
  "stroke-linejoin": "round"
};
const _hoisted_18 = {
  key: 1,
  width: "12",
  height: "12",
  viewBox: "0 0 24 24",
  fill: "none",
  stroke: "currentColor",
  "stroke-width": "2",
  "stroke-linecap": "round",
  "stroke-linejoin": "round"
};
const _hoisted_19 = {
  key: 2,
  width: "12",
  height: "12",
  viewBox: "0 0 24 24",
  fill: "none",
  stroke: "currentColor",
  "stroke-width": "2",
  "stroke-linecap": "round",
  "stroke-linejoin": "round"
};
const _hoisted_20 = {
  key: 3,
  width: "12",
  height: "12",
  viewBox: "0 0 24 24",
  fill: "none",
  stroke: "currentColor",
  "stroke-width": "2",
  "stroke-linecap": "round",
  "stroke-linejoin": "round"
};
const _hoisted_21 = {
  key: 4,
  width: "12",
  height: "12",
  viewBox: "0 0 24 24",
  fill: "none",
  stroke: "currentColor",
  "stroke-width": "2",
  "stroke-linecap": "round",
  "stroke-linejoin": "round"
};
const _hoisted_22 = {
  key: 5,
  width: "12",
  height: "12",
  viewBox: "0 0 24 24",
  fill: "none",
  stroke: "currentColor",
  "stroke-width": "2",
  "stroke-linecap": "round",
  "stroke-linejoin": "round"
};
const _hoisted_23 = {
  key: 6,
  width: "12",
  height: "12",
  viewBox: "0 0 24 24",
  fill: "none",
  stroke: "currentColor",
  "stroke-width": "2",
  "stroke-linecap": "round",
  "stroke-linejoin": "round"
};
const _hoisted_24 = ["onPointerdown"];
const _hoisted_25 = {
  key: 0,
  class: "timeline-drag-overlay"
};
const _hoisted_26 = { class: "tl-card-header" };
const _hoisted_27 = ["src"];
const _hoisted_28 = {
  class: "tl-name",
  style: { color: "var(--title-color)" }
};
const _hoisted_29 = { class: "tl-fields-container" };
const _hoisted_30 = {
  key: 0,
  width: "12",
  height: "12",
  viewBox: "0 0 24 24",
  fill: "none",
  stroke: "currentColor",
  "stroke-width": "2",
  "stroke-linecap": "round",
  "stroke-linejoin": "round"
};
const _hoisted_31 = {
  key: 1,
  width: "12",
  height: "12",
  viewBox: "0 0 24 24",
  fill: "none",
  stroke: "currentColor",
  "stroke-width": "2",
  "stroke-linecap": "round",
  "stroke-linejoin": "round"
};
const _hoisted_32 = {
  key: 2,
  width: "12",
  height: "12",
  viewBox: "0 0 24 24",
  fill: "none",
  stroke: "currentColor",
  "stroke-width": "2",
  "stroke-linecap": "round",
  "stroke-linejoin": "round"
};
const _hoisted_33 = {
  key: 3,
  width: "12",
  height: "12",
  viewBox: "0 0 24 24",
  fill: "none",
  stroke: "currentColor",
  "stroke-width": "2",
  "stroke-linecap": "round",
  "stroke-linejoin": "round"
};
const _hoisted_34 = {
  key: 4,
  width: "12",
  height: "12",
  viewBox: "0 0 24 24",
  fill: "none",
  stroke: "currentColor",
  "stroke-width": "2",
  "stroke-linecap": "round",
  "stroke-linejoin": "round"
};
const _hoisted_35 = {
  key: 5,
  width: "12",
  height: "12",
  viewBox: "0 0 24 24",
  fill: "none",
  stroke: "currentColor",
  "stroke-width": "2",
  "stroke-linecap": "round",
  "stroke-linejoin": "round"
};
const _hoisted_36 = {
  key: 6,
  width: "12",
  height: "12",
  viewBox: "0 0 24 24",
  fill: "none",
  stroke: "currentColor",
  "stroke-width": "2",
  "stroke-linecap": "round",
  "stroke-linejoin": "round"
};
const TIMELINE_HOUR_WIDTH = 375;
const DATE_LABEL_WIDTH = 140;
const LANE_HEIGHT = 86;
const PADDING_TOP = 12;
const COLLAPSED_ROW_LANES = 2;
const COLLAPSED_ROW_HEIGHT = 175;
const _sfc_main$6 = /* @__PURE__ */ defineComponent({
  __name: "BpaTimelineView",
  props: {
    bookings: {},
    weekDays: {},
    gridConfig: {},
    displaySettings: {},
    dragEnabled: { type: Boolean },
    resizeEnabled: { type: Boolean }
  },
  emits: ["card-click", "drag-begin", "drag-end", "resize-end"],
  setup(__props, { emit: __emit }) {
    const props = __props;
    const emit = __emit;
    const totalWidth = computed(() => getGridTotalMinutes(props.gridConfig) * TIMELINE_HOUR_WIDTH / 60);
    const hourMarkers = computed(() => {
      const markers = [];
      const startMinute = getGridStartMinute(props.gridConfig);
      const markerMinutes = getHourMarkerMinutes(props.gridConfig);
      markerMinutes.forEach((minute, index) => {
        markers.push({
          minute,
          label: formatMinuteLabel(minute),
          left: (minute - startMinute) * TIMELINE_HOUR_WIDTH / 60,
          isLast: index === markerMinutes.length - 1
        });
      });
      return markers;
    });
    const expandedRows = reactive({});
    const trackRefs = ref([]);
    const timelineViewRef = ref(null);
    const timelineScrollLeft = ref(0);
    const timelineViewportWidth = ref(0);
    const uiText = useCalendarText();
    const activeDrag = ref(null);
    let cleanupPendingBookingPointerInteraction = null;
    const pixelsPerMinute = computed(() => TIMELINE_HOUR_WIDTH / 60);
    const trackViewportWidth = computed(() => Math.min(totalWidth.value, Math.max(timelineViewportWidth.value - DATE_LABEL_WIDTH, 0)));
    const nowIndicatorLeft = ref(0);
    const showNowIndicator = ref(false);
    let nowIndicatorInterval = null;
    const activeDragOverlayStyle = computed(() => {
      if (!activeDrag.value) {
        return null;
      }
      const { initialCardRect, currentX, currentY, originX, originY, originalStart, originalEnd, previewBooking, interactionType } = activeDrag.value;
      const dx = currentX - originX;
      const dy = currentY - originY;
      const colors = getBookingColors(previewBooking);
      const minWidth = Math.max(pixelsPerMinute.value * props.gridConfig.snapInterval, 1);
      const originalStartMinutes = timeToMinutesOfDay(originalStart);
      const originalEndMinutes = timeToMinutesOfDay(originalEnd);
      const previewStartMinutes = timeToMinutesOfDay(previewBooking.start);
      const previewEndMinutes = timeToMinutesOfDay(previewBooking.end);
      let left = initialCardRect.left;
      let top = initialCardRect.top;
      let width = initialCardRect.width;
      if (interactionType === "drag") {
        left = initialCardRect.left + dx;
        top = initialCardRect.top + dy;
      } else if (interactionType === "resize-left") {
        const startDeltaMinutes = previewStartMinutes - originalStartMinutes;
        width = Math.max(minWidth, initialCardRect.width - startDeltaMinutes * pixelsPerMinute.value);
        left = initialCardRect.left + (initialCardRect.width - width);
      } else if (interactionType === "resize-right") {
        const endDeltaMinutes = previewEndMinutes - originalEndMinutes;
        width = Math.max(minWidth, initialCardRect.width + endDeltaMinutes * pixelsPerMinute.value);
      }
      return {
        position: "fixed",
        left: `${left}px`,
        top: `${top}px`,
        width: `${width}px`,
        height: `${initialCardRect.height}px`,
        backgroundColor: colors.bg,
        borderColor: colors.border,
        color: "#3D3F3F",
        "--title-color": colors.text,
        cursor: interactionType === "drag" ? "grabbing" : "ew-resize",
        zIndex: "1000",
        pointerEvents: "none"
      };
    });
    function toggleRow(dateIso) {
      expandedRows[dateIso] = !expandedRows[dateIso];
    }
    function updateTimelineViewportMetrics() {
      var _a;
      timelineViewportWidth.value = ((_a = timelineViewRef.value) == null ? void 0 : _a.clientWidth) ?? 0;
    }
    function onTimelineScroll(event) {
      timelineScrollLeft.value = event.target.scrollLeft;
    }
    function updateNowIndicator() {
      const now2 = /* @__PURE__ */ new Date();
      const currentMinute = timeToMinutesOfDay(now2);
      const startMinute = getGridStartMinute(props.gridConfig);
      const endMinute = getGridEndMinute(props.gridConfig);
      showNowIndicator.value = currentMinute >= startMinute && currentMinute <= endMinute;
      nowIndicatorLeft.value = (currentMinute - startMinute) * TIMELINE_HOUR_WIDTH / 60;
    }
    function setTrackRef(index, el) {
      trackRefs.value[index] = el;
    }
    function isBookingInPast(booking) {
      return resolveTimeBookingIsPast(booking);
    }
    function getBookingColors(booking) {
      return booking.theme ?? {
        bg: "var(--bpa-cl-white)",
        border: "var(--bpa-gt-gray-400)",
        text: "var(--bpa-dt-black-400)"
      };
    }
    function canResizeBooking(booking) {
      return !!props.resizeEnabled && resolveTimeBookingResizeEnabled(booking);
    }
    function getNearestDayIndex(clientY, fallbackIndex) {
      const indexedTracks = trackRefs.value.map((el, index) => ({ el, index })).filter((entry) => !!entry.el);
      if (!indexedTracks.length) {
        return fallbackIndex;
      }
      const match = indexedTracks.find(({ el }) => {
        const rect = el.getBoundingClientRect();
        return clientY >= rect.top && clientY <= rect.bottom;
      });
      if (match) {
        return match.index;
      }
      let nearest = indexedTracks[0].index;
      let nearestDistance = Number.POSITIVE_INFINITY;
      indexedTracks.forEach(({ el, index }) => {
        const rect = el.getBoundingClientRect();
        const centerY = rect.top + rect.height / 2;
        const distance = Math.abs(clientY - centerY);
        if (distance < nearestDistance) {
          nearest = index;
          nearestDistance = distance;
        }
      });
      return nearest;
    }
    function buildDragPreviewState(state) {
      const gridStartMinute = getGridStartMinute(props.gridConfig);
      const gridEndMinute = getGridEndMinute(props.gridConfig);
      const durationMinutes = (state.originalEnd.getTime() - state.originalStart.getTime()) / 6e4;
      const rawDeltaMinutes = (state.currentX - state.originX) / pixelsPerMinute.value;
      const deltaMinutes = snapMinutes(rawDeltaMinutes, props.gridConfig.snapInterval);
      const previewDayIndex = getNearestDayIndex(state.currentY, state.originalDayIndex);
      const maxStartMinute = Math.max(gridStartMinute, gridEndMinute - props.gridConfig.snapInterval);
      const rawStartMinutes = timeToMinutesOfDay(state.originalStart) + deltaMinutes;
      const clampedStart = Math.max(gridStartMinute, Math.min(maxStartMinute, rawStartMinutes));
      const snappedStart = snapMinutes(clampedStart, props.gridConfig.snapInterval);
      const dayShift = previewDayIndex - state.originalDayIndex;
      const newStart = addDays(state.originalStart, dayShift);
      newStart.setHours(Math.floor(snappedStart / 60), snappedStart % 60, 0, 0);
      const newEnd = new Date(newStart.getTime() + durationMinutes * 6e4);
      return {
        previewDayIndex,
        previewBooking: {
          ...state.previewBooking,
          isPreview: true,
          start: newStart,
          end: newEnd
        }
      };
    }
    function buildResizePreviewState(state) {
      const gridStartMinute = getGridStartMinute(props.gridConfig);
      const gridEndMinute = getGridEndMinute(props.gridConfig);
      const snapIntervalMinutes = props.gridConfig.snapInterval;
      const deltaMinutes = snapMinutes((state.currentX - state.originX) / pixelsPerMinute.value, snapIntervalMinutes);
      const minDurationMinutes = snapIntervalMinutes;
      if (state.interactionType === "resize-left") {
        const startMinutes = timeToMinutesOfDay(state.originalStart) + deltaMinutes;
        const clampedStart = Math.max(gridStartMinute, startMinutes);
        const snappedStart = snapMinutes(clampedStart, snapIntervalMinutes);
        const newStart2 = new Date(state.originalStart);
        newStart2.setHours(Math.floor(snappedStart / 60), snappedStart % 60, 0, 0);
        const newEnd2 = new Date(state.originalEnd);
        if (newStart2.getTime() >= newEnd2.getTime() - minDurationMinutes * 6e4) {
          return null;
        }
        return {
          previewDayIndex: state.originalDayIndex,
          previewBooking: {
            ...state.previewBooking,
            isPreview: true,
            start: newStart2,
            end: newEnd2
          }
        };
      }
      const endMinutes = timeToMinutesOfDay(state.originalEnd) + deltaMinutes;
      const clampedEnd = Math.min(gridEndMinute, endMinutes);
      const snappedEnd = snapMinutes(clampedEnd, snapIntervalMinutes);
      const newStart = new Date(state.originalStart);
      const newEnd = new Date(state.originalEnd);
      newEnd.setHours(Math.floor(snappedEnd / 60), snappedEnd % 60, 0, 0);
      if (newEnd.getTime() <= newStart.getTime() + minDurationMinutes * 6e4) {
        return null;
      }
      return {
        previewDayIndex: state.originalDayIndex,
        previewBooking: {
          ...state.previewBooking,
          isPreview: true,
          start: newStart,
          end: newEnd
        }
      };
    }
    function buildPreviewState(state) {
      return state.interactionType === "drag" ? buildDragPreviewState(state) : buildResizePreviewState(state);
    }
    function cleanupActiveDrag() {
      document.removeEventListener("pointermove", handleActiveDragMove);
      document.removeEventListener("pointerup", handleActiveDragEnd);
      document.body.style.userSelect = "";
      document.body.style.cursor = "";
    }
    function startActiveInteraction(booking, event, dayIndex, cardEl, interactionType) {
      emit("drag-begin");
      const actualStart = new Date(booking.originalStart ?? booking.start);
      const actualEnd = new Date(booking.originalEnd ?? booking.end);
      const cardRect = cardEl.getBoundingClientRect();
      const initialState = {
        bookingId: booking.id,
        interactionType,
        originX: event.clientX,
        originY: event.clientY,
        currentX: event.clientX,
        currentY: event.clientY,
        originalStart: actualStart,
        originalEnd: actualEnd,
        originalDayIndex: dayIndex,
        initialCardRect: {
          left: cardRect.left,
          top: cardRect.top,
          width: cardRect.width,
          height: cardRect.height
        },
        previewDayIndex: dayIndex,
        previewBooking: {
          ...booking,
          isPreview: true,
          start: actualStart,
          end: actualEnd,
          originalStart: booking.originalStart ? new Date(booking.originalStart) : void 0,
          originalEnd: booking.originalEnd ? new Date(booking.originalEnd) : void 0
        }
      };
      activeDrag.value = initialState;
      document.addEventListener("pointermove", handleActiveDragMove);
      document.addEventListener("pointerup", handleActiveDragEnd);
      document.body.style.userSelect = "none";
      document.body.style.cursor = interactionType === "drag" ? "grabbing" : "ew-resize";
    }
    function handleActiveDragMove(event) {
      if (!activeDrag.value) {
        return;
      }
      const nextState = {
        ...activeDrag.value,
        currentX: event.clientX,
        currentY: event.clientY
      };
      const preview = buildPreviewState(nextState);
      if (!preview) {
        activeDrag.value = nextState;
        return;
      }
      activeDrag.value = {
        ...nextState,
        previewDayIndex: preview.previewDayIndex,
        previewBooking: preview.previewBooking
      };
    }
    function handleActiveDragEnd() {
      if (!activeDrag.value) {
        return;
      }
      const finalState = activeDrag.value;
      const hasMovement = Math.abs(finalState.currentX - finalState.originX) > 3 || Math.abs(finalState.currentY - finalState.originY) > 3;
      cleanupActiveDrag();
      activeDrag.value = null;
      if (!hasMovement) {
        return;
      }
      if (finalState.interactionType === "drag") {
        emit(
          "drag-end",
          finalState.bookingId,
          finalState.previewBooking.start,
          finalState.previewBooking.end,
          finalState.originalDayIndex,
          finalState.previewDayIndex
        );
        return;
      }
      emit(
        "resize-end",
        finalState.bookingId,
        finalState.previewBooking.start,
        finalState.previewBooking.end,
        finalState.interactionType
      );
    }
    function onBookingPointerDown(booking, event, dayIndex) {
      if (activeDrag.value) {
        return;
      }
      cancelPendingBookingPointerInteraction();
      const cardEl = event.currentTarget;
      const pointerStart = { x: event.clientX, y: event.clientY };
      let dragStarted = false;
      const onMove = (moveEvent) => {
        if (!props.dragEnabled) {
          return;
        }
        const dx = moveEvent.clientX - pointerStart.x;
        const dy = moveEvent.clientY - pointerStart.y;
        if (Math.sqrt(dx * dx + dy * dy) <= 5) {
          return;
        }
        dragStarted = true;
        cleanupPending();
        if (isBookingInPast(booking)) {
          return;
        }
        moveEvent.preventDefault();
        moveEvent.stopPropagation();
        startActiveInteraction(booking, event, dayIndex, cardEl, "drag");
        handleActiveDragMove(moveEvent);
      };
      const onUp = () => {
        cleanupPending();
        if (!dragStarted) {
          emit("card-click", booking, cardEl);
        }
      };
      const onCancel = () => {
        cleanupPending();
      };
      const cleanupPending = () => {
        document.removeEventListener("pointermove", onMove);
        document.removeEventListener("pointerup", onUp);
        document.removeEventListener("pointercancel", onCancel);
        if (typeof window !== "undefined") {
          window.removeEventListener("scroll", onCancel, true);
        }
        if (cleanupPendingBookingPointerInteraction === cleanupPending) {
          cleanupPendingBookingPointerInteraction = null;
        }
      };
      cleanupPendingBookingPointerInteraction = cleanupPending;
      document.addEventListener("pointermove", onMove);
      document.addEventListener("pointerup", onUp);
      document.addEventListener("pointercancel", onCancel);
      if (typeof window !== "undefined") {
        window.addEventListener("scroll", onCancel, true);
      }
    }
    function onResizeHandlePointerDown(booking, event, dayIndex, edge) {
      if (!canResizeBooking(booking) || isBookingInPast(booking)) {
        return;
      }
      const handleEl = event.currentTarget;
      const cardEl = handleEl.closest(".timeline-booking");
      if (!cardEl) {
        return;
      }
      startActiveInteraction(
        booking,
        event,
        dayIndex,
        cardEl,
        edge === "left" ? "resize-left" : "resize-right"
      );
    }
    function cancelPendingBookingPointerInteraction() {
      cleanupPendingBookingPointerInteraction == null ? void 0 : cleanupPendingBookingPointerInteraction();
    }
    onUnmounted(() => {
      cancelPendingBookingPointerInteraction();
      cleanupActiveDrag();
      if (nowIndicatorInterval) {
        clearInterval(nowIndicatorInterval);
      }
      if (typeof window !== "undefined") {
        window.removeEventListener("resize", updateTimelineViewportMetrics);
      }
    });
    onMounted(() => {
      nextTick(() => {
        var _a;
        updateTimelineViewportMetrics();
        timelineScrollLeft.value = ((_a = timelineViewRef.value) == null ? void 0 : _a.scrollLeft) ?? 0;
        updateNowIndicator();
      });
      nowIndicatorInterval = setInterval(updateNowIndicator, 6e4);
      if (typeof window !== "undefined") {
        window.addEventListener("resize", updateTimelineViewportMetrics);
      }
    });
    watch(
      () => [
        props.gridConfig.startMinute,
        props.gridConfig.startHour,
        props.gridConfig.endMinute,
        props.gridConfig.endHour
      ],
      () => {
        updateNowIndicator();
      },
      { immediate: true }
    );
    watch(
      () => props.weekDays.map((date) => date.getTime()).join(","),
      () => {
        cancelPendingBookingPointerInteraction();
      }
    );
    const dayLayouts = computed(() => {
      return props.weekDays.map((date, dayIndex) => {
        const dayBookings = getTimeBookingsForDay(props.bookings, date);
        const visibleBookings = activeDrag.value ? dayBookings.filter((booking) => {
          var _a;
          return booking.id !== ((_a = activeDrag.value) == null ? void 0 : _a.bookingId);
        }) : dayBookings;
        const layout = computeColumnLayout(visibleBookings, props.gridConfig);
        const dateIso = date.toISOString();
        const maxLanes = Math.max(layout.maxLanes || 1, 1);
        const isExpanded = !!expandedRows[dateIso];
        const hasOverflow = maxLanes > COLLAPSED_ROW_LANES;
        const rowHeight = isExpanded ? maxLanes * LANE_HEIGHT + PADDING_TOP * 2 : COLLAPSED_ROW_HEIGHT;
        const innerHeight = rowHeight - 1;
        const today = /* @__PURE__ */ new Date();
        const isToday2 = date.getFullYear() === today.getFullYear() && date.getMonth() === today.getMonth() && date.getDate() === today.getDate();
        return {
          date,
          dayIndex,
          dateIso,
          maxLanes,
          isExpanded,
          hasOverflow,
          rowHeight,
          innerHeight,
          isToday: isToday2,
          positioned: layout.positioned
        };
      });
    });
    function getBookingStyle(positioned, dayDate) {
      const booking = positioned.booking;
      const gridStartMinute = getGridStartMinute(props.gridConfig);
      const gridEndMinute = getGridEndMinute(props.gridConfig);
      const canDrag = !!props.dragEnabled && !isBookingInPast(booking);
      const gridStart = new Date(dayDate);
      gridStart.setHours(Math.floor(gridStartMinute / 60), gridStartMinute % 60, 0, 0);
      const gridEnd = new Date(dayDate);
      gridEnd.setHours(Math.floor(gridEndMinute / 60), gridEndMinute % 60, 0, 0);
      const effectiveStart = booking.start < gridStart ? gridStart : booking.start;
      const effectiveEnd = booking.end > gridEnd ? gridEnd : booking.end;
      const startMin = timeToMinutesOfDay(effectiveStart) - gridStartMinute;
      const endMin = timeToMinutesOfDay(effectiveEnd) - gridStartMinute;
      const pxPerMin = pixelsPerMinute.value;
      const colors = booking.theme ?? {
        bg: "var(--bpa-cl-white)",
        border: "var(--bpa-gt-gray-400)",
        text: "var(--bpa-dt-black-400)"
      };
      const durationWidth = Math.max((endMin - startMin) * pxPerMin - 2, 1);
      return {
        left: `${startMin * pxPerMin}px`,
        // Keep the card inside its true timespan so adjacent bookings do not collide.
        width: `${durationWidth}px`,
        top: `${PADDING_TOP + positioned.laneIndex * LANE_HEIGHT}px`,
        height: `76px`,
        backgroundColor: colors.bg,
        borderColor: colors.border,
        color: "#3D3F3F",
        "--title-color": colors.text,
        cursor: canDrag ? "grab" : "pointer",
        touchAction: canDrag ? "none" : "auto"
      };
    }
    const visibleSettings = computed(() => {
      if (!props.displaySettings) return [];
      return props.displaySettings.filter((s) => s.visible);
    });
    const titleField = computed(() => visibleSettings.value[0]);
    const otherFields = computed(() => visibleSettings.value.slice(1));
    function getResolvedFieldValue(booking, id) {
      return resolveTimeBookingDisplayFieldValue(booking, id);
    }
    function getTitleText(booking) {
      const value = titleField.value ? getResolvedFieldValue(booking, titleField.value.id) : "";
      return value || booking.customerName;
    }
    function getStatusIconSrc(booking) {
      var _a;
      return ((_a = getStatusDefinition(booking.status)) == null ? void 0 : _a.iconSrc) ?? "";
    }
    function getDetailFields(booking) {
      return otherFields.value.map((field) => ({
        field,
        value: getResolvedFieldValue(booking, field.id)
      })).filter((entry) => !!entry.value);
    }
    function getDetailFieldRows(booking) {
      const detailFields = getDetailFields(booking);
      if (detailFields.length <= 1) {
        return [detailFields];
      }
      const firstRow = detailFields.filter((_, index) => index % 2 === 0);
      const secondRow = detailFields.filter((_, index) => index % 2 === 1);
      return [
        firstRow,
        secondRow
      ].filter((row) => row.length > 0);
    }
    function getFieldIconKind(id) {
      return resolveDisplayFieldIconKind(id);
    }
    return (_ctx, _cache) => {
      return openBlock(), createElementBlock("div", {
        ref_key: "timelineViewRef",
        ref: timelineViewRef,
        class: "timeline-view",
        onScroll: onTimelineScroll
      }, [
        createElementVNode("div", _hoisted_1$5, [
          _cache[0] || (_cache[0] = createElementVNode("div", { class: "date-label-spacer" }, null, -1)),
          createElementVNode("div", {
            class: "timeline-hours",
            style: normalizeStyle({ width: `${totalWidth.value}px` })
          }, [
            (openBlock(true), createElementBlock(Fragment, null, renderList(hourMarkers.value, (m) => {
              return openBlock(), createElementBlock("div", {
                key: m.minute,
                class: normalizeClass(["hour-marker", { "hour-marker-last": m.isLast }]),
                style: normalizeStyle({ left: `${m.left}px` })
              }, [
                createElementVNode("span", _hoisted_2$4, toDisplayString$1(m.label), 1)
              ], 6);
            }), 128))
          ], 4)
        ]),
        createElementVNode("div", _hoisted_3$4, [
          (openBlock(true), createElementBlock(Fragment, null, renderList(dayLayouts.value, (day) => {
            return openBlock(), createElementBlock("div", {
              key: day.dateIso,
              class: "timeline-row",
              style: normalizeStyle({ height: `${day.rowHeight}px` })
            }, [
              createElementVNode("div", {
                class: normalizeClass(["date-label", { "is-today": day.isToday }]),
                style: normalizeStyle({ height: `${day.innerHeight}px` })
              }, [
                createElementVNode("div", _hoisted_4$3, [
                  createElementVNode("div", _hoisted_5$2, [
                    createElementVNode("span", _hoisted_6$2, toDisplayString$1(day.date.getDate()), 1),
                    createElementVNode("span", _hoisted_7$1, toDisplayString$1(unref(formatDayFull)(day.date).toUpperCase()), 1)
                  ]),
                  createElementVNode("button", {
                    class: "expand-arrow-btn",
                    disabled: !day.hasOverflow,
                    onClick: withModifiers(($event) => toggleRow(day.dateIso), ["stop"]),
                    title: !day.hasOverflow ? "" : day.isExpanded ? unref(uiText).dayHeader.collapse : unref(uiText).dayHeader.expand
                  }, [
                    !day.isExpanded ? (openBlock(), createElementBlock("svg", _hoisted_9$1, [..._cache[1] || (_cache[1] = [
                      createElementVNode("polyline", { points: "6 9 12 15 18 9" }, null, -1)
                    ])])) : (openBlock(), createElementBlock("svg", _hoisted_10$1, [..._cache[2] || (_cache[2] = [
                      createElementVNode("polyline", { points: "18 15 12 9 6 15" }, null, -1)
                    ])]))
                  ], 8, _hoisted_8$1)
                ])
              ], 6),
              createElementVNode("div", {
                class: "timeline-track-shell",
                style: normalizeStyle({ width: `${totalWidth.value}px` })
              }, [
                createElementVNode("div", {
                  ref_for: true,
                  ref: (el) => setTrackRef(day.dayIndex, el),
                  class: "timeline-track-outer",
                  style: normalizeStyle({
                    left: `${DATE_LABEL_WIDTH}px`,
                    width: `${trackViewportWidth.value}px`,
                    height: `${day.innerHeight}px`,
                    overflowY: day.hasOverflow && !day.isExpanded ? "auto" : "hidden"
                  })
                }, [
                  createElementVNode("div", {
                    class: "timeline-track-inner",
                    style: normalizeStyle({
                      width: `${totalWidth.value}px`,
                      height: `${Math.max(day.maxLanes * LANE_HEIGHT + PADDING_TOP * 2, day.innerHeight)}px`,
                      transform: `translateX(-${timelineScrollLeft.value}px)`
                    })
                  }, [
                    (openBlock(true), createElementBlock(Fragment, null, renderList(hourMarkers.value, (m) => {
                      return openBlock(), createElementBlock("div", {
                        key: m.minute,
                        class: "track-line",
                        style: normalizeStyle({ left: `${m.left}px` })
                      }, null, 4);
                    }), 128)),
                    day.isToday && showNowIndicator.value ? (openBlock(), createElementBlock("div", {
                      key: 0,
                      class: "timeline-now-indicator",
                      style: normalizeStyle({ left: `${nowIndicatorLeft.value}px` })
                    }, [..._cache[3] || (_cache[3] = [
                      createElementVNode("div", { class: "timeline-now-dot" }, null, -1),
                      createElementVNode("div", { class: "timeline-now-line" }, null, -1)
                    ])], 4)) : createCommentVNode("", true),
                    (openBlock(true), createElementBlock(Fragment, null, renderList(day.positioned, (pos) => {
                      var _a;
                      return openBlock(), createElementBlock("div", {
                        key: pos.booking.sliceKey ?? pos.booking.id,
                        class: normalizeClass(["timeline-booking", { "is-preview": ((_a = activeDrag.value) == null ? void 0 : _a.bookingId) === pos.booking.id }]),
                        style: normalizeStyle(getBookingStyle(pos, day.date)),
                        onPointerdown: ($event) => onBookingPointerDown(pos.booking, $event, day.dayIndex)
                      }, [
                        canResizeBooking(pos.booking) && !isBookingInPast(pos.booking) ? (openBlock(), createElementBlock("div", {
                          key: 0,
                          class: "resize-handle resize-handle-left",
                          onPointerdown: withModifiers(($event) => onResizeHandlePointerDown(pos.booking, $event, day.dayIndex, "left"), ["stop", "prevent"])
                        }, null, 40, _hoisted_12$1)) : createCommentVNode("", true),
                        createElementVNode("div", _hoisted_13$1, [
                          getStatusIconSrc(pos.booking) ? (openBlock(), createElementBlock("img", {
                            key: 0,
                            class: "tl-status-icon",
                            src: getStatusIconSrc(pos.booking),
                            alt: "",
                            "aria-hidden": "true"
                          }, null, 8, _hoisted_14$1)) : createCommentVNode("", true),
                          createElementVNode("span", _hoisted_15$1, toDisplayString$1(getTitleText(pos.booking)), 1),
                          createVNode(BpaBookingIndicators, {
                            booking: pos.booking
                          }, null, 8, ["booking"])
                        ]),
                        createElementVNode("div", _hoisted_16, [
                          (openBlock(true), createElementBlock(Fragment, null, renderList(getDetailFieldRows(pos.booking), (row, rowIndex) => {
                            return openBlock(), createElementBlock("div", {
                              key: `${pos.booking.sliceKey ?? pos.booking.id}-row-${rowIndex}`,
                              class: "tl-fields-row"
                            }, [
                              (openBlock(true), createElementBlock(Fragment, null, renderList(row, (entry) => {
                                return openBlock(), createElementBlock("div", {
                                  key: entry.field.id,
                                  class: "tl-icon-text"
                                }, [
                                  getFieldIconKind(entry.field.id) === "time" ? (openBlock(), createElementBlock("svg", _hoisted_17, [..._cache[4] || (_cache[4] = [
                                    createElementVNode("circle", {
                                      cx: "12",
                                      cy: "12",
                                      r: "10"
                                    }, null, -1),
                                    createElementVNode("polyline", { points: "12 6 12 12 16 14" }, null, -1)
                                  ])])) : getFieldIconKind(entry.field.id) === "service" ? (openBlock(), createElementBlock("svg", _hoisted_18, [..._cache[5] || (_cache[5] = [
                                    createElementVNode("rect", {
                                      x: "3",
                                      y: "8",
                                      width: "18",
                                      height: "14",
                                      rx: "2",
                                      ry: "2"
                                    }, null, -1),
                                    createElementVNode("path", { d: "M16 8V6a4 4 0 0 0-8 0v2" }, null, -1)
                                  ])])) : getFieldIconKind(entry.field.id) === "staff" ? (openBlock(), createElementBlock("svg", _hoisted_19, [..._cache[6] || (_cache[6] = [
                                    createElementVNode("path", { d: "M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" }, null, -1),
                                    createElementVNode("circle", {
                                      cx: "12",
                                      cy: "7",
                                      r: "4"
                                    }, null, -1)
                                  ])])) : getFieldIconKind(entry.field.id) === "location" ? (openBlock(), createElementBlock("svg", _hoisted_20, [..._cache[7] || (_cache[7] = [
                                    createElementVNode("path", { d: "M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" }, null, -1),
                                    createElementVNode("circle", {
                                      cx: "12",
                                      cy: "10",
                                      r: "3"
                                    }, null, -1)
                                  ])])) : getFieldIconKind(entry.field.id) === "price" ? (openBlock(), createElementBlock("svg", _hoisted_21, [..._cache[8] || (_cache[8] = [
                                    createElementVNode("circle", {
                                      cx: "12",
                                      cy: "12",
                                      r: "10"
                                    }, null, -1),
                                    createElementVNode("path", { d: "M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8" }, null, -1),
                                    createElementVNode("path", { d: "M12 18V6" }, null, -1)
                                  ])])) : getFieldIconKind(entry.field.id) === "customer" ? (openBlock(), createElementBlock("svg", _hoisted_22, [..._cache[9] || (_cache[9] = [
                                    createElementVNode("path", { d: "M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" }, null, -1),
                                    createElementVNode("circle", {
                                      cx: "12",
                                      cy: "7",
                                      r: "4"
                                    }, null, -1)
                                  ])])) : (openBlock(), createElementBlock("svg", _hoisted_23, [..._cache[10] || (_cache[10] = [
                                    createElementVNode("rect", {
                                      x: "4",
                                      y: "5",
                                      width: "16",
                                      height: "14",
                                      rx: "2"
                                    }, null, -1),
                                    createElementVNode("path", { d: "M8 10h8M8 14h5" }, null, -1)
                                  ])])),
                                  createElementVNode("span", null, toDisplayString$1(entry.value), 1)
                                ]);
                              }), 128))
                            ]);
                          }), 128))
                        ]),
                        canResizeBooking(pos.booking) && !isBookingInPast(pos.booking) ? (openBlock(), createElementBlock("div", {
                          key: 1,
                          class: "resize-handle resize-handle-right",
                          onPointerdown: withModifiers(($event) => onResizeHandlePointerDown(pos.booking, $event, day.dayIndex, "right"), ["stop", "prevent"])
                        }, null, 40, _hoisted_24)) : createCommentVNode("", true)
                      ], 46, _hoisted_11$1);
                    }), 128))
                  ], 4)
                ], 4)
              ], 4)
            ], 4);
          }), 128))
        ]),
        activeDrag.value && activeDragOverlayStyle.value ? (openBlock(), createElementBlock("div", _hoisted_25, [
          createElementVNode("div", {
            class: "timeline-booking is-overlay",
            style: normalizeStyle(activeDragOverlayStyle.value)
          }, [
            createElementVNode("div", _hoisted_26, [
              getStatusIconSrc(activeDrag.value.previewBooking) ? (openBlock(), createElementBlock("img", {
                key: 0,
                class: "tl-status-icon",
                src: getStatusIconSrc(activeDrag.value.previewBooking),
                alt: "",
                "aria-hidden": "true"
              }, null, 8, _hoisted_27)) : createCommentVNode("", true),
              createElementVNode("span", _hoisted_28, toDisplayString$1(getTitleText(activeDrag.value.previewBooking)), 1),
              createVNode(BpaBookingIndicators, {
                booking: activeDrag.value.previewBooking
              }, null, 8, ["booking"])
            ]),
            createElementVNode("div", _hoisted_29, [
              (openBlock(true), createElementBlock(Fragment, null, renderList(getDetailFieldRows(activeDrag.value.previewBooking), (row, rowIndex) => {
                return openBlock(), createElementBlock("div", {
                  key: `${activeDrag.value.previewBooking.sliceKey ?? activeDrag.value.previewBooking.id}-row-${rowIndex}`,
                  class: "tl-fields-row"
                }, [
                  (openBlock(true), createElementBlock(Fragment, null, renderList(row, (entry) => {
                    return openBlock(), createElementBlock("div", {
                      key: entry.field.id,
                      class: "tl-icon-text"
                    }, [
                      getFieldIconKind(entry.field.id) === "time" ? (openBlock(), createElementBlock("svg", _hoisted_30, [..._cache[11] || (_cache[11] = [
                        createElementVNode("circle", {
                          cx: "12",
                          cy: "12",
                          r: "10"
                        }, null, -1),
                        createElementVNode("polyline", { points: "12 6 12 12 16 14" }, null, -1)
                      ])])) : getFieldIconKind(entry.field.id) === "service" ? (openBlock(), createElementBlock("svg", _hoisted_31, [..._cache[12] || (_cache[12] = [
                        createElementVNode("rect", {
                          x: "3",
                          y: "8",
                          width: "18",
                          height: "14",
                          rx: "2",
                          ry: "2"
                        }, null, -1),
                        createElementVNode("path", { d: "M16 8V6a4 4 0 0 0-8 0v2" }, null, -1)
                      ])])) : getFieldIconKind(entry.field.id) === "staff" ? (openBlock(), createElementBlock("svg", _hoisted_32, [..._cache[13] || (_cache[13] = [
                        createElementVNode("path", { d: "M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" }, null, -1),
                        createElementVNode("circle", {
                          cx: "12",
                          cy: "7",
                          r: "4"
                        }, null, -1)
                      ])])) : getFieldIconKind(entry.field.id) === "location" ? (openBlock(), createElementBlock("svg", _hoisted_33, [..._cache[14] || (_cache[14] = [
                        createElementVNode("path", { d: "M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" }, null, -1),
                        createElementVNode("circle", {
                          cx: "12",
                          cy: "10",
                          r: "3"
                        }, null, -1)
                      ])])) : getFieldIconKind(entry.field.id) === "price" ? (openBlock(), createElementBlock("svg", _hoisted_34, [..._cache[15] || (_cache[15] = [
                        createElementVNode("circle", {
                          cx: "12",
                          cy: "12",
                          r: "10"
                        }, null, -1),
                        createElementVNode("path", { d: "M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8" }, null, -1),
                        createElementVNode("path", { d: "M12 18V6" }, null, -1)
                      ])])) : getFieldIconKind(entry.field.id) === "customer" ? (openBlock(), createElementBlock("svg", _hoisted_35, [..._cache[16] || (_cache[16] = [
                        createElementVNode("path", { d: "M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" }, null, -1),
                        createElementVNode("circle", {
                          cx: "12",
                          cy: "7",
                          r: "4"
                        }, null, -1)
                      ])])) : (openBlock(), createElementBlock("svg", _hoisted_36, [..._cache[17] || (_cache[17] = [
                        createElementVNode("rect", {
                          x: "4",
                          y: "5",
                          width: "16",
                          height: "14",
                          rx: "2"
                        }, null, -1),
                        createElementVNode("path", { d: "M8 10h8M8 14h5" }, null, -1)
                      ])])),
                      createElementVNode("span", null, toDisplayString$1(entry.value), 1)
                    ]);
                  }), 128))
                ]);
              }), 128))
            ])
          ], 4)
        ])) : createCommentVNode("", true)
      ], 544);
    };
  }
});
const BpaTimelineView = /* @__PURE__ */ _export_sfc(_sfc_main$6, [["__scopeId", "data-v-c32945a8"]]);
const _hoisted_1$4 = { class: "timeline-belt-title" };
const _hoisted_2$3 = ["aria-expanded", "aria-label", "disabled"];
const _hoisted_3$3 = ["onClick"];
const _hoisted_4$2 = { class: "timeline-all-day-card-head" };
const _hoisted_5$1 = { class: "timeline-all-day-card-title-group" };
const _hoisted_6$1 = { class: "timeline-all-day-card-badge" };
const _hoisted_7 = {
  class: "timeline-all-day-card-marker",
  "aria-hidden": "true"
};
const _hoisted_8 = ["src"];
const _hoisted_9 = { class: "timeline-all-day-card-meta" };
const _hoisted_10 = {
  class: "timeline-all-day-card-meta-icon",
  "aria-hidden": "true"
};
const _hoisted_11 = {
  key: 0,
  width: "12",
  height: "12",
  viewBox: "0 0 24 24",
  fill: "none",
  stroke: "currentColor",
  "stroke-width": "2",
  "stroke-linecap": "round",
  "stroke-linejoin": "round"
};
const _hoisted_12 = {
  key: 1,
  width: "12",
  height: "12",
  viewBox: "0 0 24 24",
  fill: "none",
  stroke: "currentColor",
  "stroke-width": "2",
  "stroke-linecap": "round",
  "stroke-linejoin": "round"
};
const _hoisted_13 = {
  key: 2,
  width: "12",
  height: "12",
  viewBox: "0 0 24 24",
  fill: "none",
  stroke: "currentColor",
  "stroke-width": "2",
  "stroke-linecap": "round",
  "stroke-linejoin": "round"
};
const _hoisted_14 = {
  key: 3,
  width: "12",
  height: "12",
  viewBox: "0 0 24 24",
  fill: "none",
  stroke: "currentColor",
  "stroke-width": "2",
  "stroke-linecap": "round",
  "stroke-linejoin": "round"
};
const _hoisted_15 = { class: "timeline-all-day-card-meta-text" };
const BODY_VERTICAL_PADDING$1 = 16;
const _sfc_main$5 = /* @__PURE__ */ defineComponent({
  __name: "BpaTimelineAllDayBelt",
  props: {
    bookings: {},
    title: {},
    collapsedHeight: {},
    expandedHeight: {}
  },
  emits: ["card-click"],
  setup(__props, { emit: __emit }) {
    const props = __props;
    const emit = __emit;
    const isCollapsed = ref(true);
    const isToggleEnabled = ref(false);
    const beltHeaderRef = ref(null);
    const beltContentRef = ref(null);
    const measuredHeaderHeight = ref(0);
    const measuredContentHeight = ref(0);
    const uiText = useCalendarText();
    const collapsedHeight = computed(() => props.collapsedHeight ?? 200);
    const expandedHeight = computed(() => props.expandedHeight ?? 480);
    const hasBookings = computed(() => props.bookings.length > 0);
    const collapsedSectionHeight = computed(() => hasBookings.value ? collapsedHeight.value : measuredHeaderHeight.value + measuredContentHeight.value + BODY_VERTICAL_PADDING$1);
    const expandedSectionHeight = computed(() => {
      const measuredHeight = measuredHeaderHeight.value + measuredContentHeight.value + BODY_VERTICAL_PADDING$1;
      if (!hasBookings.value) {
        return measuredHeight;
      }
      return Math.min(
        Math.max(measuredHeight, collapsedHeight.value),
        expandedHeight.value
      );
    });
    const sectionHeight = computed(() => isCollapsed.value ? collapsedSectionHeight.value : expandedSectionHeight.value);
    const visibleBodyHeight = computed(() => Math.max(sectionHeight.value - measuredHeaderHeight.value - BODY_VERTICAL_PADDING$1, 0));
    const isBodyScrollable = computed(() => hasBookings.value && measuredContentHeight.value > visibleBodyHeight.value + 1);
    function getPrimaryTitle(booking) {
      return booking.customerName || booking.title;
    }
    function getBadgeText(booking) {
      if (booking.startDate.getTime() === booking.endDate.getTime()) {
        return uiText.value.allDay.fullDay;
      }
      return uiText.value.allDay.multiDay;
    }
    function formatDateLabel(date) {
      return new Intl.DateTimeFormat(String(i18n.global.locale.value ?? "en"), {
        day: "numeric",
        month: "short"
      }).format(date);
    }
    function getRangeLabel(booking) {
      if (booking.startDate.getTime() === booking.endDate.getTime()) {
        return formatDateLabel(booking.startDate);
      }
      return `${formatDateLabel(booking.startDate)} - ${formatDateLabel(booking.endDate)}`;
    }
    function getServiceText(booking) {
      return resolveBookingServiceLabel({
        serviceName: booking.serviceName,
        serviceId: booking.serviceId,
        metadata: booking.metadata ?? null
      });
    }
    function getMetadataText(booking, key) {
      var _a;
      const normalizedKey = key.trim().toLowerCase().replace(/[^a-z0-9]+/g, "");
      if (normalizedKey === "servicename" || normalizedKey === "serviceid" || normalizedKey === "serviceids" || normalizedKey === "servicenames" || normalizedKey === "servicelabel" || normalizedKey === "servicesdata") {
        return getServiceText(booking);
      }
      const value = (_a = booking.metadata) == null ? void 0 : _a[key];
      if (typeof value === "string") {
        return value.trim();
      }
      if (typeof value === "number" || typeof value === "boolean") {
        return String(value);
      }
      return "";
    }
    function getCardTheme(booking) {
      return booking.theme ?? {
        bg: "var(--bpa-app-surface-soft)",
        border: "var(--bpa-app-border)",
        text: "var(--bpa-app-heading)"
      };
    }
    function isDayServiceBooking(booking) {
      var _a;
      return normalizeBooleanLike$2((_a = booking.metadata) == null ? void 0 : _a.isDayService) === true;
    }
    function getCardMarkerIconSrc(booking) {
      var _a;
      const statusIconSrc = ((_a = getStatusDefinition(booking.status)) == null ? void 0 : _a.iconSrc) ?? "";
      if (isDayServiceBooking(booking) && statusIconSrc) {
        return statusIconSrc;
      }
      if (isDayServiceBooking(booking)) {
        return dayServiceIconUrl;
      }
      return "";
    }
    function getMetaItems(booking) {
      const items = [
        {
          key: "date",
          icon: "calendar",
          text: getRangeLabel(booking)
        }
      ];
      const serviceText = getServiceText(booking);
      if (serviceText) {
        items.push({
          key: "service",
          icon: "service",
          text: serviceText
        });
      }
      const staffText = resolveBookingStaffLabel({
        staffMemberName: booking.staffMemberName,
        staffMemberId: booking.staffMemberId,
        StaffData: booking.StaffData,
        isMultiStaff: booking.isMultiStaff,
        metadata: booking.metadata ?? null
      });
      const locationText = getMetadataText(booking, "location");
      if (staffText) {
        items.push({
          key: "staff",
          icon: "staff",
          text: staffText
        });
      }
      if (locationText) {
        items.push({
          key: "location",
          icon: "location",
          text: locationText
        });
      }
      return items;
    }
    function toggleCollapsed() {
      if (!isToggleEnabled.value) {
        return;
      }
      isCollapsed.value = !isCollapsed.value;
    }
    function onCardClick(booking, event) {
      emit("card-click", booking, event.currentTarget);
    }
    function measureHeights() {
      var _a, _b;
      measuredHeaderHeight.value = ((_a = beltHeaderRef.value) == null ? void 0 : _a.offsetHeight) ?? 0;
      measuredContentHeight.value = ((_b = beltContentRef.value) == null ? void 0 : _b.scrollHeight) ?? 0;
    }
    function updateToggleAvailability() {
      measureHeights();
      const collapsedBodyHeight = Math.max(collapsedHeight.value - measuredHeaderHeight.value - BODY_VERTICAL_PADDING$1, 0);
      isToggleEnabled.value = hasBookings.value && measuredContentHeight.value > collapsedBodyHeight + 1;
      if (!isToggleEnabled.value) {
        isCollapsed.value = true;
      }
    }
    let resizeObserver = null;
    onMounted(() => {
      nextTick(() => {
        updateToggleAvailability();
        if (typeof ResizeObserver === "undefined") {
          return;
        }
        resizeObserver = new ResizeObserver(() => updateToggleAvailability());
        if (beltHeaderRef.value) {
          resizeObserver.observe(beltHeaderRef.value);
        }
        if (beltContentRef.value) {
          resizeObserver.observe(beltContentRef.value);
        }
      });
    });
    onUnmounted(() => {
      resizeObserver == null ? void 0 : resizeObserver.disconnect();
      resizeObserver = null;
    });
    watch(
      () => props.bookings.map((booking) => `${booking.id}:${booking.startDate.getTime()}:${booking.endDate.getTime()}`).join("|"),
      () => {
        nextTick(() => updateToggleAvailability());
      }
    );
    return (_ctx, _cache) => {
      return openBlock(), createElementBlock("section", {
        class: "timeline-all-day-belt",
        style: normalizeStyle({ height: `${sectionHeight.value}px` })
      }, [
        createElementVNode("div", {
          ref_key: "beltHeaderRef",
          ref: beltHeaderRef,
          class: "timeline-belt-header"
        }, [
          createElementVNode("h3", _hoisted_1$4, toDisplayString$1(__props.title), 1),
          createElementVNode("button", {
            type: "button",
            class: normalizeClass(["timeline-belt-toggle", { "is-expanded": !isCollapsed.value }]),
            "aria-expanded": !isCollapsed.value,
            "aria-label": isCollapsed.value ? unref(uiText).allDay.expand : unref(uiText).allDay.collapse,
            disabled: !isToggleEnabled.value,
            onClick: toggleCollapsed
          }, [..._cache[0] || (_cache[0] = [
            createElementVNode("svg", {
              width: "18",
              height: "18",
              viewBox: "0 0 24 24",
              fill: "none",
              xmlns: "http://www.w3.org/2000/svg",
              "aria-hidden": "true"
            }, [
              createElementVNode("path", {
                d: "M7 10L12 15L17 10",
                stroke: "currentColor",
                "stroke-width": "1.8",
                "stroke-linecap": "round",
                "stroke-linejoin": "round"
              })
            ], -1)
          ])], 10, _hoisted_2$3)
        ], 512),
        createElementVNode("div", {
          class: normalizeClass(["timeline-belt-body", { "is-scrollable": isBodyScrollable.value }])
        }, [
          createElementVNode("div", {
            ref_key: "beltContentRef",
            ref: beltContentRef,
            class: "timeline-belt-grid"
          }, [
            (openBlock(true), createElementBlock(Fragment, null, renderList(__props.bookings, (booking) => {
              return openBlock(), createElementBlock("button", {
                key: booking.id,
                type: "button",
                class: "timeline-all-day-card",
                style: normalizeStyle({
                  backgroundColor: getCardTheme(booking).bg,
                  borderColor: getCardTheme(booking).border,
                  color: "#3D3F3F"
                }),
                onClick: ($event) => onCardClick(booking, $event)
              }, [
                createElementVNode("div", _hoisted_4$2, [
                  createElementVNode("div", _hoisted_5$1, [
                    createElementVNode("span", {
                      class: "timeline-all-day-card-title",
                      style: normalizeStyle({ color: getCardTheme(booking).text })
                    }, toDisplayString$1(getPrimaryTitle(booking)), 5),
                    createElementVNode("span", _hoisted_6$1, toDisplayString$1(getBadgeText(booking)), 1)
                  ]),
                  createVNode(BpaBookingIndicators, { booking }, null, 8, ["booking"]),
                  createElementVNode("span", _hoisted_7, [
                    getCardMarkerIconSrc(booking) ? (openBlock(), createElementBlock("img", {
                      key: 0,
                      class: "timeline-all-day-card-marker-image",
                      src: getCardMarkerIconSrc(booking),
                      alt: ""
                    }, null, 8, _hoisted_8)) : (openBlock(), createElementBlock("span", {
                      key: 1,
                      class: "timeline-all-day-card-dot",
                      style: normalizeStyle({ backgroundColor: getCardTheme(booking).border })
                    }, null, 4))
                  ])
                ]),
                createElementVNode("div", _hoisted_9, [
                  (openBlock(true), createElementBlock(Fragment, null, renderList(getMetaItems(booking), (item) => {
                    return openBlock(), createElementBlock("div", {
                      key: `${booking.id}-${item.key}`,
                      class: "timeline-all-day-card-meta-item"
                    }, [
                      createElementVNode("span", _hoisted_10, [
                        item.icon === "calendar" ? (openBlock(), createElementBlock("svg", _hoisted_11, [..._cache[1] || (_cache[1] = [
                          createElementVNode("rect", {
                            x: "3",
                            y: "4",
                            width: "18",
                            height: "18",
                            rx: "2"
                          }, null, -1),
                          createElementVNode("path", { d: "M16 2v4M8 2v4M3 10h18" }, null, -1)
                        ])])) : item.icon === "service" ? (openBlock(), createElementBlock("svg", _hoisted_12, [..._cache[2] || (_cache[2] = [
                          createElementVNode("rect", {
                            x: "3",
                            y: "8",
                            width: "18",
                            height: "14",
                            rx: "2",
                            ry: "2"
                          }, null, -1),
                          createElementVNode("path", { d: "M16 8V6a4 4 0 0 0-8 0v2" }, null, -1)
                        ])])) : item.icon === "staff" ? (openBlock(), createElementBlock("svg", _hoisted_13, [..._cache[3] || (_cache[3] = [
                          createElementVNode("path", { d: "M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" }, null, -1),
                          createElementVNode("circle", {
                            cx: "12",
                            cy: "7",
                            r: "4"
                          }, null, -1)
                        ])])) : (openBlock(), createElementBlock("svg", _hoisted_14, [..._cache[4] || (_cache[4] = [
                          createElementVNode("path", { d: "M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" }, null, -1),
                          createElementVNode("circle", {
                            cx: "12",
                            cy: "10",
                            r: "3"
                          }, null, -1)
                        ])]))
                      ]),
                      createElementVNode("span", _hoisted_15, toDisplayString$1(item.text), 1)
                    ]);
                  }), 128))
                ])
              ], 12, _hoisted_3$3);
            }), 128))
          ], 512)
        ], 2)
      ], 4);
    };
  }
});
const BpaTimelineAllDayBelt = /* @__PURE__ */ _export_sfc(_sfc_main$5, [["__scopeId", "data-v-396edabc"]]);
const _hoisted_1$3 = ["onClick"];
const _hoisted_2$2 = ["src"];
const _hoisted_3$2 = {
  key: 1,
  class: "all-day-icon"
};
const _hoisted_4$1 = ["title"];
const GAP = 12;
const _sfc_main$4 = /* @__PURE__ */ defineComponent({
  __name: "BpaAllDaySection",
  props: {
    rows: {},
    columnCount: {},
    columnWidths: {},
    variant: { default: "default" },
    surface: { default: "soft" },
    leadingOverflowWidth: { default: 0 },
    displaySettings: {}
  },
  emits: ["card-click"],
  setup(__props, { emit: __emit }) {
    const props = __props;
    const emit = __emit;
    const rowHeight = computed(() => props.variant === "belt" ? 30 : 26);
    const hasColumnWidths = computed(() => Array.isArray(props.columnWidths) && props.columnWidths.length === props.columnCount && props.columnWidths.every((width) => Number.isFinite(width) && width > 0));
    const totalGridWidth = computed(() => hasColumnWidths.value ? props.columnWidths.reduce((sum, width) => sum + width, 0) : null);
    const totalRows = computed(() => {
      if (props.rows.length === 0) return 0;
      return Math.max(...props.rows.map((r) => r.row)) + 1;
    });
    const sectionHeight = computed(() => {
      return totalRows.value * (rowHeight.value + GAP) + 8;
    });
    function getRowColor(row, index) {
      return row.booking.theme ?? getAllDayColor(index);
    }
    function getRowStyle(row, index) {
      const color = getRowColor(row, index);
      const leadingOverflow = row.continuesBefore ? props.leadingOverflowWidth : 0;
      const baseStyle = {
        top: `${row.row * (rowHeight.value + GAP) + 4}px`,
        height: `${rowHeight.value}px`,
        backgroundColor: color.bg,
        border: `1px solid ${color.border}`,
        color: color.text,
        boxSizing: "border-box",
        ...row.continuesBefore ? { borderRadius: props.variant === "belt" ? "0 8px 8px 0" : "0 4px 4px 0" } : {}
      };
      if (hasColumnWidths.value) {
        const widths = props.columnWidths;
        const left = widths.slice(0, row.startCol).reduce((sum, width2) => sum + width2, 0);
        const width = widths.slice(row.startCol, row.startCol + row.spanCols).reduce((sum, itemWidth) => sum + itemWidth, 0);
        return {
          ...baseStyle,
          left: `${left - leadingOverflow}px`,
          width: `${width + leadingOverflow}px`
        };
      }
      return {
        ...baseStyle,
        left: `calc(${row.startCol / props.columnCount * 100}% + 2px - ${leadingOverflow}px)`,
        width: `calc(${row.spanCols / props.columnCount * 100}% - 4px + ${leadingOverflow}px)`
      };
    }
    function isDayServiceBooking(row) {
      var _a;
      const value = (_a = row.booking.metadata) == null ? void 0 : _a.isDayService;
      return value === true || value === 1 || value === "1" || value === "true";
    }
    const surfaceClass = computed(() => props.surface === "transparent" ? "is-transparent" : "is-soft");
    const visibleSettings = computed(() => {
      var _a;
      return ((_a = props.displaySettings) == null ? void 0 : _a.filter((setting) => setting.visible)) ?? [];
    });
    function normalizeFieldId2(fieldId) {
      return fieldId.trim().toLowerCase().replace(/[^a-z0-9]+/g, "");
    }
    function normalizeFieldValue(value) {
      if (typeof value === "string") {
        return value.trim();
      }
      if (typeof value === "number" || typeof value === "boolean") {
        return String(value);
      }
      if (Array.isArray(value)) {
        return value.map((entry) => normalizeFieldValue(entry)).filter(Boolean).join(", ");
      }
      return "";
    }
    function isPlainObject2(value) {
      return typeof value === "object" && value !== null && !Array.isArray(value);
    }
    function formatAllDayDate(date) {
      return new Intl.DateTimeFormat(void 0, {
        day: "numeric",
        month: "short"
      }).format(date);
    }
    function getDateRangeLabel(booking) {
      if (booking.startDate.getTime() === booking.endDate.getTime()) {
        return formatAllDayDate(booking.startDate);
      }
      return `${formatAllDayDate(booking.startDate)} - ${formatAllDayDate(booking.endDate)}`;
    }
    function resolveFormFieldValue(metadata, fieldId) {
      const formFieldEntries = metadata == null ? void 0 : metadata.form_fields;
      if (!Array.isArray(formFieldEntries)) {
        return "";
      }
      return formFieldEntries.filter((entry) => isPlainObject2(entry)).filter((entry) => {
        const entryId = entry.id;
        return typeof entryId === "string" || typeof entryId === "number" ? String(entryId).trim() === fieldId : false;
      }).map((entry) => normalizeFieldValue(entry.value)).filter(Boolean).join(", ");
    }
    function resolveAllDayFieldValue(booking, fieldId) {
      var _a;
      const normalizedFieldId = normalizeFieldId2(fieldId);
      const metadata = booking.metadata ?? {};
      switch (normalizedFieldId) {
        case "customername":
          return normalizeFieldValue(booking.customerName) || normalizeFieldValue(metadata.customerName);
        case "datetime":
          return getDateRangeLabel(booking);
        case "servicename":
        case "serviceid":
        case "serviceids":
        case "servicelabel":
        case "servicesdata":
          return resolveBookingServiceSummary({
            serviceName: booking.serviceName,
            serviceId: booking.serviceId,
            servicesData: metadata.servicesData ?? metadata.services_data,
            isMultiService: booking.isMultiService ?? metadata.isMultiService,
            metadata
          }).serviceLabel;
        case "staffmembername":
          return resolveBookingStaffLabel({
            staffMemberName: booking.staffMemberName,
            staffMemberId: booking.staffMemberId,
            StaffData: booking.StaffData,
            isMultiStaff: booking.isMultiStaff,
            metadata
          });
        case "location":
          return normalizeFieldValue(metadata.location);
        case "price":
          return normalizeFieldValue(metadata.price);
        case "status":
        case "statuslabel":
          return ((_a = getStatusDefinition(booking.status ?? metadata.status ?? metadata.statusLabel)) == null ? void 0 : _a.label) || normalizeFieldValue(metadata.statusLabel) || normalizeFieldValue(booking.status);
        case "category":
        case "categoryname":
        case "categoryid":
        case "categoryids":
        case "categorylabel":
          return resolveBookingServiceSummary({
            serviceName: booking.serviceName,
            serviceId: booking.serviceId,
            servicesData: metadata.servicesData ?? metadata.services_data,
            isMultiService: booking.isMultiService ?? metadata.isMultiService,
            metadata
          }).categoryLabel;
      }
      const directValue = normalizeFieldValue(booking[fieldId]);
      if (directValue) {
        return directValue;
      }
      const formFieldValue = resolveFormFieldValue(metadata, fieldId);
      if (formFieldValue) {
        return formFieldValue;
      }
      return normalizeFieldValue(metadata[fieldId]);
    }
    function getRowText(row) {
      const values = visibleSettings.value.map((setting) => resolveAllDayFieldValue(row.booking, setting.id)).filter(Boolean);
      return values.length > 0 ? values.join(" | ") : row.booking.title;
    }
    function getTitleStyle(row, index) {
      return {
        color: props.variant === "belt" ? "#3D3F3F" : getRowColor(row, index).border
      };
    }
    function getIndicatorTextColor(row, index) {
      return props.variant === "belt" ? "#3D3F3F" : getRowColor(row, index).text;
    }
    function onRowClick(row, event) {
      emit("card-click", row.booking, event.currentTarget);
    }
    return (_ctx, _cache) => {
      return __props.rows.length > 0 ? (openBlock(), createElementBlock("div", {
        key: 0,
        class: normalizeClass(["all-day-section", [surfaceClass.value, { "is-belt": __props.variant === "belt" }]])
      }, [
        createElementVNode("div", {
          class: "all-day-grid",
          style: normalizeStyle({ height: `${sectionHeight.value}px`, width: totalGridWidth.value ? `${totalGridWidth.value}px` : void 0 })
        }, [
          (openBlock(true), createElementBlock(Fragment, null, renderList(__props.rows, (row, i) => {
            return openBlock(), createElementBlock("div", {
              key: `${row.booking.id}-${row.row}-${row.startCol}-${row.endCol}`,
              class: normalizeClass(["all-day-bar", { "is-belt": __props.variant === "belt" }]),
              style: normalizeStyle(getRowStyle(row, i)),
              onClick: ($event) => onRowClick(row, $event)
            }, [
              isDayServiceBooking(row) ? (openBlock(), createElementBlock("img", {
                key: 0,
                class: "all-day-icon-image",
                src: unref(dayServiceIconUrl),
                alt: "",
                "aria-hidden": "true"
              }, null, 8, _hoisted_2$2)) : (openBlock(), createElementBlock("span", _hoisted_3$2, "✨")),
              createElementVNode("span", {
                class: "all-day-title",
                style: normalizeStyle(getTitleStyle(row, i)),
                title: getRowText(row)
              }, toDisplayString$1(getRowText(row)), 13, _hoisted_4$1),
              createVNode(BpaBookingIndicators, {
                booking: row.booking,
                size: "compact",
                style: normalizeStyle({ "--booking-indicator-text-color": getIndicatorTextColor(row, i) })
              }, null, 8, ["booking", "style"])
            ], 14, _hoisted_1$3);
          }), 128))
        ], 4)
      ], 2)) : createCommentVNode("", true);
    };
  }
});
const BpaAllDaySection = /* @__PURE__ */ _export_sfc(_sfc_main$4, [["__scopeId", "data-v-350d5b20"]]);
const _hoisted_1$2 = { class: "belt-title" };
const _hoisted_2$1 = ["aria-expanded", "aria-label", "disabled"];
const _hoisted_3$1 = {
  key: 1,
  class: "belt-empty"
};
const BODY_VERTICAL_PADDING = 16;
const _sfc_main$3 = /* @__PURE__ */ defineComponent({
  __name: "BpaWeekAllDayBelt",
  props: {
    rows: {},
    columnCount: {},
    title: {},
    columnWidths: {},
    gutterWidth: {},
    collapsedHeight: {},
    expandedHeight: {},
    displaySettings: {}
  },
  emits: ["card-click"],
  setup(__props, { emit: __emit }) {
    const props = __props;
    const emit = __emit;
    const isCollapsed = ref(true);
    const isToggleEnabled = ref(false);
    const beltHeaderRef = ref(null);
    const beltContentRef = ref(null);
    const measuredHeaderHeight = ref(0);
    const measuredContentHeight = ref(0);
    const uiText = useCalendarText();
    const hasRows = computed(() => props.rows.length > 0);
    const gutterWidth = computed(() => props.gutterWidth ?? 92);
    const collapsedHeight = computed(() => props.collapsedHeight ?? 200);
    const expandedHeight = computed(() => props.expandedHeight ?? 480);
    const collapsedSectionHeight = computed(() => hasRows.value ? collapsedHeight.value : measuredHeaderHeight.value + measuredContentHeight.value + BODY_VERTICAL_PADDING);
    const expandedSectionHeight = computed(() => {
      const measuredHeight = measuredHeaderHeight.value + measuredContentHeight.value + BODY_VERTICAL_PADDING;
      if (!hasRows.value) {
        return measuredHeight;
      }
      return Math.min(
        Math.max(measuredHeight, collapsedHeight.value),
        expandedHeight.value
      );
    });
    const sectionHeight = computed(() => isCollapsed.value ? collapsedSectionHeight.value : expandedSectionHeight.value);
    const visibleBodyHeight = computed(() => Math.max(sectionHeight.value - measuredHeaderHeight.value - BODY_VERTICAL_PADDING, 0));
    const isBodyScrollable = computed(() => hasRows.value && measuredContentHeight.value > visibleBodyHeight.value + 1);
    const totalColumnWidth = computed(() => Array.isArray(props.columnWidths) && props.columnWidths.length ? props.columnWidths.reduce((sum, width) => sum + width, 0) : null);
    function toggleCollapsed() {
      if (!isToggleEnabled.value) {
        return;
      }
      isCollapsed.value = !isCollapsed.value;
    }
    function onCardClick(booking, cardEl) {
      emit("card-click", booking, cardEl);
    }
    function measureHeights() {
      var _a, _b;
      measuredHeaderHeight.value = ((_a = beltHeaderRef.value) == null ? void 0 : _a.offsetHeight) ?? 0;
      measuredContentHeight.value = ((_b = beltContentRef.value) == null ? void 0 : _b.scrollHeight) ?? 0;
    }
    function updateToggleAvailability() {
      measureHeights();
      const collapsedBodyHeight = Math.max(collapsedHeight.value - measuredHeaderHeight.value - BODY_VERTICAL_PADDING, 0);
      isToggleEnabled.value = hasRows.value && measuredContentHeight.value > collapsedBodyHeight + 1;
      if (!isToggleEnabled.value) {
        isCollapsed.value = true;
      }
    }
    let resizeObserver = null;
    onMounted(() => {
      nextTick(() => {
        updateToggleAvailability();
        if (typeof ResizeObserver === "undefined") {
          return;
        }
        resizeObserver = new ResizeObserver(() => updateToggleAvailability());
        if (beltHeaderRef.value) {
          resizeObserver.observe(beltHeaderRef.value);
        }
        if (beltContentRef.value) {
          resizeObserver.observe(beltContentRef.value);
        }
      });
    });
    onUnmounted(() => {
      resizeObserver == null ? void 0 : resizeObserver.disconnect();
      resizeObserver = null;
    });
    watch(
      () => {
        var _a;
        return [props.rows.length, ((_a = props.columnWidths) == null ? void 0 : _a.join("|")) ?? "", props.collapsedHeight, props.expandedHeight];
      },
      () => {
        nextTick(() => updateToggleAvailability());
      }
    );
    return (_ctx, _cache) => {
      return openBlock(), createElementBlock("section", {
        class: normalizeClass(["week-all-day-belt", { "is-collapsed": isCollapsed.value }]),
        style: normalizeStyle({
          height: `${sectionHeight.value}px`,
          minWidth: totalColumnWidth.value ? `${totalColumnWidth.value + gutterWidth.value}px` : void 0
        })
      }, [
        createElementVNode("div", {
          ref_key: "beltHeaderRef",
          ref: beltHeaderRef,
          class: "belt-header"
        }, [
          createElementVNode("h3", _hoisted_1$2, toDisplayString$1(__props.title), 1),
          createElementVNode("button", {
            type: "button",
            class: normalizeClass(["belt-toggle", { "is-expanded": !isCollapsed.value }]),
            "aria-expanded": !isCollapsed.value,
            "aria-label": isCollapsed.value ? unref(uiText).allDay.expand : unref(uiText).allDay.collapse,
            disabled: !isToggleEnabled.value,
            onClick: toggleCollapsed
          }, [..._cache[0] || (_cache[0] = [
            createElementVNode("svg", {
              width: "18",
              height: "18",
              viewBox: "0 0 24 24",
              fill: "none",
              xmlns: "http://www.w3.org/2000/svg",
              "aria-hidden": "true"
            }, [
              createElementVNode("path", {
                d: "M7 10L12 15L17 10",
                stroke: "currentColor",
                "stroke-width": "1.8",
                "stroke-linecap": "round",
                "stroke-linejoin": "round"
              })
            ], -1)
          ])], 10, _hoisted_2$1)
        ], 512),
        createElementVNode("div", {
          class: normalizeClass(["belt-body", { "is-scrollable": isBodyScrollable.value }])
        }, [
          createElementVNode("div", {
            class: "belt-body-spacer",
            style: normalizeStyle({ width: `${gutterWidth.value}px`, minWidth: `${gutterWidth.value}px` })
          }, null, 4),
          createElementVNode("div", {
            ref_key: "beltContentRef",
            ref: beltContentRef,
            class: "belt-body-content",
            style: normalizeStyle({ width: totalColumnWidth.value ? `${totalColumnWidth.value}px` : void 0 })
          }, [
            hasRows.value ? (openBlock(), createBlock(BpaAllDaySection, {
              key: 0,
              rows: __props.rows,
              "column-count": __props.columnCount,
              "column-widths": __props.columnWidths,
              "leading-overflow-width": gutterWidth.value,
              "display-settings": __props.displaySettings,
              variant: "belt",
              surface: "transparent",
              onCardClick
            }, null, 8, ["rows", "column-count", "column-widths", "leading-overflow-width", "display-settings"])) : (openBlock(), createElementBlock("div", _hoisted_3$1, toDisplayString$1(unref(uiText).allDay.empty), 1))
          ], 4)
        ], 2)
      ], 6);
    };
  }
});
const BpaWeekAllDayBelt = /* @__PURE__ */ _export_sfc(_sfc_main$3, [["__scopeId", "data-v-e4f7c006"]]);
const _hoisted_1$1 = {
  key: 0,
  class: "bpa-day-all-day-belt"
};
const _hoisted_2 = { class: "bpa-sticky-headers" };
const _hoisted_3 = { class: "bpa-headers-inner" };
const _hoisted_4 = { class: "bpa-headers-inner" };
const _hoisted_5 = {
  key: 3,
  class: "bpa-timeline-shell"
};
const _hoisted_6 = {
  key: 0,
  class: "bpa-timeline-all-day-belt"
};
const DRAG_STOP_EVENT_NAME = "bookingpress:appointment-drag-stop";
const RESIZE_STOP_EVENT_NAME = "bookingpress:appointment-resize-stop";
const CLOSE_POPOVER_EVENT_NAME = "bookingpress:appointment-popover-close";
const OPEN_SIDEBAR_EVENT_NAME = "bookingpress:calendar-sidebar-open";
const MONTH_RANGE_CHANGE_EVENT_NAME = "bookingpress:calendar-month-range-change";
const MOBILE_BREAKPOINT = 768;
const MOBILE_LANDSCAPE_HEIGHT_BREAKPOINT = 500;
const TABLET_BREAKPOINT = 1280;
const DATA_LOAD_CHUNK_THRESHOLD = 500;
const DATA_LOAD_CHUNK_MIN_SIZE = 150;
const DATA_LOAD_CHUNK_MAX_SIZE = 400;
const _sfc_main$2 = /* @__PURE__ */ defineComponent({
  __name: "BpaCalendarView",
  setup(__props) {
    const calendar2 = useCalendar();
    const injectedConfig = inject("bpaInitialConfig", {});
    const calendarHostMode = inject("bpaCalendarHostMode", "viewport");
    const uiText = useCalendarText();
    const configuredDisplaySettings = (() => {
      const baseDisplaySettings = injectedConfig.displaySettings ?? injectedConfig.displayFieldOptions ?? injectedConfig.cardFieldOptions ?? [];
      const extraDisplayFields = injectedConfig.extraDisplayFields;
      if (!Array.isArray(extraDisplayFields) || extraDisplayFields.length === 0) {
        return baseDisplaySettings;
      }
      if (Array.isArray(baseDisplaySettings)) {
        return {
          fields: baseDisplaySettings,
          extraDisplayFields
        };
      }
      if (isPlainObject2(baseDisplaySettings)) {
        return {
          ...baseDisplaySettings,
          extraDisplayFields
        };
      }
      return {
        extraDisplayFields
      };
    })();
    const registerDataCallback = inject("bpaRegisterDataCallback");
    const displaySettings = ref(
      getDisplaySettings(configuredDisplaySettings, uiText.value.displayFields) || getDefaultDisplaySettings(configuredDisplaySettings, uiText.value.displayFields)
    );
    const bookingFilters = ref(getInitialBookingFilters(injectedConfig));
    const calendarRootRef = ref(null);
    const scrollContainer = ref(null);
    const gridArea = ref(null);
    const weekStickyHeadersRef = ref(null);
    const weekStickyHeaderHeight = ref(72);
    const allDayGutterWidth = ref(92);
    const allDayColumnWidths = ref([]);
    const weekViewportWidth = ref(0);
    const expandedDays = reactive({});
    const activePopoverBookingId = ref(null);
    const isMobileViewport = ref(false);
    const isMobileLandscapeViewport = ref(false);
    const isTabletViewport = ref(false);
    const isTouchInteractionDevice = ref(getIsTouchInteractionDevice());
    const lastDesktopView = ref("month");
    const hasInitializedWideView = ref(false);
    const activePopoverBooking = computed(() => {
      if (!activePopoverBookingId.value) {
        return null;
      }
      return findBookingById(activePopoverBookingId.value);
    });
    const popoverAnchorEl = ref(null);
    const showPopover = ref(false);
    const isEmbeddedHost = computed(() => calendarHostMode === "embedded");
    const availableViewportHeight = ref(null);
    const columnWidth = ref(200);
    const visibleDays = computed(() => calendar2.dayColumns.value.map((column) => column.date));
    const externalBookingFilterConfig = computed(() => {
      const rawConfig = injectedConfig.bookingFilterConfig ?? injectedConfig.bookingFilters ?? injectedConfig.filters ?? {};
      return {
        service: isPlainObject2(rawConfig.service) ? rawConfig.service : void 0,
        status: isPlainObject2(rawConfig.status) ? rawConfig.status : void 0,
        employee: isPlainObject2(rawConfig.employee) ? rawConfig.employee : void 0,
        location: isPlainObject2(rawConfig.location) ? rawConfig.location : void 0,
        category: isPlainObject2(rawConfig.category) ? rawConfig.category : void 0
      };
    });
    const interactionConfig = computed(() => normalizeInteractionConfig(
      injectedConfig.interactionConfig
    ));
    const isCardDragDropEnabled = computed(() => interactionConfig.value.enableCardDragDrop && !isTouchInteractionDevice.value);
    const isCardResizeEnabled = computed(() => interactionConfig.value.enableCardResize && !isTouchInteractionDevice.value);
    const showAddAppointmentButton = computed(() => interactionConfig.value.showAddAppointmentButton);
    const popoverConfig = computed(() => normalizePopoverConfig(
      injectedConfig.popover,
      interactionConfig.value
    ));
    const bookings = useBookings(() => activeGridConfig.value);
    let dataMutationQueue = Promise.resolve();
    const currentMonthKey = computed(() => formatMonthKey(calendar2.currentDate.value));
    const filteredTimeBookings = computed(() => bookings.timeBookings.value.filter(matchesTimeBookingFilters));
    const filteredAllDayBookings = computed(() => bookings.allDayBookings.value.filter(matchesAllDayBookingFilters));
    const visibleTimeBookingsForGrid = computed(() => {
      const days = visibleDays.value;
      if (days.length === 0) {
        return filteredTimeBookings.value;
      }
      const scopedBookings = [];
      for (const day of days) {
        scopedBookings.push(...bookings.getBookingsForDay(day).filter(matchesTimeBookingFilters));
      }
      return scopedBookings;
    });
    const ratchetBounds = ref({ startMin: 9999, endMin: -1 });
    watch(
      [() => calendar2.currentView.value, visibleDays],
      () => {
        ratchetBounds.value = { startMin: 9999, endMin: -1 };
      }
    );
    const visibleGridConfig = computed(() => {
      if (calendar2.currentView.value === "month") {
        return calendar2.gridConfig.value;
      }
      const days = visibleDays.value;
      if (days.length === 0) {
        return expandGridConfigForBookings(calendar2.gridConfig.value, visibleTimeBookingsForGrid.value);
      }
      const idealExpanded = expandGridConfigForBookings(calendar2.gridConfig.value, visibleTimeBookingsForGrid.value);
      const idealStartMin = idealExpanded.startMinute ?? (idealExpanded.startHour ?? 0) * 60;
      const idealEndMin = idealExpanded.endMinute ?? (idealExpanded.endHour ?? 24) * 60;
      const ratchetStartMin = Math.min(idealStartMin, ratchetBounds.value.startMin);
      const ratchetEndMin = Math.max(idealEndMin, ratchetBounds.value.endMin);
      return {
        ...idealExpanded,
        startHour: Math.floor(ratchetStartMin / 60),
        startMinute: ratchetStartMin,
        endHour: Math.floor(ratchetEndMin / 60),
        endMinute: ratchetEndMin
      };
    });
    watch(
      visibleGridConfig,
      (config) => {
        const s = config.startMinute ?? (config.startHour ?? 0) * 60;
        const e = config.endMinute ?? (config.endHour ?? 24) * 60;
        if (s < ratchetBounds.value.startMin || e > ratchetBounds.value.endMin) {
          ratchetBounds.value = {
            startMin: Math.min(s, ratchetBounds.value.startMin),
            endMin: Math.max(e, ratchetBounds.value.endMin)
          };
        }
      },
      { immediate: true }
    );
    const frozenGridConfig = ref(null);
    const activeGridConfig = computed(() => {
      if (frozenGridConfig.value) {
        return frozenGridConfig.value;
      }
      return visibleGridConfig.value;
    });
    const weekAllDaySectionTitle = computed(() => {
      var _a;
      return ((_a = activeGridConfig.value.allDaySectionTitle) == null ? void 0 : _a.trim()) || uiText.value.allDay.title;
    });
    const filterOptionBookings = computed(() => [
      ...bookings.timeBookings.value,
      ...bookings.allDayBookings.value
    ]);
    const serviceFilterOptions = computed(() => buildServiceOptions(filterOptionBookings.value));
    const statusFilterOptions = computed(() => buildStatusOptions(filterOptionBookings.value));
    const popoverStatusOptions = computed(() => {
      var _a;
      return normalizePopoverStatusOptions(
        (_a = injectedConfig.popover) == null ? void 0 : _a.statusOptions,
        uiText.value.statuses
      );
    });
    const employeeFilterOptions = computed(() => {
      const optionsByValue = /* @__PURE__ */ new Map();
      filterOptionBookings.value.forEach((booking) => {
        const summary = resolveBookingStaffSummary(booking);
        const items = summary.items.length > 0 ? summary.items : summary.staffValues.map((value) => ({
          staffId: value,
          staffName: ""
        }));
        items.forEach((item) => {
          const value = item.staffId ?? item.staffName;
          if (value === null || value === "") return;
          const key = String(value);
          if (optionsByValue.has(key)) return;
          optionsByValue.set(key, {
            label: item.staffName || formatOptionLabel(key),
            value
          });
        });
      });
      return Array.from(optionsByValue.values()).sort((left, right) => String(left.label).localeCompare(String(right.label), void 0, { numeric: true }));
    });
    const locationFilterOptions = computed(() => {
      const optionsByValue = /* @__PURE__ */ new Map();
      filterOptionBookings.value.forEach((booking) => {
        const value = getBookingFilterValue("location", booking);
        if (value === null) return;
        const key = String(value);
        if (optionsByValue.has(key)) return;
        optionsByValue.set(key, {
          label: getBookingMetadataText(booking, "location") || formatOptionLabel(key),
          value
        });
      });
      return Array.from(optionsByValue.values()).sort((left, right) => String(left.label).localeCompare(String(right.label), void 0, { numeric: true }));
    });
    const categoryFilterOptions = computed(() => {
      const optionsByValue = /* @__PURE__ */ new Map();
      filterOptionBookings.value.forEach((booking) => {
        const summary = resolveBookingServiceSummary(booking);
        summary.items.forEach((item) => {
          const value = item.categoryId ?? (item.categoryName || null);
          const label = item.categoryName || formatOptionLabel(String(value ?? ""));
          if (value === null || !label) {
            return;
          }
          const key = String(value);
          if (optionsByValue.has(key)) {
            return;
          }
          optionsByValue.set(key, {
            label,
            value
          });
        });
      });
      return Array.from(optionsByValue.values()).sort((left, right) => String(left.label).localeCompare(String(right.label), void 0, { numeric: true }));
    });
    const bookingFilterConfig = computed(() => ({
      service: withAllowedOptions(mergeFilterFieldConfig(
        {
          label: uiText.value.bookingFilters.fields.service,
          placeholder: uiText.value.bookingFilters.placeholders.service,
          visible: true,
          options: serviceFilterOptions.value
        },
        externalBookingFilterConfig.value.service
      )),
      status: withAllowedOptions(mergeFilterFieldConfig(
        {
          label: uiText.value.bookingFilters.fields.status,
          placeholder: uiText.value.bookingFilters.placeholders.status,
          visible: true,
          options: statusFilterOptions.value
        },
        externalBookingFilterConfig.value.status
      )),
      employee: withAllowedOptions(mergeFilterFieldConfig(
        {
          label: uiText.value.bookingFilters.fields.employee,
          placeholder: uiText.value.bookingFilters.placeholders.employee,
          visible: false,
          options: employeeFilterOptions.value
        },
        externalBookingFilterConfig.value.employee
      )),
      location: withAllowedOptions(mergeFilterFieldConfig(
        {
          label: uiText.value.bookingFilters.fields.location,
          placeholder: uiText.value.bookingFilters.placeholders.location,
          visible: false,
          options: locationFilterOptions.value
        },
        externalBookingFilterConfig.value.location
      )),
      category: withAllowedOptions(mergeFilterFieldConfig(
        {
          label: uiText.value.bookingFilters.fields.category,
          placeholder: uiText.value.bookingFilters.placeholders.category,
          visible: false,
          options: categoryFilterOptions.value
        },
        externalBookingFilterConfig.value.category
      ))
    }));
    const activeBookingFilters = computed(() => sanitizeBookingFilters(
      bookingFilters.value,
      bookingFilterConfig.value
    ));
    const timelineVisibleAllDayBookings = computed(() => {
      const weekDays = calendar2.weekDays.value;
      if (!weekDays.length) {
        return [];
      }
      const rangeStart = startOfDay$1(weekDays[0]).getTime();
      const rangeEndExclusive = startOfDay$1(weekDays[weekDays.length - 1]).getTime() + 864e5;
      return filteredAllDayBookings.value.filter((booking) => {
        const bookingStart = startOfDay$1(booking.startDate).getTime();
        const bookingEndExclusive = startOfDay$1(booking.endDate).getTime() + 864e5;
        return bookingStart < rangeEndExclusive && bookingEndExclusive > rangeStart;
      });
    });
    function updateDisplaySettings(newSettings) {
      displaySettings.value = newSettings;
      saveDisplaySettings(newSettings);
    }
    function updateBookingFilters(newFilters) {
      bookingFilters.value = sanitizeBookingFilters(newFilters, bookingFilterConfig.value);
    }
    function clearExpandedDays() {
      Object.keys(expandedDays).forEach((key) => delete expandedDays[Number(key)]);
    }
    function getWeekColumnsAvailableWidth() {
      var _a, _b;
      const viewportWidth = weekViewportWidth.value || ((_a = scrollContainer.value) == null ? void 0 : _a.clientWidth) || ((_b = calendarRootRef.value) == null ? void 0 : _b.clientWidth) || (typeof window !== "undefined" ? window.innerWidth : 1200);
      return Math.max(0, viewportWidth - allDayGutterWidth.value);
    }
    function getWeekCollapsedColumnWidth(dayCount = calendar2.dayColumns.value.length) {
      const minWidth = activeGridConfig.value.columnMinWidth;
      if (dayCount <= 0) {
        return minWidth;
      }
      return Math.max(minWidth, getWeekColumnsAvailableWidth() / dayCount);
    }
    function collapseExpandedDaysThatFitCollapsedWidth() {
      if (calendar2.currentView.value !== "week") {
        return;
      }
      const collapsedWidth = getWeekCollapsedColumnWidth();
      calendar2.dayColumns.value.forEach((column, index) => {
        if (!expandedDays[index]) {
          return;
        }
        if (getColumnLayout(column.date).requiredWidth <= collapsedWidth) {
          delete expandedDays[index];
        }
      });
    }
    function toggleDayExpand(dayIndex) {
      const isCurrentlyExpanded = !!expandedDays[dayIndex];
      clearExpandedDays();
      if (!isCurrentlyExpanded) {
        expandedDays[dayIndex] = true;
      }
      nextTick(() => {
        allDayColumnWidths.value = columnInfos.value.map((info) => info.cappedWidth);
        measureColumnWidth();
      });
    }
    function getFilteredBookingsForDay(day) {
      return bookings.getBookingsForDay(day).filter(matchesTimeBookingFilters);
    }
    function getColumnLayout(day) {
      return computeColumnLayout(getFilteredBookingsForDay(day), activeGridConfig.value);
    }
    const allDayLayout = computed(() => computeAllDayLayout(filteredAllDayBookings.value, visibleDays.value));
    function openDayView(date) {
      calendar2.setDate(date);
      calendar2.setView("day");
    }
    function findTimeBookingById(bookingId) {
      return bookings.timeBookings.value.find((booking) => booking.id === bookingId) ?? null;
    }
    function findAllDayBookingById(bookingId) {
      return bookings.allDayBookings.value.find((booking) => booking.id === bookingId) ?? null;
    }
    function findBookingById(bookingId) {
      return findTimeBookingById(bookingId) ?? findAllDayBookingById(bookingId);
    }
    function canResizeBooking(booking) {
      return resolveTimeBookingResizeEnabled(booking);
    }
    function cloneTimeBookingForEvent(booking) {
      const snapshotStart = booking.originalStart ? new Date(booking.originalStart) : new Date(booking.start);
      const snapshotEnd = booking.originalEnd ? new Date(booking.originalEnd) : new Date(booking.end);
      return {
        ...booking,
        start: snapshotStart,
        end: snapshotEnd,
        originalStart: booking.originalStart ? new Date(booking.originalStart) : void 0,
        originalEnd: booking.originalEnd ? new Date(booking.originalEnd) : void 0,
        metadata: isPlainObject2(booking.metadata) ? { ...booking.metadata } : booking.metadata,
        theme: booking.theme ? { ...booking.theme } : booking.theme,
        start_time_24h: formatTimeForTwentyFourHourEntry(snapshotStart),
        end_time_24h: formatTimeForTwentyFourHourEntry(snapshotEnd),
        start_time_val: formatTimeForExtendedDataEntry(snapshotStart, snapshotStart),
        end_time_val: formatTimeForExtendedDataEntry(snapshotEnd, snapshotStart)
      };
    }
    function cloneAllDayBookingForEvent(booking) {
      return {
        ...booking,
        startDate: new Date(booking.startDate),
        endDate: new Date(booking.endDate),
        isDayService: true,
        start_date: formatDateForRawEntry(booking.startDate),
        end_date: formatDateForRawEntry(booking.endDate),
        metadata: isPlainObject2(booking.metadata) ? { ...booking.metadata } : booking.metadata,
        theme: booking.theme ? { ...booking.theme } : booking.theme
      };
    }
    function buildMonthMovedTimeRange(booking, targetDate) {
      const sourceStart = booking.originalStart ? new Date(booking.originalStart) : new Date(booking.start);
      const sourceEnd = booking.originalEnd ? new Date(booking.originalEnd) : new Date(booking.end);
      const durationMs = sourceEnd.getTime() - sourceStart.getTime();
      const nextStart = new Date(targetDate);
      nextStart.setHours(
        sourceStart.getHours(),
        sourceStart.getMinutes(),
        sourceStart.getSeconds(),
        sourceStart.getMilliseconds()
      );
      return {
        start: nextStart,
        end: new Date(nextStart.getTime() + durationMs)
      };
    }
    function buildMonthMovedAllDayRange(booking, targetDate) {
      const sourceStart = startOfDay$1(booking.startDate);
      const sourceEnd = startOfDay$1(booking.endDate);
      const daySpan = Math.max(Math.round((sourceEnd.getTime() - sourceStart.getTime()) / 864e5), 0);
      const nextStartDate = startOfDay$1(targetDate);
      return {
        startDate: nextStartDate,
        endDate: addDays(nextStartDate, daySpan)
      };
    }
    function getMonthDayIndexFromDate(date) {
      const targetTime = startOfDay$1(date).getTime();
      const match = calendar2.monthDays.value.findIndex((day) => startOfDay$1(day).getTime() === targetTime);
      return match >= 0 ? match : 0;
    }
    function formatMonthKey(date) {
      const year = date.getFullYear();
      const month = `${date.getMonth() + 1}`.padStart(2, "0");
      return `${year}-${month}`;
    }
    function getCurrentMonthRange(date) {
      const monthStart = new Date(date.getFullYear(), date.getMonth(), 1);
      const monthEnd = new Date(date.getFullYear(), date.getMonth() + 1, 0);
      monthEnd.setHours(23, 59, 59, 999);
      return {
        monthStart,
        monthEnd,
        monthKey: formatMonthKey(date)
      };
    }
    function dispatchCalendarInteractionEvent(eventName, detail) {
      if (typeof window === "undefined") {
        return;
      }
      window.dispatchEvent(new CustomEvent(eventName, { detail }));
    }
    function dispatchCalendarMonthRangeChangeEvent(detail) {
      dispatchCalendarInteractionEvent(MONTH_RANGE_CHANGE_EVENT_NAME, detail);
    }
    function getIsTouchInteractionDevice() {
      if (typeof window === "undefined") {
        return false;
      }
      const hasTouchPoints = typeof navigator !== "undefined" && navigator.maxTouchPoints > 0;
      const matchesTouchMedia = typeof window.matchMedia === "function" && (window.matchMedia("(any-pointer: coarse)").matches || window.matchMedia("(pointer: coarse)").matches || window.matchMedia("(hover: none)").matches);
      return hasTouchPoints || matchesTouchMedia;
    }
    function getIsMobileViewport() {
      if (typeof window === "undefined") {
        return false;
      }
      const width = window.innerWidth;
      const height = window.innerHeight;
      if (width <= MOBILE_BREAKPOINT) {
        return true;
      }
      if (typeof window.matchMedia !== "function") {
        return false;
      }
      const isTouchLikeViewport = window.matchMedia("(pointer: coarse)").matches || window.matchMedia("(hover: none)").matches;
      return isTouchLikeViewport && height <= MOBILE_LANDSCAPE_HEIGHT_BREAKPOINT;
    }
    function getIsMobileLandscapeViewport() {
      if (typeof window === "undefined") {
        return false;
      }
      if (typeof window.matchMedia !== "function") {
        return false;
      }
      const isTouchLikeViewport = window.matchMedia("(pointer: coarse)").matches || window.matchMedia("(hover: none)").matches;
      const isLandscape = window.matchMedia("(orientation: landscape)").matches || window.innerWidth > window.innerHeight;
      const shortSide = Math.min(window.innerWidth, window.innerHeight);
      return isTouchLikeViewport && isLandscape && shortSide <= MOBILE_BREAKPOINT;
    }
    function getIsTabletViewport() {
      if (typeof window === "undefined") {
        return false;
      }
      const width = window.innerWidth;
      const height = window.innerHeight;
      const isTouchLikeViewport = typeof window.matchMedia === "function" && (window.matchMedia("(pointer: coarse)").matches || window.matchMedia("(hover: none)").matches);
      if (width <= MOBILE_BREAKPOINT || width > TABLET_BREAKPOINT || !isTouchLikeViewport || height <= MOBILE_LANDSCAPE_HEIGHT_BREAKPOINT) {
        return false;
      }
      return true;
    }
    function getDefaultWideView() {
      return isTabletViewport.value ? "timeline" : "month";
    }
    function ensureWideViewInitialized() {
      if (hasInitializedWideView.value) {
        return;
      }
      lastDesktopView.value = getDefaultWideView();
      hasInitializedWideView.value = true;
    }
    function syncResponsiveMode() {
      isMobileViewport.value = getIsMobileViewport();
      isMobileLandscapeViewport.value = getIsMobileLandscapeViewport();
      isTabletViewport.value = getIsTabletViewport();
      isTouchInteractionDevice.value = getIsTouchInteractionDevice();
    }
    const columnInfos = computed(() => {
      const minWidth = activeGridConfig.value.columnMinWidth;
      const days = calendar2.dayColumns.value;
      const totalAvailable = getWeekColumnsAvailableWidth();
      const collapsedWidth = getWeekCollapsedColumnWidth(days.length);
      const layouts = days.map((column) => getColumnLayout(column.date));
      const expandedStates = layouts.map((layout, index) => !!expandedDays[index] && layout.requiredWidth > collapsedWidth);
      const expandedCount = expandedStates.filter(Boolean).length;
      const nonExpandedCount = days.length - expandedCount;
      const reservedForOthers = nonExpandedCount * minWidth;
      const maxPerExpanded = Math.max(minWidth, totalAvailable - reservedForOthers);
      const expandedWidths = layouts.map((layout, index) => expandedStates[index] ? Math.max(minWidth, Math.min(layout.requiredWidth, maxPerExpanded)) : 0);
      const totalExpandedWidth = expandedWidths.reduce((sum, width) => sum + width, 0);
      const visibleCollapsedWidth = nonExpandedCount > 0 ? Math.max(minWidth, (totalAvailable - totalExpandedWidth) / nonExpandedCount) : minWidth;
      return days.map((column, index) => {
        const layout = layouts[index];
        const isExpanded = expandedStates[index];
        const hasOverflow = layout.requiredWidth > visibleCollapsedWidth;
        const visibleWidth = isExpanded ? expandedWidths[index] : visibleCollapsedWidth;
        const flexStyle = {
          flexGrow: "0",
          flexShrink: "0",
          flexBasis: `${visibleWidth}px`,
          width: `${visibleWidth}px`,
          minWidth: `${visibleWidth}px`,
          maxWidth: `${visibleWidth}px`,
          transition: "flex-basis 0.28s ease, width 0.28s ease, min-width 0.28s ease, max-width 0.28s ease",
          willChange: "flex-basis, width, min-width, max-width"
        };
        return {
          col: column,
          idx: index,
          layout,
          hasOverflow,
          isExpanded,
          cappedWidth: visibleWidth,
          flexStyle
        };
      });
    });
    const gridHeight = computed(() => getGridTotalMinutes(activeGridConfig.value) * activeGridConfig.value.hourHeight / 60);
    const calendarRootStyle = computed(() => {
      if (isEmbeddedHost.value) {
        return void 0;
      }
      if (availableViewportHeight.value === null) {
        return void 0;
      }
      return {
        height: `${availableViewportHeight.value}px`
      };
    });
    function measureColumnWidth() {
      if (!gridArea.value) {
        return;
      }
      const columns = gridArea.value.querySelectorAll(".time-column");
      if (columns.length > 0) {
        columnWidth.value = columns[0].offsetWidth;
      }
    }
    function updateAvailableViewportHeight() {
      var _a, _b;
      if (typeof window === "undefined" || !calendarRootRef.value) {
        availableViewportHeight.value = null;
        return;
      }
      const viewportHeight = ((_a = window.visualViewport) == null ? void 0 : _a.height) ?? window.innerHeight;
      const rootTop = Math.max(calendarRootRef.value.getBoundingClientRect().top, 0);
      const parentHeight = ((_b = calendarRootRef.value.parentElement) == null ? void 0 : _b.clientHeight) ?? 0;
      const nextHeight = Math.max(0, Math.round(viewportHeight - rootTop));
      availableViewportHeight.value = parentHeight > 0 ? Math.min(nextHeight, parentHeight) : nextHeight;
    }
    const dragResize = useDragResize(
      () => activeGridConfig.value,
      () => visibleDays.value,
      () => columnWidth.value,
      {
        onDragEnd(bookingId, newStart, newEnd, newDayIndex) {
          commitDragMove(bookingId, newStart, newEnd, dragResize.originalDayIndex.value, newDayIndex);
        },
        onResizeEnd(bookingId, newStart, newEnd) {
          var _a;
          const previousBooking = findTimeBookingById(bookingId);
          const previousSnapshot = previousBooking ? cloneTimeBookingForEvent(previousBooking) : null;
          const resizeType = ((_a = dragResize.dragState.value) == null ? void 0 : _a.type) ?? null;
          if (previousBooking) {
            bookings.replaceTimeBookings([buildUpdatedTimeBooking(previousBooking, { start: newStart, end: newEnd })]);
          }
          bookings.invalidateLayoutCache();
          frozenGridConfig.value = null;
          const updatedBooking = findTimeBookingById(bookingId);
          dispatchCalendarInteractionEvent(RESIZE_STOP_EVENT_NAME, {
            bookingId,
            booking: updatedBooking ? cloneTimeBookingForEvent(updatedBooking) : null,
            previousBooking: previousSnapshot,
            previousStart: (previousSnapshot == null ? void 0 : previousSnapshot.start) ?? null,
            previousEnd: (previousSnapshot == null ? void 0 : previousSnapshot.end) ?? null,
            newStart,
            newEnd,
            resizeType
          });
        },
        onCancel() {
          frozenGridConfig.value = null;
        }
      }
    );
    const previewLayout = computed(() => {
      if (!dragResize.previewBooking.value) {
        return null;
      }
      const previewBooking = dragResize.previewBooking.value;
      const previewDayIndex = dragResize.previewDayIndex.value;
      const targetDay = visibleDays.value[previewDayIndex] || previewBooking.start;
      const dayBookings = getFilteredBookingsForDay(targetDay);
      const clampedStart = new Date(Math.max(previewBooking.start.getTime(), targetDay.getTime()));
      const targetDayEnd = new Date(targetDay);
      targetDayEnd.setHours(23, 59, 59, 999);
      const clampedEnd = new Date(Math.min(previewBooking.end.getTime(), targetDayEnd.getTime()));
      const clampedPreviewBooking = cloneBookingWithMutableStaffData(previewBooking, {
        start: clampedStart.getTime() > clampedEnd.getTime() ? clampedEnd : clampedStart,
        end: clampedEnd
      });
      const overridden = dayBookings.map((booking) => booking.id === previewBooking.id ? clampedPreviewBooking : booking);
      if (!overridden.find((booking) => booking.id === previewBooking.id)) {
        overridden.push(clampedPreviewBooking);
      }
      const layout = computeColumnLayout(overridden, activeGridConfig.value);
      return layout.positioned.find((entry) => entry.booking.id === previewBooking.id) ?? null;
    });
    const originalPreviewLayout = computed(() => {
      const state = dragResize.dragState.value;
      const previewBooking = dragResize.previewBooking.value;
      const originalDayIndex = dragResize.originalDayIndex.value;
      if (!state || !previewBooking || originalDayIndex < 0) {
        return null;
      }
      const targetDay = visibleDays.value[originalDayIndex];
      if (!targetDay) {
        return null;
      }
      const dayBookings = getFilteredBookingsForDay(targetDay);
      const clampedStart = new Date(Math.max(state.originalStart.getTime(), targetDay.getTime()));
      const targetDayEnd = new Date(targetDay);
      targetDayEnd.setHours(23, 59, 59, 999);
      const clampedEnd = new Date(Math.min(state.originalEnd.getTime(), targetDayEnd.getTime()));
      const clampedOriginalBooking = cloneBookingWithMutableStaffData(previewBooking, {
        start: clampedStart.getTime() > clampedEnd.getTime() ? clampedEnd : clampedStart,
        end: clampedEnd
      });
      const overridden = dayBookings.map((booking) => booking.id === previewBooking.id ? clampedOriginalBooking : booking);
      if (!overridden.find((booking) => booking.id === previewBooking.id)) {
        overridden.push(clampedOriginalBooking);
      }
      const layout = computeColumnLayout(overridden, activeGridConfig.value);
      return layout.positioned.find((entry) => entry.booking.id === previewBooking.id) ?? null;
    });
    const dragPreviewPositioned = computed(() => {
      const layout = originalPreviewLayout.value;
      const previewBooking = dragResize.previewBooking.value;
      if (!layout || !previewBooking) {
        return null;
      }
      return {
        ...layout,
        booking: cloneBookingWithMutableStaffData(previewBooking)
      };
    });
    const dragPreviewFixedStyle = computed(() => dragResize.getDragPreviewFixedStyle());
    const overlayPreviewPositioned = computed(() => {
      const preview = dragPreviewPositioned.value;
      const fixedStyle = dragPreviewFixedStyle.value;
      if (!preview || !fixedStyle) {
        return null;
      }
      const width = Number.parseFloat(fixedStyle.width ?? "");
      return {
        ...preview,
        rect: {
          top: 0,
          left: 0,
          width: Number.isFinite(width) ? width : preview.rect.width,
          height: preview.rect.height
        }
      };
    });
    function commitDragMove(bookingId, newStart, newEnd, originalDayIndex, newDayIndex) {
      const previousBooking = findTimeBookingById(bookingId);
      const previousSnapshot = previousBooking ? cloneTimeBookingForEvent(previousBooking) : null;
      if (previousBooking) {
        bookings.replaceTimeBookings([buildUpdatedTimeBooking(previousBooking, { start: newStart, end: newEnd })]);
      }
      bookings.invalidateLayoutCache();
      frozenGridConfig.value = null;
      const updatedBooking = findTimeBookingById(bookingId);
      dispatchCalendarInteractionEvent(DRAG_STOP_EVENT_NAME, {
        bookingId,
        booking: updatedBooking ? cloneTimeBookingForEvent(updatedBooking) : null,
        previousBooking: previousSnapshot,
        previousStart: (previousSnapshot == null ? void 0 : previousSnapshot.start) ?? null,
        previousEnd: (previousSnapshot == null ? void 0 : previousSnapshot.end) ?? null,
        newStart,
        newEnd,
        originalDayIndex,
        newDayIndex
      });
    }
    function onDragStart(booking, event, dayIndex, cardEl) {
      if (!isCardDragDropEnabled.value) {
        return;
      }
      frozenGridConfig.value = { ...activeGridConfig.value };
      closePopover();
      dragResize.startDrag(booking, event.clientX, event.clientY, dayIndex, cardEl == null ? void 0 : cardEl.getBoundingClientRect());
    }
    function onResizeTopStart(booking, event, dayIndex) {
      if (!isCardResizeEnabled.value || !canResizeBooking(booking)) {
        return;
      }
      frozenGridConfig.value = { ...activeGridConfig.value };
      dragResize.startResizeTop(booking, event.clientX, event.clientY, dayIndex);
    }
    function onResizeBottomStart(booking, event, dayIndex) {
      if (!isCardResizeEnabled.value || !canResizeBooking(booking)) {
        return;
      }
      frozenGridConfig.value = { ...activeGridConfig.value };
      dragResize.startResizeBottom(booking, event.clientX, event.clientY, dayIndex);
    }
    function onCardClick(booking, cardEl) {
      const isSameBooking = activePopoverBookingId.value === booking.id;
      const isSameAnchor = popoverAnchorEl.value === cardEl;
      if (showPopover.value && isSameBooking && isSameAnchor) {
        closePopover();
        return;
      }
      activePopoverBookingId.value = booking.id;
      popoverAnchorEl.value = cardEl;
      showPopover.value = true;
    }
    function onMonthDragBegin() {
      closePopover();
    }
    function onMonthDragEnd(booking, targetDate) {
      const latestBooking = findBookingById(booking.id) ?? booking;
      if ("start" in latestBooking) {
        if (startOfDay$1(latestBooking.start).getTime() === startOfDay$1(targetDate).getTime()) {
          return;
        }
        const previousSnapshot2 = cloneTimeBookingForEvent(latestBooking);
        const { start: newStart, end: newEnd } = buildMonthMovedTimeRange(latestBooking, targetDate);
        applySingleBookingUpdate(latestBooking.id, { start: newStart, end: newEnd });
        const updatedBooking2 = findTimeBookingById(latestBooking.id);
        const originalDayIndex2 = getMonthDayIndexFromDate(previousSnapshot2.start);
        const newDayIndex2 = getMonthDayIndexFromDate(newStart);
        dispatchCalendarInteractionEvent(DRAG_STOP_EVENT_NAME, {
          bookingId: latestBooking.id,
          booking: updatedBooking2 ? cloneTimeBookingForEvent(updatedBooking2) : null,
          previousBooking: previousSnapshot2,
          previousStart: previousSnapshot2.start,
          previousEnd: previousSnapshot2.end,
          newStart,
          newEnd,
          originalDayIndex: originalDayIndex2,
          newDayIndex: newDayIndex2,
          sourceView: "month"
        });
        return;
      }
      if (startOfDay$1(latestBooking.startDate).getTime() === startOfDay$1(targetDate).getTime()) {
        return;
      }
      const previousSnapshot = cloneAllDayBookingForEvent(latestBooking);
      const { startDate: newStartDate, endDate: newEndDate } = buildMonthMovedAllDayRange(latestBooking, targetDate);
      applySingleBookingUpdate(latestBooking.id, { startDate: newStartDate, endDate: newEndDate, isDayService: true });
      const updatedBooking = findAllDayBookingById(latestBooking.id);
      const originalDayIndex = getMonthDayIndexFromDate(previousSnapshot.startDate);
      const newDayIndex = getMonthDayIndexFromDate(newStartDate);
      dispatchCalendarInteractionEvent(DRAG_STOP_EVENT_NAME, {
        bookingId: latestBooking.id,
        booking: updatedBooking ? cloneAllDayBookingForEvent(updatedBooking) : null,
        previousBooking: previousSnapshot,
        previousStart: previousSnapshot.startDate,
        previousEnd: previousSnapshot.endDate,
        newStart: newStartDate,
        newEnd: newEndDate,
        start_date: formatDateForRawEntry(newStartDate),
        end_date: formatDateForRawEntry(newEndDate),
        previous_start_date: formatDateForRawEntry(previousSnapshot.startDate),
        previous_end_date: formatDateForRawEntry(previousSnapshot.endDate),
        originalDayIndex,
        newDayIndex,
        sourceView: "month"
      });
    }
    function onTimelineDragBegin() {
      frozenGridConfig.value = { ...activeGridConfig.value };
      closePopover();
    }
    function onTimelineDragEnd(bookingId, newStart, newEnd, originalDayIndex, newDayIndex) {
      commitDragMove(bookingId, newStart, newEnd, originalDayIndex, newDayIndex);
    }
    function onTimelineResizeEnd(bookingId, newStart, newEnd, resizeType) {
      const previousBooking = findTimeBookingById(bookingId);
      if (!previousBooking || !isCardResizeEnabled.value || !canResizeBooking(previousBooking)) {
        return;
      }
      const previousSnapshot = previousBooking ? cloneTimeBookingForEvent(previousBooking) : null;
      if (previousBooking) {
        bookings.replaceTimeBookings([buildUpdatedTimeBooking(previousBooking, { start: newStart, end: newEnd })]);
      }
      bookings.invalidateLayoutCache();
      frozenGridConfig.value = null;
      const updatedBooking = findTimeBookingById(bookingId);
      dispatchCalendarInteractionEvent(RESIZE_STOP_EVENT_NAME, {
        bookingId,
        booking: updatedBooking ? cloneTimeBookingForEvent(updatedBooking) : null,
        previousBooking: previousSnapshot,
        previousStart: (previousSnapshot == null ? void 0 : previousSnapshot.start) ?? null,
        previousEnd: (previousSnapshot == null ? void 0 : previousSnapshot.end) ?? null,
        newStart,
        newEnd,
        resizeType
      });
    }
    function closePopover() {
      showPopover.value = false;
      activePopoverBookingId.value = null;
      popoverAnchorEl.value = null;
    }
    function onClosePopoverEvent() {
      closePopover();
    }
    function bpaOpenAddNewAppointmentForm() {
      closePopover();
      window.dispatchEvent(new CustomEvent("bookingpress:open-add-new-appointment-form"));
    }
    function handleHeaderViewChange(view) {
      if (isMobileViewport.value) {
        calendar2.setView("day");
        return;
      }
      calendar2.setView(view);
    }
    function openCalendarSidebar(requestedState) {
      closePopover();
      dispatchCalendarInteractionEvent(OPEN_SIDEBAR_EVENT_NAME, {
        source: "mobile-header",
        currentView: calendar2.currentView.value,
        currentDate: new Date(calendar2.currentDate.value),
        requestedState
      });
    }
    function updateWeekStickyHeaderHeight() {
      var _a;
      weekStickyHeaderHeight.value = ((_a = weekStickyHeadersRef.value) == null ? void 0 : _a.offsetHeight) ?? 72;
    }
    function updateAllDayLayoutMetrics() {
      var _a, _b, _c, _d;
      weekViewportWidth.value = ((_a = scrollContainer.value) == null ? void 0 : _a.clientWidth) ?? ((_b = calendarRootRef.value) == null ? void 0 : _b.clientWidth) ?? (typeof window !== "undefined" ? window.innerWidth : 0);
      const headerGutter = (_c = scrollContainer.value) == null ? void 0 : _c.querySelector(".bpa-gutter-spacer");
      allDayGutterWidth.value = (headerGutter == null ? void 0 : headerGutter.getBoundingClientRect().width) ?? 92;
      const columnElements = Array.from(
        ((_d = gridArea.value) == null ? void 0 : _d.querySelectorAll(".time-column-container")) ?? []
      );
      if (columnElements.length > 0) {
        allDayColumnWidths.value = columnElements.map((element) => element.getBoundingClientRect().width);
        return;
      }
      allDayColumnWidths.value = columnInfos.value.map((info) => info.cappedWidth);
    }
    function onWindowResize() {
      syncResponsiveMode();
      updateAvailableViewportHeight();
      measureColumnWidth();
      updateWeekStickyHeaderHeight();
      updateAllDayLayoutMetrics();
    }
    function normalizeAllDayBooking(booking) {
      const metadata = isPlainObject2(booking.metadata) ? booking.metadata : null;
      const isPast = normalizeBooleanLike$2(booking.isPast) ?? normalizeBooleanLike$2(metadata == null ? void 0 : metadata.isPast);
      return {
        ...booking,
        ...isPast !== void 0 ? { isPast } : {},
        metadata: metadata ? {
          ...metadata,
          ...isPast !== void 0 ? { isPast } : {}
        } : isPast !== void 0 ? { isPast } : booking.metadata,
        startDate: booking.startDate instanceof Date ? booking.startDate : new Date(booking.startDate),
        endDate: booking.endDate instanceof Date ? booking.endDate : new Date(booking.endDate)
      };
    }
    function normalizeIncomingData(data) {
      if (Array.isArray(data)) {
        return normalizeRawEntries(data);
      }
      const dataset = data ?? {};
      const normalizedRaw = normalizeRawEntries(dataset.timeBookings || []);
      return {
        timeBookings: normalizedRaw.timeBookings,
        allDayBookings: [
          ...normalizedRaw.allDayBookings,
          ...(dataset.allDayBookings || []).map(normalizeAllDayBooking)
        ]
      };
    }
    function getIncomingDataCount(data) {
      if (Array.isArray(data)) {
        return data.length;
      }
      if (!isPlainObject2(data)) {
        return 0;
      }
      const timeBookings = Array.isArray(data.timeBookings) ? data.timeBookings.length : 0;
      const allDayBookings = Array.isArray(data.allDayBookings) ? data.allDayBookings.length : 0;
      return timeBookings + allDayBookings;
    }
    function getLoadChunkSize(totalCount) {
      if (totalCount <= DATA_LOAD_CHUNK_THRESHOLD) {
        return totalCount;
      }
      return Math.max(
        DATA_LOAD_CHUNK_MIN_SIZE,
        Math.min(DATA_LOAD_CHUNK_MAX_SIZE, Math.ceil(totalCount / 14))
      );
    }
    function yieldToBrowser() {
      if (typeof window === "undefined") {
        return Promise.resolve();
      }
      return new Promise((resolve) => {
        if (typeof window.requestAnimationFrame === "function") {
          window.requestAnimationFrame(() => resolve());
          return;
        }
        window.setTimeout(resolve, 0);
      });
    }
    function commitNormalizedChunk(normalized, mode, commitState) {
      if (mode === "append" || commitState.hasCommitted) {
        bookings.appendTimeBookings(normalized.timeBookings);
        bookings.appendAllDayBookings(normalized.allDayBookings);
      } else {
        bookings.setTimeBookings(normalized.timeBookings);
        bookings.setAllDayBookings(normalized.allDayBookings);
      }
      bookings.invalidateLayoutCache();
      commitState.hasCommitted = true;
    }
    async function processRawEntriesInChunks(rawEntries, mode, commitState) {
      if (rawEntries.length === 0) {
        return;
      }
      const chunkSize = getLoadChunkSize(rawEntries.length);
      for (let index = 0; index < rawEntries.length; index += chunkSize) {
        const chunk = rawEntries.slice(index, index + chunkSize);
        commitNormalizedChunk(normalizeRawEntries(chunk), mode, commitState);
        if (index + chunkSize < rawEntries.length) {
          await yieldToBrowser();
        }
      }
    }
    async function processAllDayBookingsInChunks(allDayBookings, mode, commitState) {
      if (allDayBookings.length === 0) {
        return;
      }
      const chunkSize = getLoadChunkSize(allDayBookings.length);
      for (let index = 0; index < allDayBookings.length; index += chunkSize) {
        const chunk = allDayBookings.slice(index, index + chunkSize);
        commitNormalizedChunk(
          {
            timeBookings: [],
            allDayBookings: chunk.map(normalizeAllDayBooking)
          },
          mode,
          commitState
        );
        if (index + chunkSize < allDayBookings.length) {
          await yieldToBrowser();
        }
      }
    }
    async function processIncomingDataInChunks(data, mode) {
      if (mode === "replace") {
        bookings.setTimeBookings([]);
        bookings.setAllDayBookings([]);
        bookings.invalidateLayoutCache();
      }
      const commitState = { hasCommitted: false };
      if (Array.isArray(data)) {
        await processRawEntriesInChunks(data, mode, commitState);
        return;
      }
      const dataset = data ?? {};
      await processRawEntriesInChunks(dataset.timeBookings || [], mode, commitState);
      await processAllDayBookingsInChunks(dataset.allDayBookings || [], mode, commitState);
    }
    function queueDataMutation(mutation) {
      dataMutationQueue = dataMutationQueue.then(() => processDataMutation(mutation)).catch((error) => {
        console.error("Failed to apply calendar data mutation.", error);
      });
    }
    async function processDataMutation(mutation) {
      if (!mutation) {
        return;
      }
      if (mutation.mode === "update-booking") {
        applySingleBookingUpdate(mutation.bookingId, mutation.updates);
        return;
      }
      if (mutation.data === void 0 || mutation.data === null) {
        return;
      }
      const shouldChunk = getIncomingDataCount(mutation.data) >= DATA_LOAD_CHUNK_THRESHOLD;
      if (shouldChunk) {
        await processIncomingDataInChunks(mutation.data, mutation.mode);
        return;
      }
      const normalized = normalizeIncomingData(mutation.data);
      if (mutation.mode === "append") {
        bookings.appendTimeBookings(normalized.timeBookings);
        bookings.appendAllDayBookings(normalized.allDayBookings);
      } else {
        bookings.setTimeBookings(normalized.timeBookings);
        bookings.setAllDayBookings(normalized.allDayBookings);
      }
      bookings.invalidateLayoutCache();
    }
    function formatDateForRawEntry(date) {
      const year = date.getFullYear();
      const month = `${date.getMonth() + 1}`.padStart(2, "0");
      const day = `${date.getDate()}`.padStart(2, "0");
      return `${year}-${month}-${day}`;
    }
    function formatTimeForRawEntry(date) {
      return formatTimeForDataEntry(date);
    }
    function formatTimeForTwentyFourHourEntry(date) {
      const hours = `${date.getHours()}`.padStart(2, "0");
      const minutes = `${date.getMinutes()}`.padStart(2, "0");
      const seconds = `${date.getSeconds()}`.padStart(2, "0");
      return `${hours}:${minutes}:${seconds}`;
    }
    function normalizeUpdatedDateValue(value, fallback) {
      if (value instanceof Date) {
        return Number.isNaN(value.getTime()) ? new Date(fallback) : new Date(value);
      }
      if (typeof value === "string" || typeof value === "number") {
        const parsed = new Date(value);
        return Number.isNaN(parsed.getTime()) ? new Date(fallback) : parsed;
      }
      return new Date(fallback);
    }
    function normalizeThemeValue(value, fallback) {
      if (isPlainObject2(value) && typeof value.bg === "string" && typeof value.border === "string" && typeof value.text === "string") {
        return {
          bg: value.bg,
          border: value.border,
          text: value.text
        };
      }
      return fallback;
    }
    function cloneStaffAssignments2(items) {
      return items.map((item) => ({
        staffId: item.staffId,
        staffName: item.staffName
      }));
    }
    function cloneBookingWithMutableStaffData(booking, overrides = {}) {
      const { StaffData, ...rest } = booking;
      return {
        ...rest,
        ...StaffData ? { StaffData: cloneStaffAssignments2(StaffData) } : {},
        ...overrides
      };
    }
    function buildRawTimeBookingEntry(existing) {
      const metadata = isPlainObject2(existing.metadata) ? existing.metadata : {};
      const allowResize = normalizeBooleanLike$2(existing.allowResize) ?? normalizeBooleanLike$2(metadata.allowResize);
      const preventResize = normalizeBooleanLike$2(existing.preventResize) ?? normalizeBooleanLike$2(metadata.preventResize);
      const staffSummary = resolveBookingStaffSummary(existing);
      const staffData = cloneStaffAssignments2(staffSummary.items);
      return {
        id: existing.id,
        customerName: existing.customerName,
        isPast: existing.isPast,
        ...allowResize !== void 0 ? { allowResize } : {},
        ...preventResize !== void 0 ? { preventResize } : {},
        start_date: formatDateForRawEntry(existing.start),
        end_date: formatDateForRawEntry(existing.end),
        start_time: formatTimeForRawEntry(existing.start),
        end_time: formatTimeForRawEntry(existing.end),
        start_time_val: formatTimeForExtendedDataEntry(existing.start, existing.start),
        end_time_val: formatTimeForExtendedDataEntry(existing.end, existing.start),
        serviceName: existing.serviceName,
        serviceId: existing.serviceId,
        status: existing.status,
        statusLabel: typeof metadata.statusLabel === "string" ? metadata.statusLabel : void 0,
        staffMemberName: staffSummary.staffLabel || existing.staffMemberName,
        ...staffSummary.items.length === 1 ? { staffMemberId: staffSummary.staffValues[0] } : {},
        ...staffData.length > 0 ? { StaffData: staffData } : {},
        ...staffSummary.isMultiStaff ? { isMultiStaff: true } : {},
        location: typeof metadata.location === "string" ? metadata.location : void 0,
        price: typeof metadata.price === "string" || typeof metadata.price === "number" ? metadata.price : void 0,
        theme: existing.theme,
        metadata
      };
    }
    function buildRawAllDayBookingEntry(existing) {
      const metadata = isPlainObject2(existing.metadata) ? existing.metadata : {};
      const staffSummary = resolveBookingStaffSummary(existing);
      const staffData = cloneStaffAssignments2(staffSummary.items);
      return {
        id: existing.id,
        customerName: existing.customerName || "",
        isPast: typeof existing.isPast === "boolean" ? existing.isPast : metadata.isPast,
        isDayService: true,
        start_date: formatDateForRawEntry(existing.startDate),
        end_date: formatDateForRawEntry(existing.endDate),
        serviceName: existing.serviceName || "",
        serviceId: existing.serviceId,
        status: existing.status,
        statusLabel: typeof metadata.statusLabel === "string" ? metadata.statusLabel : void 0,
        staffMemberName: staffSummary.staffLabel || existing.staffMemberName,
        ...staffSummary.items.length === 1 ? { staffMemberId: staffSummary.staffValues[0] } : {},
        ...staffData.length > 0 ? { StaffData: staffData } : {},
        ...staffSummary.isMultiStaff ? { isMultiStaff: true } : {},
        location: typeof metadata.location === "string" ? metadata.location : void 0,
        price: typeof metadata.price === "string" || typeof metadata.price === "number" ? metadata.price : void 0,
        theme: existing.theme,
        metadata
      };
    }
    function buildUpdatedTimeBooking(existing, updates) {
      var _a;
      const patch = isPlainObject2(updates) ? updates : {};
      const metadataPatch = isPlainObject2(patch.metadata) ? patch.metadata : {};
      const existingStatusLabel = typeof ((_a = existing.metadata) == null ? void 0 : _a.statusLabel) === "string" ? existing.metadata.statusLabel : void 0;
      const metadataStatusLabel = typeof metadataPatch.statusLabel === "string" ? metadataPatch.statusLabel : void 0;
      const existingRaw = buildRawTimeBookingEntry(existing);
      const mergedRaw = {
        ...existingRaw,
        ...patch,
        id: existing.id
      };
      if ("metadata" in patch) {
        mergedRaw.metadata = {
          ...isPlainObject2(existingRaw.metadata) ? existingRaw.metadata : {},
          ...isPlainObject2(metadataPatch) ? metadataPatch : {}
        };
      }
      if ("customerName" in metadataPatch && !("customerName" in patch) && typeof metadataPatch.customerName === "string") {
        mergedRaw.customerName = metadataPatch.customerName;
      }
      if ("serviceName" in metadataPatch && !("serviceName" in patch) && typeof metadataPatch.serviceName === "string") {
        mergedRaw.serviceName = metadataPatch.serviceName;
      }
      if ("serviceId" in metadataPatch && !("serviceId" in patch)) {
        mergedRaw.serviceId = metadataPatch.serviceId;
      }
      if ("isPast" in patch) {
        mergedRaw.isPast = typeof patch.isPast === "boolean" ? patch.isPast : void 0;
      } else if ("isPast" in metadataPatch) {
        mergedRaw.isPast = typeof metadataPatch.isPast === "boolean" ? metadataPatch.isPast : void 0;
      }
      if ("status" in metadataPatch && !("status" in patch)) {
        mergedRaw.status = metadataPatch.status;
      }
      if ("staffMemberName" in patch) {
        mergedRaw.staffMemberName = patch.staffMemberName;
      } else if ("staffMemberName" in metadataPatch) {
        mergedRaw.staffMemberName = metadataPatch.staffMemberName;
      }
      if ("staffMemberId" in patch) {
        mergedRaw.staffMemberId = patch.staffMemberId;
      } else if ("staffMemberId" in metadataPatch) {
        mergedRaw.staffMemberId = metadataPatch.staffMemberId;
      }
      if ("StaffData" in patch || "staffData" in patch) {
        mergedRaw.StaffData = "StaffData" in patch ? patch.StaffData : patch.staffData;
      } else if ("StaffData" in metadataPatch || "staffData" in metadataPatch) {
        mergedRaw.StaffData = "StaffData" in metadataPatch ? metadataPatch.StaffData : metadataPatch.staffData;
      }
      if ("isMultiStaff" in patch) {
        mergedRaw.isMultiStaff = patch.isMultiStaff;
      } else if ("isMultiStaff" in metadataPatch) {
        mergedRaw.isMultiStaff = metadataPatch.isMultiStaff;
      }
      if ("location" in patch) {
        mergedRaw.location = typeof patch.location === "string" ? patch.location : void 0;
      } else if ("location" in metadataPatch) {
        mergedRaw.location = typeof metadataPatch.location === "string" ? metadataPatch.location : void 0;
      }
      if ("price" in patch) {
        mergedRaw.price = patch.price;
      } else if ("price" in metadataPatch) {
        mergedRaw.price = metadataPatch.price;
      }
      const hasUpdatedStatus = "status" in patch || "status" in metadataPatch;
      const hasExplicitStatusLabel = "statusLabel" in patch || metadataStatusLabel !== void 0 && metadataStatusLabel !== existingStatusLabel;
      if ("statusLabel" in patch) {
        mergedRaw.statusLabel = typeof patch.statusLabel === "string" ? patch.statusLabel : void 0;
      } else if (hasExplicitStatusLabel) {
        mergedRaw.statusLabel = metadataStatusLabel;
      } else if (hasUpdatedStatus && !hasExplicitStatusLabel) {
        delete mergedRaw.statusLabel;
      }
      if ("theme" in metadataPatch && !("theme" in patch)) {
        mergedRaw.theme = normalizeThemeValue(metadataPatch.theme, existing.theme);
      }
      const hasExplicitTimeValuePatch = "start_time_val" in patch || "end_time_val" in patch;
      const hasStandardDateOrTimePatch = "start_date" in patch || "end_date" in patch || "start_time" in patch || "end_time" in patch;
      const usesDirectDateValues = "start" in patch || "end" in patch;
      if (usesDirectDateValues) {
        const nextStart = normalizeUpdatedDateValue(patch.start, existing.start);
        const nextEnd = normalizeUpdatedDateValue(patch.end, existing.end);
        mergedRaw.start = nextStart;
        mergedRaw.end = nextEnd;
        mergedRaw.start_date = formatDateForRawEntry(nextStart);
        mergedRaw.end_date = formatDateForRawEntry(nextEnd);
        mergedRaw.start_time = formatTimeForRawEntry(nextStart);
        mergedRaw.end_time = formatTimeForRawEntry(nextEnd);
        mergedRaw.start_time_val = formatTimeForExtendedDataEntry(nextStart, nextStart);
        mergedRaw.end_time_val = formatTimeForExtendedDataEntry(nextEnd, nextStart);
      } else if (hasStandardDateOrTimePatch && !hasExplicitTimeValuePatch) {
        delete mergedRaw.start_time_val;
        delete mergedRaw.end_time_val;
      }
      const normalized = toTimeBooking(mergedRaw);
      const nextMetadataPatch = { ...metadataPatch };
      if (hasUpdatedStatus && !hasExplicitStatusLabel) {
        delete nextMetadataPatch.statusLabel;
      }
      const nextMetadata = {
        ...isPlainObject2(existing.metadata) ? existing.metadata : {},
        ...isPlainObject2(normalized.metadata) ? normalized.metadata : {},
        ...nextMetadataPatch
      };
      const resourceId = typeof patch.resourceId === "string" && patch.resourceId.trim() ? patch.resourceId.trim() : existing.resourceId;
      return {
        ...existing,
        ...normalized,
        id: existing.id,
        resourceId,
        theme: normalizeThemeValue(patch.theme, normalizeThemeValue(metadataPatch.theme, normalized.theme ?? existing.theme)),
        metadata: nextMetadata
      };
    }
    function buildUpdatedAllDayBooking(existing, updates) {
      const patch = isPlainObject2(updates) ? updates : {};
      const metadataPatch = isPlainObject2(patch.metadata) ? patch.metadata : {};
      const existingMetadata = isPlainObject2(existing.metadata) ? existing.metadata : {};
      const hasExplicitIsPast = "isPast" in patch || "isPast" in metadataPatch;
      const startDateSource = patch.startDate ?? patch.start_date ?? metadataPatch.startDate ?? metadataPatch.start_date;
      const endDateSource = patch.endDate ?? patch.end_date ?? metadataPatch.endDate ?? metadataPatch.end_date;
      const nextStartDate = startDateSource instanceof Date || typeof startDateSource === "string" ? new Date(startDateSource) : existing.startDate;
      const nextEndDate = endDateSource instanceof Date || typeof endDateSource === "string" ? new Date(endDateSource) : existing.endDate;
      const customerName = typeof patch.customerName === "string" ? patch.customerName : typeof metadataPatch.customerName === "string" ? metadataPatch.customerName : existing.customerName;
      const statusSource = "status" in patch ? patch.status : metadataPatch.status;
      const nextStatus = statusSource === void 0 ? existing.status : normalizeFilterValue2(statusSource) ?? void 0;
      const nextIsPast = hasExplicitIsPast ? normalizeBooleanLike$2("isPast" in patch ? patch.isPast : metadataPatch.isPast) : typeof existing.isPast === "boolean" ? existing.isPast : normalizeBooleanLike$2(existingMetadata.isPast);
      const nextMetadata = {
        ...existingMetadata,
        ...metadataPatch,
        ...nextIsPast !== void 0 ? { isPast: nextIsPast } : {}
      };
      const serviceSummary = resolveBookingServiceSummary({
        serviceName: typeof patch.serviceName === "string" ? patch.serviceName : typeof metadataPatch.serviceName === "string" ? metadataPatch.serviceName : existing.serviceName,
        serviceId: "serviceId" in patch ? patch.serviceId : metadataPatch.serviceId,
        category: metadataPatch.category ?? existingMetadata.category,
        categoryId: metadataPatch.categoryId ?? existingMetadata.categoryId,
        categoryName: metadataPatch.categoryName ?? existingMetadata.categoryName,
        servicesData: "servicesData" in patch ? patch.servicesData : "servicesData" in metadataPatch ? metadataPatch.servicesData : existingMetadata.servicesData,
        isMultiService: "isMultiService" in patch ? patch.isMultiService : "isMultiService" in metadataPatch ? metadataPatch.isMultiService : existingMetadata.isMultiService,
        metadata: nextMetadata
      });
      const staffSummary = resolveBookingStaffSummary({
        staffMemberName: "staffMemberName" in patch ? patch.staffMemberName : "staffMemberName" in metadataPatch ? metadataPatch.staffMemberName : existing.staffMemberName,
        staffMemberId: "staffMemberId" in patch ? patch.staffMemberId : "staffMemberId" in metadataPatch ? metadataPatch.staffMemberId : existing.staffMemberId,
        StaffData: "StaffData" in patch ? patch.StaffData : "staffData" in patch ? patch.staffData : "StaffData" in metadataPatch ? metadataPatch.StaffData : "staffData" in metadataPatch ? metadataPatch.staffData : existing.StaffData,
        isMultiStaff: "isMultiStaff" in patch ? patch.isMultiStaff : "isMultiStaff" in metadataPatch ? metadataPatch.isMultiStaff : existing.isMultiStaff,
        metadata: nextMetadata
      });
      const nextServiceName = serviceSummary.isMultiService ? serviceSummary.serviceLabel : serviceSummary.serviceLabel || existing.serviceName;
      const nextServiceId = serviceSummary.isMultiService ? serviceSummary.items.length === 1 ? serviceSummary.serviceValues[0] : void 0 : serviceSummary.serviceValues[0] ?? ("serviceId" in patch ? normalizeFilterValue2(patch.serviceId) : "serviceId" in metadataPatch ? normalizeFilterValue2(metadataPatch.serviceId) : existing.serviceId);
      const nextStaffName = staffSummary.staffLabel || existing.staffMemberName;
      const nextStaffId = staffSummary.items.length === 1 ? staffSummary.staffValues[0] : void 0;
      const nextStaffData = cloneStaffAssignments2(staffSummary.items);
      if (hasExplicitIsPast && nextIsPast === void 0) {
        delete nextMetadata.isPast;
      }
      const nextMetadataWithDetails = {
        ...nextMetadata,
        ...serviceSummary.isMultiService ? { isMultiService: true } : {},
        serviceName: nextServiceName,
        serviceId: nextServiceId,
        ...nextStaffName ? { staffMemberName: nextStaffName } : {},
        ...nextStaffId !== void 0 ? { staffMemberId: nextStaffId } : {},
        ...nextStaffData.length > 0 ? { StaffData: nextStaffData } : {},
        ...staffSummary.isMultiStaff ? { isMultiStaff: true } : {}
      };
      return {
        ...existing,
        ...typeof patch.title === "string" ? { title: patch.title } : {},
        startDate: nextStartDate,
        endDate: nextEndDate,
        ...hasExplicitIsPast || typeof existing.isPast === "boolean" || nextIsPast !== void 0 ? { isPast: nextIsPast } : {},
        ...typeof customerName === "string" && customerName.trim() ? { customerName } : {},
        serviceName: nextServiceName,
        serviceId: nextServiceId,
        ...nextStaffName ? { staffMemberName: nextStaffName } : {},
        ...nextStaffId !== void 0 ? { staffMemberId: nextStaffId } : {},
        ...nextStaffData.length > 0 ? { StaffData: nextStaffData } : {},
        ...staffSummary.isMultiStaff ? { isMultiStaff: true } : {},
        ...nextStatus !== void 0 ? { status: nextStatus } : {},
        theme: normalizeThemeValue(patch.theme, normalizeThemeValue(metadataPatch.theme, existing.theme)),
        metadata: nextMetadataWithDetails
      };
    }
    function isBookingVisible(booking) {
      if ("start" in booking && "end" in booking) {
        return filteredTimeBookings.value.some((entry) => entry.id === booking.id);
      }
      return filteredAllDayBookings.value.some((entry) => entry.id === booking.id);
    }
    function syncActivePopoverAfterBookingUpdate(bookingId) {
      if (!showPopover.value || activePopoverBookingId.value !== bookingId) {
        return;
      }
      nextTick(() => {
        var _a;
        const booking = activePopoverBooking.value;
        if (!booking || !((_a = popoverAnchorEl.value) == null ? void 0 : _a.isConnected) || !isBookingVisible(booking)) {
          closePopover();
        }
      });
    }
    function applySingleBookingUpdate(bookingId, updates) {
      var _a;
      const timeBooking = findTimeBookingById(bookingId);
      const allDayBooking = findAllDayBookingById(bookingId);
      const existing = timeBooking || allDayBooking;
      if (!existing) {
        return;
      }
      const patch = isPlainObject2(updates) ? updates : {};
      const metadataPatch = isPlainObject2(patch.metadata) ? patch.metadata : {};
      const mergedIsDayService = "isDayService" in patch ? normalizeBooleanLike$2(patch.isDayService) : "isDayService" in metadataPatch ? normalizeBooleanLike$2(metadataPatch.isDayService) : ((_a = existing.metadata) == null ? void 0 : _a.isDayService) ?? false;
      const shouldBeAllDay = !!mergedIsDayService;
      if (shouldBeAllDay) {
        const updated = allDayBooking ? buildUpdatedAllDayBooking(allDayBooking, updates) : buildUpdatedAllDayBooking(toAllDayBooking(buildRawTimeBookingEntry(timeBooking)), updates);
        if (timeBooking) {
          bookings.removeTimeBooking(bookingId);
        }
        bookings.replaceAllDayBookings([updated]);
      } else {
        const updated = timeBooking ? buildUpdatedTimeBooking(timeBooking, updates) : buildUpdatedTimeBooking(toTimeBooking(buildRawAllDayBookingEntry(allDayBooking)), updates);
        if (allDayBooking) {
          bookings.removeAllDayBooking(bookingId);
        }
        bookings.replaceTimeBookings([updated]);
      }
      bookings.invalidateLayoutCache();
      syncActivePopoverAfterBookingUpdate(bookingId);
    }
    function getInitialBookingFilters(config) {
      var _a, _b;
      const providedValues = config.bookingFilterValues ?? config.filterValues ?? ((_a = config.bookingFilters) == null ? void 0 : _a.values) ?? ((_b = config.filters) == null ? void 0 : _b.values) ?? {};
      return {
        service: normalizeFilterValue2(providedValues.service),
        status: normalizeFilterValue2(providedValues.status),
        employee: normalizeFilterValue2(providedValues.employee),
        location: normalizeFilterValue2(providedValues.location),
        category: normalizeFilterValue2(providedValues.category)
      };
    }
    function normalizeInteractionConfig(config) {
      const providedConfig = isPlainObject2(config) ? config : {};
      return {
        ...DEFAULT_INTERACTION_CONFIG,
        enableCardDragDrop: normalizeBoolean(
          providedConfig.enableCardDragDrop,
          DEFAULT_INTERACTION_CONFIG.enableCardDragDrop
        ),
        enableCardResize: normalizeBoolean(
          providedConfig.enableCardResize,
          DEFAULT_INTERACTION_CONFIG.enableCardResize
        ),
        showAddAppointmentButton: normalizeBoolean(
          providedConfig.showAddAppointmentButton,
          DEFAULT_INTERACTION_CONFIG.showAddAppointmentButton
        ),
        showEditAppointmentButton: normalizeBoolean(
          providedConfig.showEditAppointmentButton,
          DEFAULT_INTERACTION_CONFIG.showEditAppointmentButton
        )
      };
    }
    function normalizePopoverConfig(config, interactionFallback = DEFAULT_INTERACTION_CONFIG) {
      const providedConfig = isPlainObject2(config) ? config : {};
      const additionalDetails = Array.isArray(providedConfig.additionalDetails) ? providedConfig.additionalDetails.map((detail) => normalizePopoverAdditionalDetail(detail)).filter((detail) => detail !== null) : [];
      return {
        ...DEFAULT_POPOVER_CONFIG,
        customerName: normalizeBoolean(providedConfig.customerName, DEFAULT_POPOVER_CONFIG.customerName),
        dateTime: normalizeBoolean(providedConfig.dateTime, DEFAULT_POPOVER_CONFIG.dateTime),
        serviceName: normalizeBoolean(providedConfig.serviceName, DEFAULT_POPOVER_CONFIG.serviceName),
        customerEmail: normalizeBoolean(providedConfig.customerEmail, DEFAULT_POPOVER_CONFIG.customerEmail),
        customerPhone: normalizeBoolean(providedConfig.customerPhone, DEFAULT_POPOVER_CONFIG.customerPhone),
        staffMemberName: normalizeBoolean(providedConfig.staffMemberName, DEFAULT_POPOVER_CONFIG.staffMemberName),
        location: normalizeBoolean(providedConfig.location, DEFAULT_POPOVER_CONFIG.location),
        numberOfPerson: normalizeBoolean(providedConfig.numberOfPerson, DEFAULT_POPOVER_CONFIG.numberOfPerson),
        price: normalizeBoolean(providedConfig.price, DEFAULT_POPOVER_CONFIG.price),
        showStatusDropdown: normalizeBoolean(
          providedConfig.showStatusDropdown,
          DEFAULT_POPOVER_CONFIG.showStatusDropdown
        ),
        enableStatusDropdown: normalizeBoolean(
          providedConfig.enableStatusDropdown,
          DEFAULT_POPOVER_CONFIG.enableStatusDropdown
        ),
        showEditAppointmentButton: normalizeBoolean(
          providedConfig.showEditAppointmentButton,
          interactionFallback.showEditAppointmentButton
        ),
        enableEditAppointmentButton: normalizeBoolean(
          providedConfig.enableEditAppointmentButton,
          DEFAULT_POPOVER_CONFIG.enableEditAppointmentButton
        ),
        showRescheduleAppointmentButton: normalizeBoolean(
          providedConfig.showRescheduleAppointmentButton,
          DEFAULT_POPOVER_CONFIG.showRescheduleAppointmentButton
        ),
        enableRescheduleAppointmentButton: normalizeBoolean(
          providedConfig.enableRescheduleAppointmentButton,
          DEFAULT_POPOVER_CONFIG.enableRescheduleAppointmentButton
        ),
        additionalDetails
      };
    }
    function normalizePopoverAdditionalDetail(detail) {
      if (typeof detail === "string" || typeof detail === "number") {
        const label2 = String(detail).trim();
        return label2 ? { id: label2, label: label2, visible: true } : null;
      }
      if (!isPlainObject2(detail)) {
        return null;
      }
      const rawId = typeof detail.id === "string" || typeof detail.id === "number" ? String(detail.id).trim() : "";
      const label = typeof detail.label === "string" ? detail.label.trim() : "";
      const visible = typeof detail.visible === "boolean" ? detail.visible : true;
      if (!rawId && !label) {
        return null;
      }
      return {
        ...rawId ? { id: rawId } : {},
        ...label ? { label } : {},
        visible
      };
    }
    function normalizePopoverStatusOptions(configValue, labelOverrides) {
      if (!Array.isArray(configValue)) {
        return getDefaultStatusOptions(labelOverrides);
      }
      const configuredOptions = /* @__PURE__ */ new Map();
      configValue.forEach((entry) => {
        if (!isPlainObject2(entry)) {
          return;
        }
        const option = normalizePopoverStatusOption(entry, labelOverrides);
        if (option) {
          configuredOptions.set(String(option.value), option);
        }
      });
      return Array.from(configuredOptions.values());
    }
    function normalizePopoverStatusOption(option, labelOverrides) {
      const value = normalizeFilterValue2(option.value);
      const label = typeof option.label === "string" ? option.label.trim() : "";
      const statusDefinition = getStatusDefinition(value) ?? getStatusDefinition(label);
      const resolvedValue = value ?? (statusDefinition == null ? void 0 : statusDefinition.value) ?? null;
      const resolvedLabel = resolveStatusOptionLabel(resolvedValue, label, labelOverrides);
      if (!resolvedLabel || resolvedValue === null) {
        return null;
      }
      return enhanceStatusOption({
        label: resolvedLabel,
        value: resolvedValue,
        disabled: option.disabled === true
      });
    }
    function mergeFilterFieldConfig(defaults, overrides) {
      return {
        ...defaults,
        ...overrides,
        options: (overrides == null ? void 0 : overrides.options) ?? defaults.options
      };
    }
    function normalizeAllowedFilterValues(values) {
      if (!Array.isArray(values)) {
        return null;
      }
      const normalizedValues = values.flatMap((value) => normalizeFilterValues2(value));
      return normalizedValues.length ? normalizedValues : null;
    }
    function withAllowedOptions(config) {
      const allowedValues = normalizeAllowedFilterValues(config == null ? void 0 : config.allowedValues);
      const options = (config == null ? void 0 : config.options) ?? [];
      if (!(allowedValues == null ? void 0 : allowedValues.length) || !options.length) {
        return config;
      }
      const allowedValueSet = new Set(allowedValues.map((value) => String(value)));
      return {
        ...config,
        options: options.filter((option) => allowedValueSet.has(String(option.value)))
      };
    }
    function matchesAllowedFilterValues(allowedValues, bookingValue) {
      const normalizedValues = normalizeAllowedFilterValues(allowedValues);
      if (!(normalizedValues == null ? void 0 : normalizedValues.length)) {
        return true;
      }
      const bookingValues = normalizeFilterValues2(bookingValue);
      if (!bookingValues.length) {
        return false;
      }
      return normalizedValues.some(
        (value) => bookingValues.some((bookingEntry) => String(value) === String(bookingEntry))
      );
    }
    function sanitizeFieldFilterValue(value, config) {
      if (!(config == null ? void 0 : config.visible)) {
        return null;
      }
      if (Array.isArray(value)) {
        const normalizedValues = value.map((v) => normalizeFilterValue2(v)).filter((v) => v !== null && matchesAllowedFilterValues(config.allowedValues, v));
        return normalizedValues.length > 0 ? normalizedValues : null;
      }
      const normalizedValue = normalizeFilterValue2(value);
      return matchesAllowedFilterValues(config.allowedValues, normalizedValue) ? normalizedValue : null;
    }
    function sanitizeBookingFilters(filters, config) {
      return {
        service: sanitizeFieldFilterValue(filters.service, config.service),
        status: sanitizeFieldFilterValue(filters.status, config.status),
        employee: sanitizeFieldFilterValue(filters.employee, config.employee),
        location: sanitizeFieldFilterValue(filters.location, config.location),
        category: sanitizeFieldFilterValue(filters.category, config.category)
      };
    }
    function buildStatusOptions(bookings2) {
      const optionsByValue = /* @__PURE__ */ new Map();
      bookings2.forEach((booking) => {
        var _a;
        const value = getBookingFilterValue("status", booking);
        if (value === null) {
          return;
        }
        const key = String(value);
        if (optionsByValue.has(key)) {
          return;
        }
        const statusLabel = typeof ((_a = booking.metadata) == null ? void 0 : _a.statusLabel) === "string" ? booking.metadata.statusLabel.trim() : "";
        const resolvedLabel = resolveStatusOptionLabel(value, statusLabel, uiText.value.statuses);
        optionsByValue.set(key, enhanceStatusOption({
          label: resolvedLabel || formatOptionLabel(key),
          value
        }));
      });
      return sortStatusOptions(Array.from(optionsByValue.values()));
    }
    function resolveStatusOptionLabel(statusValue, fallbackLabel, labelOverrides) {
      const statusDefinition = getStatusDefinition(statusValue) ?? getStatusDefinition(fallbackLabel);
      if (!statusDefinition) {
        return fallbackLabel.trim();
      }
      const overrideKey = statusDefinition.key === "no-show" ? "noShow" : statusDefinition.key;
      const translatedLabel = labelOverrides[overrideKey] ?? statusDefinition.label;
      const normalizedFallback = fallbackLabel.trim();
      if (!normalizedFallback) {
        return translatedLabel;
      }
      if (getStatusDefinition(normalizedFallback) === statusDefinition) {
        return translatedLabel;
      }
      return normalizedFallback;
    }
    function buildServiceOptions(bookings2) {
      const optionsByValue = /* @__PURE__ */ new Map();
      bookings2.forEach((booking) => {
        const summary = resolveBookingServiceSummary(booking);
        summary.items.forEach((item) => {
          const value = item.serviceId ?? (item.serviceName || null);
          const label = item.serviceName || formatOptionLabel(String(value ?? ""));
          if (value === null || !label) {
            return;
          }
          const key = String(value);
          if (optionsByValue.has(key)) {
            return;
          }
          optionsByValue.set(key, {
            label,
            value
          });
        });
      });
      return Array.from(optionsByValue.values()).sort((left, right) => String(left.label).localeCompare(String(right.label), void 0, { numeric: true }));
    }
    function formatOptionLabel(value) {
      return value.split("-").map((segment) => segment.charAt(0).toUpperCase() + segment.slice(1)).join(" ");
    }
    function normalizeFilterValue2(value) {
      if (Array.isArray(value)) {
        return value.map((v) => normalizeFilterValue2(v)).filter((v) => v !== null);
      }
      if (typeof value === "string") {
        const trimmedValue = value.trim();
        return trimmedValue ? trimmedValue : null;
      }
      if (typeof value === "number" || typeof value === "boolean") {
        return value;
      }
      return null;
    }
    function normalizeFilterValues2(value) {
      if (Array.isArray(value)) {
        return value.flatMap((entry) => normalizeFilterValues2(entry));
      }
      const normalized = normalizeFilterValue2(value);
      return normalized === null ? [] : [normalized];
    }
    function getBookingMetadataText(booking, key) {
      var _a;
      const normalizedKey = key.trim().toLowerCase().replace(/[^a-z0-9]+/g, "");
      if (normalizedKey === "servicename" || normalizedKey === "serviceid" || normalizedKey === "serviceids" || normalizedKey === "servicenames" || normalizedKey === "servicelabel" || normalizedKey === "servicesdata") {
        const serviceLabel = resolveBookingServiceSummary(booking).serviceLabel;
        if (serviceLabel) {
          return serviceLabel;
        }
      }
      if (normalizedKey === "category" || normalizedKey === "categoryname" || normalizedKey === "categoryid" || normalizedKey === "categoryids" || normalizedKey === "categorynames" || normalizedKey === "categorylabel" || normalizedKey === "servicecategoryname" || normalizedKey === "servicecategorynames" || normalizedKey === "servicecategorylabel" || normalizedKey === "servicecategoryid" || normalizedKey === "servicecategoryids") {
        const categoryLabel = resolveBookingServiceSummary(booking).categoryLabel;
        if (categoryLabel) {
          return categoryLabel;
        }
      }
      const value = (_a = booking.metadata) == null ? void 0 : _a[key];
      if (Array.isArray(value)) {
        return value.map((v) => String(v).trim()).filter(Boolean).join(", ");
      }
      if (typeof value === "string") {
        return value.trim();
      }
      if (typeof value === "number" || typeof value === "boolean") {
        return String(value);
      }
      return "";
    }
    function normalizeBoolean(value, fallback) {
      return typeof value === "boolean" ? value : fallback;
    }
    function isPlainObject2(value) {
      return typeof value === "object" && value !== null && !Array.isArray(value);
    }
    function matchesFilterValue(selectedValue, bookingRawValue) {
      const selectedValues = normalizeFilterValues2(selectedValue);
      if (!selectedValues.length) {
        return true;
      }
      const bookingValues = normalizeFilterValues2(bookingRawValue);
      if (!bookingValues.length) {
        return false;
      }
      return selectedValues.some(
        (sel) => bookingValues.some((book) => String(book) === String(sel ?? ""))
      );
    }
    function getBookingFilterValue(fieldId, booking) {
      var _a, _b, _c;
      const summary = resolveBookingServiceSummary(booking);
      const staffSummary = resolveBookingStaffSummary(booking);
      switch (fieldId) {
        case "service":
          return summary.serviceValues.length > 1 ? summary.serviceValues : summary.serviceValues[0] ?? null;
        case "status":
          return "status" in booking ? normalizeFilterValue2(booking.status) : normalizeFilterValue2((_a = booking.metadata) == null ? void 0 : _a.status);
        case "employee":
          return staffSummary.staffValues.length > 1 ? staffSummary.staffValues : staffSummary.staffValues[0] ?? null;
        case "location":
          return normalizeFilterValue2(((_b = booking.metadata) == null ? void 0 : _b.locationId) ?? ((_c = booking.metadata) == null ? void 0 : _c.location));
        case "category":
          return summary.categoryValues.length > 1 ? summary.categoryValues : summary.categoryValues[0] ?? null;
      }
    }
    function matchesTimeBookingFilters(booking) {
      var _a, _b, _c, _d, _e;
      const filters = activeBookingFilters.value;
      const config = bookingFilterConfig.value;
      return matchesAllowedFilterValues((_a = config.service) == null ? void 0 : _a.allowedValues, getBookingFilterValue("service", booking)) && matchesAllowedFilterValues((_b = config.status) == null ? void 0 : _b.allowedValues, getBookingFilterValue("status", booking)) && matchesAllowedFilterValues((_c = config.employee) == null ? void 0 : _c.allowedValues, getBookingFilterValue("employee", booking)) && matchesAllowedFilterValues((_d = config.location) == null ? void 0 : _d.allowedValues, getBookingFilterValue("location", booking)) && matchesAllowedFilterValues((_e = config.category) == null ? void 0 : _e.allowedValues, getBookingFilterValue("category", booking)) && matchesFilterValue(filters.service, getBookingFilterValue("service", booking)) && matchesFilterValue(filters.status, getBookingFilterValue("status", booking)) && matchesFilterValue(filters.employee, getBookingFilterValue("employee", booking)) && matchesFilterValue(filters.location, getBookingFilterValue("location", booking)) && matchesFilterValue(filters.category, getBookingFilterValue("category", booking));
    }
    function matchesAllDayBookingFilters(booking) {
      var _a, _b, _c, _d, _e;
      const filters = activeBookingFilters.value;
      const config = bookingFilterConfig.value;
      return matchesAllowedFilterValues((_a = config.service) == null ? void 0 : _a.allowedValues, getBookingFilterValue("service", booking)) && matchesAllowedFilterValues((_b = config.status) == null ? void 0 : _b.allowedValues, getBookingFilterValue("status", booking)) && matchesAllowedFilterValues((_c = config.employee) == null ? void 0 : _c.allowedValues, getBookingFilterValue("employee", booking)) && matchesAllowedFilterValues((_d = config.location) == null ? void 0 : _d.allowedValues, getBookingFilterValue("location", booking)) && matchesAllowedFilterValues((_e = config.category) == null ? void 0 : _e.allowedValues, getBookingFilterValue("category", booking)) && matchesFilterValue(filters.service, getBookingFilterValue("service", booking)) && matchesFilterValue(filters.status, getBookingFilterValue("status", booking)) && matchesFilterValue(filters.employee, getBookingFilterValue("employee", booking)) && matchesFilterValue(filters.location, getBookingFilterValue("location", booking)) && matchesFilterValue(filters.category, getBookingFilterValue("category", booking));
    }
    onMounted(() => {
      var _a, _b;
      syncResponsiveMode();
      if (!isMobileViewport.value) {
        ensureWideViewInitialized();
        if (calendar2.currentView.value === "month" && lastDesktopView.value !== "month") {
          calendar2.setView(lastDesktopView.value);
        } else {
          lastDesktopView.value = calendar2.currentView.value;
          hasInitializedWideView.value = true;
        }
      }
      if (registerDataCallback) {
        registerDataCallback((mutation) => {
          if (!mutation) {
            return;
          }
          queueDataMutation(mutation);
        });
      }
      nextTick(() => {
        updateAvailableViewportHeight();
        measureColumnWidth();
        updateWeekStickyHeaderHeight();
        updateAllDayLayoutMetrics();
      });
      window.addEventListener("resize", onWindowResize);
      window.addEventListener(CLOSE_POPOVER_EVENT_NAME, onClosePopoverEvent);
      window.addEventListener("scroll", updateAvailableViewportHeight, true);
      (_a = window.visualViewport) == null ? void 0 : _a.addEventListener("resize", updateAvailableViewportHeight);
      (_b = window.visualViewport) == null ? void 0 : _b.addEventListener("scroll", updateAvailableViewportHeight);
    });
    onUnmounted(() => {
      var _a, _b;
      window.removeEventListener("resize", onWindowResize);
      window.removeEventListener(CLOSE_POPOVER_EVENT_NAME, onClosePopoverEvent);
      window.removeEventListener("scroll", updateAvailableViewportHeight, true);
      (_a = window.visualViewport) == null ? void 0 : _a.removeEventListener("resize", updateAvailableViewportHeight);
      (_b = window.visualViewport) == null ? void 0 : _b.removeEventListener("scroll", updateAvailableViewportHeight);
    });
    watch(
      () => isMobileViewport.value,
      (isMobile, wasMobile) => {
        if (isMobile) {
          if (calendar2.currentView.value !== "day") {
            ensureWideViewInitialized();
            lastDesktopView.value = calendar2.currentView.value;
            calendar2.setView("day");
          }
          clearExpandedDays();
          return;
        }
        if (wasMobile && calendar2.currentView.value === "day") {
          ensureWideViewInitialized();
          calendar2.setView(lastDesktopView.value);
        }
      }
    );
    watch(
      () => calendar2.currentView.value,
      (view) => {
        if (isMobileViewport.value && view !== "day") {
          calendar2.setView("day");
          return;
        }
        if (!isMobileViewport.value) {
          hasInitializedWideView.value = true;
          lastDesktopView.value = view;
        }
      }
    );
    watch(
      () => calendar2.currentDate.value,
      () => {
        bookings.invalidateLayoutCache();
        clearExpandedDays();
      }
    );
    watch(
      currentMonthKey,
      (nextMonthKey, previousMonthKey) => {
        const monthRange = getCurrentMonthRange(calendar2.currentDate.value);
        dispatchCalendarMonthRangeChangeEvent({
          currentDate: new Date(calendar2.currentDate.value),
          currentView: calendar2.currentView.value,
          monthStart: monthRange.monthStart,
          monthEnd: monthRange.monthEnd,
          monthStartDate: formatDateForRawEntry(monthRange.monthStart),
          monthEndDate: formatDateForRawEntry(monthRange.monthEnd),
          monthKey: nextMonthKey,
          reason: previousMonthKey ? "navigation" : "initial"
        });
      },
      { immediate: true }
    );
    watch(
      [
        filteredTimeBookings,
        visibleDays,
        () => calendar2.currentView.value,
        () => activeGridConfig.value.columnMinWidth
      ],
      () => {
        collapseExpandedDaysThatFitCollapsedWidth();
        nextTick(() => {
          updateAvailableViewportHeight();
          measureColumnWidth();
          updateWeekStickyHeaderHeight();
          updateAllDayLayoutMetrics();
        });
      }
    );
    watch(
      () => activePopoverBooking.value,
      (booking) => {
        if (showPopover.value && activePopoverBookingId.value && !booking) {
          closePopover();
        }
      }
    );
    return (_ctx, _cache) => {
      return openBlock(), createElementBlock("div", {
        ref_key: "calendarRootRef",
        ref: calendarRootRef,
        class: normalizeClass(["bpa-calendar-root", { "is-embedded": isEmbeddedHost.value, "is-mobile-landscape": isMobileLandscapeViewport.value }]),
        style: normalizeStyle(calendarRootStyle.value)
      }, [
        createVNode(BpaCalendarHeader, {
          title: unref(calendar2).headerTitle.value,
          "current-view": unref(calendar2).currentView.value,
          "is-mobile": isMobileViewport.value,
          "display-settings": displaySettings.value,
          "booking-filters": activeBookingFilters.value,
          "booking-filter-config": bookingFilterConfig.value,
          "show-add-appointment-button": showAddAppointmentButton.value,
          onToday: _cache[0] || (_cache[0] = ($event) => unref(calendar2).goToToday()),
          onPrev: _cache[1] || (_cache[1] = ($event) => unref(calendar2).goToPrevious()),
          onNext: _cache[2] || (_cache[2] = ($event) => unref(calendar2).goToNext()),
          onAddNew: bpaOpenAddNewAppointmentForm,
          onMobileMenu: openCalendarSidebar,
          onViewChange: handleHeaderViewChange,
          "onUpdate:displaySettings": updateDisplaySettings,
          "onUpdate:bookingFilters": updateBookingFilters
        }, null, 8, ["title", "current-view", "is-mobile", "display-settings", "booking-filters", "booking-filter-config", "show-add-appointment-button"]),
        unref(calendar2).currentView.value === "day" ? (openBlock(), createElementBlock(Fragment, { key: 0 }, [
          activeGridConfig.value.showAllDaySection !== false ? (openBlock(), createElementBlock("div", _hoisted_1$1, [
            createVNode(BpaTimelineAllDayBelt, {
              bookings: timelineVisibleAllDayBookings.value,
              title: weekAllDaySectionTitle.value,
              onCardClick
            }, null, 8, ["bookings", "title"])
          ])) : createCommentVNode("", true),
          createElementVNode("div", {
            ref_key: "scrollContainer",
            ref: scrollContainer,
            class: "bpa-grid-scroll-container"
          }, [
            createElementVNode("div", _hoisted_2, [
              _cache[3] || (_cache[3] = createElementVNode("div", { class: "bpa-gutter-spacer" }, null, -1)),
              createElementVNode("div", _hoisted_3, [
                (openBlock(true), createElementBlock(Fragment, null, renderList(columnInfos.value, (info) => {
                  return openBlock(), createElementBlock("div", {
                    key: "day-hdr-" + info.col.date.toISOString(),
                    class: "bpa-header-cell bpa-header-cell-day",
                    style: normalizeStyle(info.flexStyle)
                  }, [
                    createVNode(BpaDayHeader, {
                      column: info.col,
                      expanded: false,
                      "has-overflow": false,
                      "show-expand-toggle": false
                    }, null, 8, ["column"])
                  ], 4);
                }), 128))
              ])
            ]),
            createElementVNode("div", {
              class: "bpa-grid-body bpa-grid-body-day",
              style: normalizeStyle({ height: `${gridHeight.value}px` })
            }, [
              createVNode(BpaTimeGutter, { "grid-config": activeGridConfig.value }, null, 8, ["grid-config"]),
              createElementVNode("div", {
                ref_key: "gridArea",
                ref: gridArea,
                class: "bpa-columns-area"
              }, [
                (openBlock(true), createElementBlock(Fragment, null, renderList(columnInfos.value, (info) => {
                  var _a, _b;
                  return openBlock(), createBlock(BpaTimeColumn, {
                    key: "day-col-" + info.col.date.toISOString(),
                    "day-index": info.idx,
                    "positioned-bookings": info.layout.positioned,
                    "grid-config": activeGridConfig.value,
                    "is-today": info.col.isToday,
                    "drag-booking-id": unref(dragResize).isDragging.value ? ((_a = unref(dragResize).dragState.value) == null ? void 0 : _a.bookingId) ?? null : null,
                    "drag-interaction-type": ((_b = unref(dragResize).dragState.value) == null ? void 0 : _b.type) ?? null,
                    "preview-day-index": unref(dragResize).previewDayIndex.value,
                    "preview-layout": previewLayout.value,
                    "original-day-index": unref(dragResize).originalDayIndex.value,
                    "original-preview-layout": dragPreviewPositioned.value,
                    "drag-transform": unref(dragResize).getDragTransform(),
                    "preview-in-overlay": !!dragPreviewFixedStyle.value,
                    expanded: true,
                    "required-width": info.layout.requiredWidth,
                    "capped-width": info.cappedWidth,
                    "flex-style": { flex: "1 1 100%", minWidth: "100%" },
                    "display-settings": displaySettings.value,
                    "drag-enabled": isCardDragDropEnabled.value,
                    "resize-enabled": isCardResizeEnabled.value,
                    onDragStart,
                    onResizeTopStart,
                    onResizeBottomStart,
                    onCardClick
                  }, null, 8, ["day-index", "positioned-bookings", "grid-config", "is-today", "drag-booking-id", "drag-interaction-type", "preview-day-index", "preview-layout", "original-day-index", "original-preview-layout", "drag-transform", "preview-in-overlay", "required-width", "capped-width", "display-settings", "drag-enabled", "resize-enabled"]);
                }), 128))
              ], 512)
            ], 4),
            dragPreviewFixedStyle.value && overlayPreviewPositioned.value ? (openBlock(), createElementBlock("div", {
              key: 0,
              class: "bpa-drag-preview-overlay",
              style: normalizeStyle(dragPreviewFixedStyle.value)
            }, [
              createVNode(BpaBookingCard, {
                positioned: overlayPreviewPositioned.value,
                "is-dragging": true,
                "is-preview": true,
                "display-settings": displaySettings.value,
                "resize-enabled": isCardResizeEnabled.value
              }, null, 8, ["positioned", "display-settings", "resize-enabled"])
            ], 4)) : createCommentVNode("", true)
          ], 512)
        ], 64)) : unref(calendar2).currentView.value === "month" ? (openBlock(), createBlock(BpaMonthView, {
          key: 1,
          "time-bookings": filteredTimeBookings.value,
          "all-day-bookings": filteredAllDayBookings.value,
          "month-days": unref(calendar2).monthDays.value,
          "display-settings": displaySettings.value,
          "drag-enabled": isCardDragDropEnabled.value,
          onCardClick,
          onDayDblclick: openDayView,
          onMoreClick: openDayView,
          onDragBegin: onMonthDragBegin,
          onDragEnd: onMonthDragEnd
        }, null, 8, ["time-bookings", "all-day-bookings", "month-days", "display-settings", "drag-enabled"])) : unref(calendar2).currentView.value === "week" ? (openBlock(), createElementBlock("div", {
          key: 2,
          ref_key: "scrollContainer",
          ref: scrollContainer,
          class: "bpa-grid-scroll-container"
        }, [
          createElementVNode("div", {
            ref_key: "weekStickyHeadersRef",
            ref: weekStickyHeadersRef,
            class: "bpa-sticky-headers"
          }, [
            _cache[4] || (_cache[4] = createElementVNode("div", { class: "bpa-gutter-spacer" }, null, -1)),
            createElementVNode("div", _hoisted_4, [
              (openBlock(true), createElementBlock(Fragment, null, renderList(columnInfos.value, (info) => {
                return openBlock(), createElementBlock("div", {
                  key: "hdr-" + info.col.date.toISOString(),
                  class: "bpa-header-cell",
                  style: normalizeStyle(info.flexStyle)
                }, [
                  createVNode(BpaDayHeader, {
                    column: info.col,
                    expanded: info.isExpanded,
                    "has-overflow": info.hasOverflow,
                    onToggleExpand: ($event) => toggleDayExpand(info.idx)
                  }, null, 8, ["column", "expanded", "has-overflow", "onToggleExpand"])
                ], 4);
              }), 128))
            ])
          ], 512),
          !isMobileViewport.value && activeGridConfig.value.showAllDaySection !== false ? (openBlock(), createElementBlock("div", {
            key: 0,
            class: "bpa-week-all-day-sticky",
            style: normalizeStyle({ top: `${weekStickyHeaderHeight.value}px` })
          }, [
            createVNode(BpaWeekAllDayBelt, {
              rows: allDayLayout.value,
              "column-count": 7,
              "column-widths": allDayColumnWidths.value,
              "gutter-width": allDayGutterWidth.value,
              title: weekAllDaySectionTitle.value,
              "display-settings": displaySettings.value,
              onCardClick
            }, null, 8, ["rows", "column-widths", "gutter-width", "title", "display-settings"])
          ], 4)) : createCommentVNode("", true),
          createElementVNode("div", {
            class: "bpa-grid-body",
            style: normalizeStyle({ height: `${gridHeight.value}px` })
          }, [
            createVNode(BpaTimeGutter, { "grid-config": activeGridConfig.value }, null, 8, ["grid-config"]),
            createElementVNode("div", {
              ref_key: "gridArea",
              ref: gridArea,
              class: "bpa-columns-area"
            }, [
              (openBlock(true), createElementBlock(Fragment, null, renderList(columnInfos.value, (info) => {
                var _a, _b;
                return openBlock(), createBlock(BpaTimeColumn, {
                  key: info.col.date.toISOString(),
                  "day-index": info.idx,
                  "positioned-bookings": info.layout.positioned,
                  "grid-config": activeGridConfig.value,
                  "is-today": info.col.isToday,
                  "drag-booking-id": unref(dragResize).isDragging.value ? ((_a = unref(dragResize).dragState.value) == null ? void 0 : _a.bookingId) ?? null : null,
                  "drag-interaction-type": ((_b = unref(dragResize).dragState.value) == null ? void 0 : _b.type) ?? null,
                  "preview-day-index": unref(dragResize).previewDayIndex.value,
                  "preview-layout": previewLayout.value,
                  "original-day-index": unref(dragResize).originalDayIndex.value,
                  "original-preview-layout": dragPreviewPositioned.value,
                  "drag-transform": unref(dragResize).getDragTransform(),
                  "preview-in-overlay": !!dragPreviewFixedStyle.value,
                  expanded: info.isExpanded,
                  "required-width": info.layout.requiredWidth,
                  "capped-width": info.cappedWidth,
                  "flex-style": info.flexStyle,
                  "display-settings": displaySettings.value,
                  "drag-enabled": isCardDragDropEnabled.value,
                  "resize-enabled": isCardResizeEnabled.value,
                  onDragStart,
                  onResizeTopStart,
                  onResizeBottomStart,
                  onCardClick
                }, null, 8, ["day-index", "positioned-bookings", "grid-config", "is-today", "drag-booking-id", "drag-interaction-type", "preview-day-index", "preview-layout", "original-day-index", "original-preview-layout", "drag-transform", "preview-in-overlay", "expanded", "required-width", "capped-width", "flex-style", "display-settings", "drag-enabled", "resize-enabled"]);
              }), 128))
            ], 512)
          ], 4),
          dragPreviewFixedStyle.value && overlayPreviewPositioned.value ? (openBlock(), createElementBlock("div", {
            key: 1,
            class: "bpa-drag-preview-overlay",
            style: normalizeStyle(dragPreviewFixedStyle.value)
          }, [
            createVNode(BpaBookingCard, {
              positioned: overlayPreviewPositioned.value,
              "is-dragging": true,
              "is-preview": true,
              "display-settings": displaySettings.value,
              "resize-enabled": isCardResizeEnabled.value
            }, null, 8, ["positioned", "display-settings", "resize-enabled"])
          ], 4)) : createCommentVNode("", true)
        ], 512)) : unref(calendar2).currentView.value === "timeline" ? (openBlock(), createElementBlock("div", _hoisted_5, [
          !isMobileViewport.value && activeGridConfig.value.showAllDaySection !== false ? (openBlock(), createElementBlock("div", _hoisted_6, [
            createVNode(BpaTimelineAllDayBelt, {
              bookings: timelineVisibleAllDayBookings.value,
              title: weekAllDaySectionTitle.value,
              onCardClick
            }, null, 8, ["bookings", "title"])
          ])) : createCommentVNode("", true),
          createVNode(BpaTimelineView, {
            bookings: filteredTimeBookings.value,
            "week-days": unref(calendar2).weekDays.value,
            "grid-config": activeGridConfig.value,
            "display-settings": displaySettings.value,
            "drag-enabled": isCardDragDropEnabled.value,
            "resize-enabled": isCardResizeEnabled.value,
            onDragBegin: onTimelineDragBegin,
            onDragEnd: onTimelineDragEnd,
            onResizeEnd: onTimelineResizeEnd,
            onCardClick
          }, null, 8, ["bookings", "week-days", "grid-config", "display-settings", "drag-enabled", "resize-enabled"])
        ])) : createCommentVNode("", true),
        showPopover.value && activePopoverBooking.value && popoverAnchorEl.value ? (openBlock(), createBlock(BpaEventPopover, {
          key: 4,
          booking: activePopoverBooking.value,
          "anchor-el": popoverAnchorEl.value,
          config: popoverConfig.value,
          "status-options": popoverStatusOptions.value,
          mobile: isMobileViewport.value,
          onClose: closePopover
        }, null, 8, ["booking", "anchor-el", "config", "status-options", "mobile"])) : createCommentVNode("", true)
      ], 6);
    };
  }
});
const BpaCalendarView = /* @__PURE__ */ _export_sfc(_sfc_main$2, [["__scopeId", "data-v-8543eeb6"]]);
const _sfc_main$1 = /* @__PURE__ */ defineComponent({
  __name: "BpaCalendarApp",
  props: {
    config: {},
    data: {},
    embedded: { type: Boolean }
  },
  setup(__props, { expose: __expose }) {
    const props = __props;
    const parentConfig = inject("bpaInitialConfig", {});
    const parentRegisterDataCallback = inject(
      "bpaRegisterDataCallback",
      void 0
    );
    const calendarViewKey = ref(0);
    const configState = reactive({});
    const dataMutationCallback = ref(null);
    const pendingDataMutations = ref([]);
    const resolvedConfig = computed(() => stripConfigData(
      props.config ?? parentConfig
    ));
    provide("bpaInitialConfig", configState);
    provide("bpaCalendarHostMode", props.embedded ? "embedded" : "viewport");
    provide("bpaRegisterDataCallback", (cb) => {
      dataMutationCallback.value = cb;
      if (parentRegisterDataCallback) {
        parentRegisterDataCallback(cb);
      }
      if (pendingDataMutations.value.length > 0) {
        pendingDataMutations.value.forEach((mutation) => cb(mutation));
        pendingDataMutations.value = [];
      }
    });
    function stripConfigData(config) {
      if (!config) {
        return {};
      }
      const nextConfig = { ...config };
      delete nextConfig.data;
      return nextConfig;
    }
    function syncConfigState(nextConfig) {
      Object.keys(configState).forEach((key) => {
        delete configState[key];
      });
      Object.assign(configState, nextConfig);
    }
    function queueDataMutation(mutation) {
      if (dataMutationCallback.value) {
        dataMutationCallback.value(mutation);
        return;
      }
      pendingDataMutations.value.push(mutation);
    }
    function loadData(data) {
      queueDataMutation({
        mode: "replace",
        data
      });
    }
    function appendData(data) {
      queueDataMutation({
        mode: "append",
        data
      });
    }
    function updateBooking(bookingOrId, updates) {
      const isBookingObject = typeof bookingOrId === "object" && bookingOrId !== null;
      const rawBookingId = isBookingObject ? bookingOrId.id : bookingOrId;
      const bookingId = rawBookingId === void 0 || rawBookingId === null ? "" : String(rawBookingId).trim();
      if (!bookingId) {
        throw new Error("updateBooking requires a booking id.");
      }
      queueDataMutation({
        mode: "update-booking",
        bookingId,
        updates: isBookingObject && typeof updates === "undefined" ? bookingOrId : updates ?? {}
      });
    }
    watch(
      () => resolvedConfig.value,
      (nextConfig) => {
        syncConfigState(nextConfig);
        setCalendarTimeFormat(nextConfig.timeFormat ?? nextConfig.time_format);
        calendarViewKey.value += 1;
      },
      { deep: true, immediate: true }
    );
    watch(
      () => {
        var _a;
        return typeof props.data !== "undefined" ? props.data : (_a = props.config) == null ? void 0 : _a.data;
      },
      (nextData) => {
        if (typeof nextData === "undefined") {
          return;
        }
        loadData(nextData);
      },
      { deep: true, immediate: true }
    );
    __expose({
      loadData,
      appendData,
      updateBooking
    });
    return (_ctx, _cache) => {
      return openBlock(), createBlock(BpaCalendarView, { key: calendarViewKey.value });
    };
  }
});
const _hoisted_1 = ["aria-label"];
const STANDALONE_SIDEBAR_BREAKPOINT = 768;
const STANDALONE_SIDEBAR_OPEN_EVENT_NAME = "bookingpress:calendar-sidebar-open";
const _sfc_main = /* @__PURE__ */ defineComponent({
  __name: "App",
  setup(__props) {
    const { locale } = useI18n();
    const injectedConfig = inject("bpaInitialConfig", {});
    const uiText = useCalendarText();
    const adminBarHeight = ref(0);
    const viewportHeight = ref(0);
    const standaloneSidebarOpen = ref(false);
    const customSidebarTemplate = ref("");
    const customSidebarComponent = shallowRef(null);
    const appShellConfig = computed(() => injectedConfig.appShell && typeof injectedConfig.appShell === "object" ? injectedConfig.appShell : {});
    const showStandaloneSidebarPreview = computed(() => Boolean(appShellConfig.value.enabled));
    const standaloneSidebarWidth = computed(() => getNonEmptyString(appShellConfig.value.sidebarWidth, "220px"));
    const standaloneSidebarSide = computed(() => appShellConfig.value.sidebarSide === "left" ? "left" : "right");
    const standaloneSidebarOffsetTop = computed(() => {
      const baseOffset = getNonEmptyString(appShellConfig.value.mobileDrawerOffsetTop, "76px");
      return adminBarHeight.value > 0 ? `calc(${adminBarHeight.value}px + ${baseOffset})` : baseOffset;
    });
    const standaloneSidebarAriaLabel = computed(() => getNonEmptyString(appShellConfig.value.sidebarAriaLabel, uiText.value.sidebar.ariaLabel));
    const hasCustomSidebarTemplate = computed(() => customSidebarTemplate.value.trim().length > 0 && customSidebarComponent.value !== null);
    const sidebarLinks = computed(() => [
      { label: uiText.value.sidebar.calendar, active: false, premium: false },
      { label: uiText.value.sidebar.appointments, active: true, premium: false },
      { label: uiText.value.sidebar.payments, active: false, premium: false },
      { label: uiText.value.sidebar.customers, active: false, premium: false },
      { label: uiText.value.sidebar.services, active: false, premium: false },
      { label: uiText.value.sidebar.notifications, active: false, premium: false },
      { label: uiText.value.sidebar.customize, active: false, premium: false },
      { label: uiText.value.sidebar.settings, active: false, premium: false },
      { label: uiText.value.sidebar.goPremium, active: false, premium: true }
    ]);
    const appLayoutStyle = computed(() => ({
      "--bpa-admin-bar-height": `${adminBarHeight.value}px`,
      "--bpa-viewport-height": `${viewportHeight.value || 0}px`
    }));
    function getNonEmptyString(value, fallback) {
      return typeof value === "string" && value.trim() ? value.trim() : fallback;
    }
    function setCustomSidebarTemplate(template) {
      const normalizedTemplate = template.trim();
      customSidebarTemplate.value = normalizedTemplate;
      if (!normalizedTemplate) {
        customSidebarComponent.value = null;
        return;
      }
      try {
        customSidebarComponent.value = markRaw(defineComponent({
          name: "BpaStandaloneSidebarTemplate",
          render: compile$1(`<div class="bpa-standalone-sidebar__custom">${normalizedTemplate}</div>`)
        }));
      } catch (error) {
        customSidebarComponent.value = null;
        console.error("Failed to compile BookingPress sidebar template.", error);
      }
    }
    function resolveCustomSidebarMarkup() {
      if (typeof document === "undefined") {
        setCustomSidebarTemplate("");
        return;
      }
      const sidebarHtml = typeof appShellConfig.value.sidebarHtml === "string" ? appShellConfig.value.sidebarHtml.trim() : "";
      if (sidebarHtml) {
        setCustomSidebarTemplate(sidebarHtml);
        return;
      }
      const sidebarTemplateSelector = typeof appShellConfig.value.sidebarTemplateSelector === "string" ? appShellConfig.value.sidebarTemplateSelector.trim() : "";
      if (!sidebarTemplateSelector) {
        setCustomSidebarTemplate("");
        return;
      }
      const templateSource = document.querySelector(sidebarTemplateSelector);
      if (templateSource instanceof HTMLTemplateElement) {
        setCustomSidebarTemplate(templateSource.innerHTML);
        return;
      }
      if (templateSource instanceof HTMLElement) {
        setCustomSidebarTemplate(templateSource.innerHTML);
        return;
      }
      setCustomSidebarTemplate("");
    }
    function updateAdminBarHeight() {
      if (typeof document === "undefined") {
        return;
      }
      const adminBar = document.getElementById("wpadminbar");
      adminBarHeight.value = adminBar instanceof HTMLElement ? adminBar.offsetHeight : 0;
    }
    function updateViewportHeight() {
      var _a;
      if (typeof window === "undefined") {
        return;
      }
      viewportHeight.value = Math.round(((_a = window.visualViewport) == null ? void 0 : _a.height) ?? window.innerHeight);
    }
    function syncStandaloneSidebarState() {
      if (typeof window === "undefined") {
        return;
      }
      if (window.innerWidth > STANDALONE_SIDEBAR_BREAKPOINT && standaloneSidebarOpen.value) {
        standaloneSidebarOpen.value = false;
      }
    }
    function handleStandaloneSidebarEvent(event) {
      var _a;
      if (!showStandaloneSidebarPreview.value || typeof window === "undefined") {
        return;
      }
      if (window.innerWidth > STANDALONE_SIDEBAR_BREAKPOINT) {
        return;
      }
      const requestedState = (_a = event.detail) == null ? void 0 : _a.requestedState;
      standaloneSidebarOpen.value = typeof requestedState === "boolean" ? requestedState : !standaloneSidebarOpen.value;
    }
    watchEffect(() => {
      const rtlLanguages = ["ar", "ur"];
      document.documentElement.dir = rtlLanguages.includes(locale.value) ? "rtl" : "ltr";
    });
    onMounted(() => {
      var _a, _b;
      updateAdminBarHeight();
      updateViewportHeight();
      resolveCustomSidebarMarkup();
      window.addEventListener("resize", updateAdminBarHeight);
      window.addEventListener("resize", updateViewportHeight);
      window.addEventListener("resize", syncStandaloneSidebarState);
      window.addEventListener(STANDALONE_SIDEBAR_OPEN_EVENT_NAME, handleStandaloneSidebarEvent);
      (_a = window.visualViewport) == null ? void 0 : _a.addEventListener("resize", updateViewportHeight);
      (_b = window.visualViewport) == null ? void 0 : _b.addEventListener("scroll", updateViewportHeight);
    });
    onUnmounted(() => {
      var _a, _b;
      window.removeEventListener("resize", updateAdminBarHeight);
      window.removeEventListener("resize", updateViewportHeight);
      window.removeEventListener("resize", syncStandaloneSidebarState);
      window.removeEventListener(STANDALONE_SIDEBAR_OPEN_EVENT_NAME, handleStandaloneSidebarEvent);
      (_a = window.visualViewport) == null ? void 0 : _a.removeEventListener("resize", updateViewportHeight);
      (_b = window.visualViewport) == null ? void 0 : _b.removeEventListener("scroll", updateViewportHeight);
    });
    return (_ctx, _cache) => {
      const _component_BpUiWrapper = resolveComponent("BpUiWrapper");
      return openBlock(), createBlock(_component_BpUiWrapper, {
        modelValue: standaloneSidebarOpen.value,
        "onUpdate:modelValue": _cache[0] || (_cache[0] = ($event) => standaloneSidebarOpen.value = $event),
        locale: unref(locale),
        class: "bpa-app-layout",
        style: normalizeStyle(appLayoutStyle.value),
        "sidebar-close-label": unref(uiText).shell.closeSidebar,
        "mobile-only-sidebar": "",
        "sidebar-width": standaloneSidebarWidth.value,
        "sidebar-side": standaloneSidebarSide.value,
        "mobile-drawer-offset-top": standaloneSidebarOffsetTop.value,
        "listen-sidebar-open-event": !showStandaloneSidebarPreview.value
      }, createSlots({
        default: withCtx(() => [
          createVNode(_sfc_main$1, { embedded: showStandaloneSidebarPreview.value }, null, 8, ["embedded"])
        ]),
        _: 2
      }, [
        showStandaloneSidebarPreview.value ? {
          name: "sidebar",
          fn: withCtx(() => [
            createElementVNode("aside", {
              class: normalizeClass(["bpa-standalone-sidebar", { "bpa-standalone-sidebar--custom": hasCustomSidebarTemplate.value }])
            }, [
              hasCustomSidebarTemplate.value && customSidebarComponent.value ? (openBlock(), createBlock(resolveDynamicComponent(customSidebarComponent.value), { key: 0 })) : (openBlock(), createElementBlock("nav", {
                key: 1,
                class: "bpa-standalone-sidebar__nav",
                "aria-label": standaloneSidebarAriaLabel.value
              }, [
                (openBlock(true), createElementBlock(Fragment, null, renderList(sidebarLinks.value, (link) => {
                  return openBlock(), createElementBlock("button", {
                    key: link.label,
                    type: "button",
                    class: normalizeClass(["bpa-standalone-sidebar__item", {
                      "is-active": link.active,
                      "is-premium": link.premium
                    }])
                  }, [
                    _cache[1] || (_cache[1] = createElementVNode("span", {
                      class: "bpa-standalone-sidebar__icon",
                      "aria-hidden": "true"
                    }, null, -1)),
                    createElementVNode("span", null, toDisplayString$1(link.label), 1)
                  ], 2);
                }), 128))
              ], 8, _hoisted_1))
            ], 2)
          ]),
          key: "0"
        } : void 0
      ]), 1032, ["modelValue", "locale", "style", "sidebar-close-label", "sidebar-width", "sidebar-side", "mobile-drawer-offset-top", "listen-sidebar-open-event"]);
    };
  }
});
async function installDevBookingPressUI(_app, _options = {}) {
}
function getStylesheetUrlFromScriptUrl(scriptUrl) {
  const url = new URL(scriptUrl, import.meta.url);
  const pathSegments = url.pathname.split("/");
  if (pathSegments.length > 1) {
    const directoryIndex = pathSegments.length - 2;
    const directoryName = pathSegments[directoryIndex];
    if (/^js$/i.test(directoryName)) {
      pathSegments[directoryIndex] = directoryName === "JS" ? "CSS" : "css";
    }
  }
  pathSegments[pathSegments.length - 1] = pathSegments[pathSegments.length - 1].replace(/\.js$/i, ".css");
  url.pathname = pathSegments.join("/");
  return url.href;
}
function getStylesheetKey(href) {
  var _a;
  return ((_a = new URL(href, document.baseURI).pathname.split("/").pop()) == null ? void 0 : _a.toLowerCase()) ?? "";
}
function ensureStylesheet(url) {
  if (typeof document === "undefined") {
    return;
  }
  const normalizedHref = new URL(url, document.baseURI).href;
  const targetKey = getStylesheetKey(normalizedHref);
  const existingStylesheets = Array.from(
    document.querySelectorAll('link[rel="stylesheet"]')
  );
  if (existingStylesheets.some((link) => {
    const existingHref = new URL(link.href, document.baseURI).href;
    return existingHref === normalizedHref || getStylesheetKey(existingHref) === targetKey;
  })) {
    return;
  }
  const stylesheet = document.createElement("link");
  stylesheet.rel = "stylesheet";
  stylesheet.href = normalizedHref;
  (document.head ?? document.documentElement).appendChild(stylesheet);
}
class BpaCalendar {
  constructor(config = {}) {
    __publicField(this, "app", null);
    __publicField(this, "config");
    __publicField(this, "dataMutationCallback", null);
    __publicField(this, "pendingDataMutations", []);
    this.config = config;
    if ("data" in config && typeof config.data !== "undefined") {
      this.pendingDataMutations.push({
        mode: "replace",
        data: config.data
      });
    }
  }
  mount(elementOrSelector) {
    setCalendarTimeFormat(this.config.timeFormat ?? this.config.time_format);
    this.app = createApp(_sfc_main);
    if (this.config.locale) {
      i18n.global.locale.value = this.config.locale;
    }
    this.app.provide("bpaRegisterDataCallback", (cb) => {
      this.dataMutationCallback = cb;
      if (this.pendingDataMutations.length > 0) {
        this.pendingDataMutations.forEach((mutation) => cb(mutation));
        this.pendingDataMutations = [];
      }
    });
    this.app.provide("bpaInitialConfig", this.config);
    this.app.use(i18n);
    this.ensureBundledStyles();
    const bookingPressUI = window.BookingPressUI;
    if (bookingPressUI == null ? void 0 : bookingPressUI.install) {
      void this.mountWithInstalledUI(elementOrSelector, bookingPressUI);
      return this.app;
    }
    void this.mountWithBundledUI(elementOrSelector);
    return this.app;
  }
  ensureBundledStyles() {
    ensureStylesheet(getStylesheetUrlFromScriptUrl(import.meta.url));
    const bundledUiUrl = new URL(
      /* @vite-ignore */
      "bookingpress-ui.js",
      import.meta.url
    ).href;
    ensureStylesheet(getStylesheetUrlFromScriptUrl(bundledUiUrl));
  }
  async mountWithLocalUI(elementOrSelector) {
    if (!this.app) {
      return;
    }
    await installDevBookingPressUI(this.app, {
      locale: this.config.locale
    });
    this.app.mount(elementOrSelector);
  }
  async mountWithInstalledUI(elementOrSelector, bookingPressUI) {
    if (!this.app) {
      return;
    }
    if (this.config.locale && typeof bookingPressUI.setLocale === "function") {
      await bookingPressUI.setLocale(this.config.locale);
    }
    bookingPressUI.install(this.app, {
      locale: this.config.locale
    });
    this.app.mount(elementOrSelector);
  }
  async mountWithBundledUI(elementOrSelector) {
    if (!this.app) {
      return;
    }
    const bundledUiUrl = new URL(
      /* @vite-ignore */
      "bookingpress-ui.js",
      import.meta.url
    ).href;
    const uiModule = await import(
      /* @vite-ignore */
      bundledUiUrl
    );
    const bookingPressUI = window.BookingPressUI ?? (uiModule == null ? void 0 : uiModule.BookingPressUI) ?? (uiModule == null ? void 0 : uiModule.default);
    if (!(bookingPressUI == null ? void 0 : bookingPressUI.install)) {
      throw new Error("BookingPressUI bundle is required before mounting BpaCalendar.");
    }
    await this.mountWithInstalledUI(elementOrSelector, bookingPressUI);
  }
  queueDataMutation(mutation) {
    if (this.dataMutationCallback) {
      this.dataMutationCallback(mutation);
      return;
    }
    this.pendingDataMutations.push(mutation);
  }
  loadData(data) {
    this.queueDataMutation({
      mode: "replace",
      data
    });
  }
  appendData(data) {
    this.queueDataMutation({
      mode: "append",
      data
    });
  }
  updateBooking(bookingOrId, updates) {
    const isBookingObject = typeof bookingOrId === "object" && bookingOrId !== null;
    const rawBookingId = isBookingObject ? bookingOrId.id : bookingOrId;
    const bookingId = rawBookingId === void 0 || rawBookingId === null ? "" : String(rawBookingId).trim();
    if (!bookingId) {
      throw new Error("updateBooking requires a booking id.");
    }
    this.queueDataMutation({
      mode: "update-booking",
      bookingId,
      updates: isBookingObject && typeof updates === "undefined" ? bookingOrId : updates ?? {}
    });
  }
}
if (typeof window !== "undefined") {
  window.BpaCalendar = BpaCalendar;
  window.BpaCalendarApp = _sfc_main$1;
}
export {
  BpaCalendar,
  _sfc_main$1 as BpaCalendarApp
};
