webpackJsonp([0],[
/* 0 */,
/* 1 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_vux_loader_src_script_loader_js_vux_loader_src_script_loader_js_vue_loader_lib_selector_type_script_index_0_grid_vue__ = __webpack_require__(143);
/* unused harmony namespace reexport */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__babel_loader_vux_loader_src_script_loader_js_vue_loader_lib_selector_type_script_index_0_grid_vue__ = __webpack_require__(144);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__vue_loader_lib_template_compiler_index_id_data_v_21378e8c_hasScoped_false_buble_transforms_vux_loader_src_before_template_compiler_loader_js_vux_loader_src_template_loader_js_vue_loader_lib_selector_type_template_index_0_grid_vue__ = __webpack_require__(145);
function injectStyle (ssrContext) {
  __webpack_require__(142)
}
var normalizeComponent = __webpack_require__(0)
/* script */


/* template */

/* template functional */
var __vue_template_functional__ = false
/* styles */
var __vue_styles__ = injectStyle
/* scopeId */
var __vue_scopeId__ = null
/* moduleIdentifier (server only) */
var __vue_module_identifier__ = null
var Component = normalizeComponent(
  __WEBPACK_IMPORTED_MODULE_1__babel_loader_vux_loader_src_script_loader_js_vue_loader_lib_selector_type_script_index_0_grid_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_2__vue_loader_lib_template_compiler_index_id_data_v_21378e8c_hasScoped_false_buble_transforms_vux_loader_src_before_template_compiler_loader_js_vux_loader_src_template_loader_js_vue_loader_lib_selector_type_template_index_0_grid_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)

/* harmony default export */ __webpack_exports__["a"] = (Component.exports);


/***/ }),
/* 2 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_vux_loader_src_script_loader_js_vux_loader_src_script_loader_js_vue_loader_lib_selector_type_script_index_0_grid_item_vue__ = __webpack_require__(147);
/* unused harmony namespace reexport */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__babel_loader_vux_loader_src_script_loader_js_vue_loader_lib_selector_type_script_index_0_grid_item_vue__ = __webpack_require__(148);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__vue_loader_lib_template_compiler_index_id_data_v_1b090120_hasScoped_false_buble_transforms_vux_loader_src_before_template_compiler_loader_js_vux_loader_src_template_loader_js_vue_loader_lib_selector_type_template_index_0_grid_item_vue__ = __webpack_require__(149);
function injectStyle (ssrContext) {
  __webpack_require__(146)
}
var normalizeComponent = __webpack_require__(0)
/* script */


/* template */

/* template functional */
var __vue_template_functional__ = false
/* styles */
var __vue_styles__ = injectStyle
/* scopeId */
var __vue_scopeId__ = null
/* moduleIdentifier (server only) */
var __vue_module_identifier__ = null
var Component = normalizeComponent(
  __WEBPACK_IMPORTED_MODULE_1__babel_loader_vux_loader_src_script_loader_js_vue_loader_lib_selector_type_script_index_0_grid_item_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_2__vue_loader_lib_template_compiler_index_id_data_v_1b090120_hasScoped_false_buble_transforms_vux_loader_src_before_template_compiler_loader_js_vux_loader_src_template_loader_js_vue_loader_lib_selector_type_template_index_0_grid_item_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)

/* harmony default export */ __webpack_exports__["a"] = (Component.exports);


/***/ }),
/* 3 */,
/* 4 */,
/* 5 */,
/* 6 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";


var objectAssign = __webpack_require__(28);

function getTarget(node) {
  if (node === void 0) {
    return document.body;
  }

  if (typeof node === 'string' && node.indexOf('?') === 0) {
    return document.body;
  } else if (typeof node === 'string' && node.indexOf('?') > 0) {
    node = node.split('?')[0];
  }

  if (node === 'body' || node === true) {
    return document.body;
  }

  return node instanceof window.Node ? node : document.querySelector(node);
}

function getShouldUpdate(node) {
  if (!node) {
    return false;
  }
  if (typeof node === 'string' && node.indexOf('?') > 0) {
    try {
      var config = JSON.parse(node.split('?')[1]);
      return config.autoUpdate || false;
    } catch (e) {
      return false;
    }
  }
  return false;
}

var directive = {
  inserted: function inserted(el, _ref, vnode) {
    var value = _ref.value;

    el.className = el.className ? el.className + ' v-transfer-dom' : 'v-transfer-dom';
    var parentNode = el.parentNode;
    var home = document.createComment('');
    var hasMovedOut = false;

    if (value !== false) {
      parentNode.replaceChild(home, el);
      getTarget(value).appendChild(el);
      hasMovedOut = true;
    }
    if (!el.__transferDomData) {
      el.__transferDomData = {
        parentNode: parentNode,
        home: home,
        target: getTarget(value),
        hasMovedOut: hasMovedOut
      };
    }
  },
  componentUpdated: function componentUpdated(el, _ref2) {
    var value = _ref2.value;

    var shouldUpdate = getShouldUpdate(value);
    if (!shouldUpdate) {
      return;
    }

    var ref$1 = el.__transferDomData;

    var parentNode = ref$1.parentNode;
    var home = ref$1.home;
    var hasMovedOut = ref$1.hasMovedOut;

    if (!hasMovedOut && value) {
      parentNode.replaceChild(home, el);

      getTarget(value).appendChild(el);
      el.__transferDomData = objectAssign({}, el.__transferDomData, { hasMovedOut: true, target: getTarget(value) });
    } else if (hasMovedOut && value === false) {
      parentNode.replaceChild(el, home);
      el.__transferDomData = objectAssign({}, el.__transferDomData, { hasMovedOut: false, target: getTarget(value) });
    } else if (value) {
      getTarget(value).appendChild(el);
    }
  },

  unbind: function unbind(el, binding) {
    el.className = el.className.replace('v-transfer-dom', '');
    if (el.__transferDomData && el.__transferDomData.hasMovedOut === true) {
      el.__transferDomData.parentNode && el.__transferDomData.parentNode.appendChild(el);
    }
    el.__transferDomData = null;
  }
};

/* harmony default export */ __webpack_exports__["a"] = (directive);

/***/ }),
/* 7 */,
/* 8 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_vux_loader_src_script_loader_js_vux_loader_src_script_loader_js_vue_loader_lib_selector_type_script_index_0_swiper_vue__ = __webpack_require__(118);
/* unused harmony namespace reexport */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__babel_loader_vux_loader_src_script_loader_js_vue_loader_lib_selector_type_script_index_0_swiper_vue__ = __webpack_require__(119);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__vue_loader_lib_template_compiler_index_id_data_v_8fe8f518_hasScoped_false_buble_transforms_vux_loader_src_before_template_compiler_loader_js_vux_loader_src_template_loader_js_vue_loader_lib_selector_type_template_index_0_swiper_vue__ = __webpack_require__(120);
function injectStyle (ssrContext) {
  __webpack_require__(117)
}
var normalizeComponent = __webpack_require__(0)
/* script */


/* template */

/* template functional */
var __vue_template_functional__ = false
/* styles */
var __vue_styles__ = injectStyle
/* scopeId */
var __vue_scopeId__ = null
/* moduleIdentifier (server only) */
var __vue_module_identifier__ = null
var Component = normalizeComponent(
  __WEBPACK_IMPORTED_MODULE_1__babel_loader_vux_loader_src_script_loader_js_vue_loader_lib_selector_type_script_index_0_swiper_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_2__vue_loader_lib_template_compiler_index_id_data_v_8fe8f518_hasScoped_false_buble_transforms_vux_loader_src_before_template_compiler_loader_js_vux_loader_src_template_loader_js_vue_loader_lib_selector_type_template_index_0_swiper_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)

/* harmony default export */ __webpack_exports__["a"] = (Component.exports);


/***/ }),
/* 9 */,
/* 10 */,
/* 11 */,
/* 12 */,
/* 13 */,
/* 14 */,
/* 15 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_vux_loader_src_script_loader_js_vux_loader_src_script_loader_js_vue_loader_lib_selector_type_script_index_0_swiper_item_vue__ = __webpack_require__(159);
/* unused harmony namespace reexport */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__babel_loader_vux_loader_src_script_loader_js_vue_loader_lib_selector_type_script_index_0_swiper_item_vue__ = __webpack_require__(160);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__vue_loader_lib_template_compiler_index_id_data_v_4cd6837a_hasScoped_false_buble_transforms_vux_loader_src_before_template_compiler_loader_js_vux_loader_src_template_loader_js_vue_loader_lib_selector_type_template_index_0_swiper_item_vue__ = __webpack_require__(161);
var normalizeComponent = __webpack_require__(0)
/* script */


/* template */

/* template functional */
var __vue_template_functional__ = false
/* styles */
var __vue_styles__ = null
/* scopeId */
var __vue_scopeId__ = null
/* moduleIdentifier (server only) */
var __vue_module_identifier__ = null
var Component = normalizeComponent(
  __WEBPACK_IMPORTED_MODULE_1__babel_loader_vux_loader_src_script_loader_js_vue_loader_lib_selector_type_script_index_0_swiper_item_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_2__vue_loader_lib_template_compiler_index_id_data_v_4cd6837a_hasScoped_false_buble_transforms_vux_loader_src_before_template_compiler_loader_js_vux_loader_src_template_loader_js_vue_loader_lib_selector_type_template_index_0_swiper_item_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)

/* harmony default export */ __webpack_exports__["a"] = (Component.exports);


/***/ }),
/* 16 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_vux_loader_src_script_loader_js_vux_loader_src_script_loader_js_vue_loader_lib_selector_type_script_index_0_index_vue__ = __webpack_require__(77);
/* unused harmony namespace reexport */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__babel_loader_vux_loader_src_script_loader_js_vue_loader_lib_selector_type_script_index_0_index_vue__ = __webpack_require__(78);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__vue_loader_lib_template_compiler_index_id_data_v_ca2d6776_hasScoped_false_buble_transforms_vux_loader_src_before_template_compiler_loader_js_vux_loader_src_template_loader_js_vue_loader_lib_selector_type_template_index_0_index_vue__ = __webpack_require__(79);
function injectStyle (ssrContext) {
  __webpack_require__(76)
}
var normalizeComponent = __webpack_require__(0)
/* script */


/* template */

/* template functional */
var __vue_template_functional__ = false
/* styles */
var __vue_styles__ = injectStyle
/* scopeId */
var __vue_scopeId__ = null
/* moduleIdentifier (server only) */
var __vue_module_identifier__ = null
var Component = normalizeComponent(
  __WEBPACK_IMPORTED_MODULE_1__babel_loader_vux_loader_src_script_loader_js_vue_loader_lib_selector_type_script_index_0_index_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_2__vue_loader_lib_template_compiler_index_id_data_v_ca2d6776_hasScoped_false_buble_transforms_vux_loader_src_before_template_compiler_loader_js_vux_loader_src_template_loader_js_vue_loader_lib_selector_type_template_index_0_index_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)

/* harmony default export */ __webpack_exports__["a"] = (Component.exports);


/***/ }),
/* 17 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_vux_loader_src_script_loader_js_vux_loader_src_script_loader_js_vue_loader_lib_selector_type_script_index_0_index_vue__ = __webpack_require__(47);
/* unused harmony namespace reexport */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__babel_loader_vux_loader_src_script_loader_js_vue_loader_lib_selector_type_script_index_0_index_vue__ = __webpack_require__(48);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__vue_loader_lib_template_compiler_index_id_data_v_bcc96ffc_hasScoped_false_buble_transforms_vux_loader_src_before_template_compiler_loader_js_vux_loader_src_template_loader_js_vue_loader_lib_selector_type_template_index_0_index_vue__ = __webpack_require__(49);
function injectStyle (ssrContext) {
  __webpack_require__(46)
}
var normalizeComponent = __webpack_require__(0)
/* script */


/* template */

/* template functional */
var __vue_template_functional__ = false
/* styles */
var __vue_styles__ = injectStyle
/* scopeId */
var __vue_scopeId__ = null
/* moduleIdentifier (server only) */
var __vue_module_identifier__ = null
var Component = normalizeComponent(
  __WEBPACK_IMPORTED_MODULE_1__babel_loader_vux_loader_src_script_loader_js_vue_loader_lib_selector_type_script_index_0_index_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_2__vue_loader_lib_template_compiler_index_id_data_v_bcc96ffc_hasScoped_false_buble_transforms_vux_loader_src_before_template_compiler_loader_js_vux_loader_src_template_loader_js_vue_loader_lib_selector_type_template_index_0_index_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)

/* harmony default export */ __webpack_exports__["a"] = (Component.exports);


/***/ }),
/* 18 */,
/* 19 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_vux_loader_src_script_loader_js_vux_loader_src_script_loader_js_vue_loader_lib_selector_type_script_index_0_tabbar_vue__ = __webpack_require__(43);
/* unused harmony namespace reexport */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__babel_loader_vux_loader_src_script_loader_js_vue_loader_lib_selector_type_script_index_0_tabbar_vue__ = __webpack_require__(44);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__vue_loader_lib_template_compiler_index_id_data_v_df815522_hasScoped_false_buble_transforms_vux_loader_src_before_template_compiler_loader_js_vux_loader_src_template_loader_js_vue_loader_lib_selector_type_template_index_0_tabbar_vue__ = __webpack_require__(45);
function injectStyle (ssrContext) {
  __webpack_require__(42)
}
var normalizeComponent = __webpack_require__(0)
/* script */


/* template */

/* template functional */
var __vue_template_functional__ = false
/* styles */
var __vue_styles__ = injectStyle
/* scopeId */
var __vue_scopeId__ = null
/* moduleIdentifier (server only) */
var __vue_module_identifier__ = null
var Component = normalizeComponent(
  __WEBPACK_IMPORTED_MODULE_1__babel_loader_vux_loader_src_script_loader_js_vue_loader_lib_selector_type_script_index_0_tabbar_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_2__vue_loader_lib_template_compiler_index_id_data_v_df815522_hasScoped_false_buble_transforms_vux_loader_src_before_template_compiler_loader_js_vux_loader_src_template_loader_js_vue_loader_lib_selector_type_template_index_0_tabbar_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)

/* harmony default export */ __webpack_exports__["a"] = (Component.exports);


/***/ }),
/* 20 */,
/* 21 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_vux_loader_src_script_loader_js_vux_loader_src_script_loader_js_vue_loader_lib_selector_type_script_index_0_tab_vue__ = __webpack_require__(90);
/* unused harmony namespace reexport */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__babel_loader_vux_loader_src_script_loader_js_vue_loader_lib_selector_type_script_index_0_tab_vue__ = __webpack_require__(91);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__vue_loader_lib_template_compiler_index_id_data_v_2507d984_hasScoped_false_buble_transforms_vux_loader_src_before_template_compiler_loader_js_vux_loader_src_template_loader_js_vue_loader_lib_selector_type_template_index_0_tab_vue__ = __webpack_require__(92);
function injectStyle (ssrContext) {
  __webpack_require__(89)
}
var normalizeComponent = __webpack_require__(0)
/* script */


/* template */

/* template functional */
var __vue_template_functional__ = false
/* styles */
var __vue_styles__ = injectStyle
/* scopeId */
var __vue_scopeId__ = null
/* moduleIdentifier (server only) */
var __vue_module_identifier__ = null
var Component = normalizeComponent(
  __WEBPACK_IMPORTED_MODULE_1__babel_loader_vux_loader_src_script_loader_js_vue_loader_lib_selector_type_script_index_0_tab_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_2__vue_loader_lib_template_compiler_index_id_data_v_2507d984_hasScoped_false_buble_transforms_vux_loader_src_before_template_compiler_loader_js_vux_loader_src_template_loader_js_vue_loader_lib_selector_type_template_index_0_tab_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)

/* harmony default export */ __webpack_exports__["a"] = (Component.exports);


/***/ }),
/* 22 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_vux_loader_src_script_loader_js_vux_loader_src_script_loader_js_vue_loader_lib_selector_type_script_index_0_tab_item_vue__ = __webpack_require__(93);
/* unused harmony namespace reexport */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__babel_loader_vux_loader_src_script_loader_js_vue_loader_lib_selector_type_script_index_0_tab_item_vue__ = __webpack_require__(94);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__vue_loader_lib_template_compiler_index_id_data_v_9b8ab944_hasScoped_false_buble_transforms_vux_loader_src_before_template_compiler_loader_js_vux_loader_src_template_loader_js_vue_loader_lib_selector_type_template_index_0_tab_item_vue__ = __webpack_require__(95);
var normalizeComponent = __webpack_require__(0)
/* script */


/* template */

/* template functional */
var __vue_template_functional__ = false
/* styles */
var __vue_styles__ = null
/* scopeId */
var __vue_scopeId__ = null
/* moduleIdentifier (server only) */
var __vue_module_identifier__ = null
var Component = normalizeComponent(
  __WEBPACK_IMPORTED_MODULE_1__babel_loader_vux_loader_src_script_loader_js_vue_loader_lib_selector_type_script_index_0_tab_item_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_2__vue_loader_lib_template_compiler_index_id_data_v_9b8ab944_hasScoped_false_buble_transforms_vux_loader_src_before_template_compiler_loader_js_vux_loader_src_template_loader_js_vue_loader_lib_selector_type_template_index_0_tab_item_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)

/* harmony default export */ __webpack_exports__["a"] = (Component.exports);


/***/ }),
/* 23 */,
/* 24 */,
/* 25 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_vux_loader_src_script_loader_js_vux_loader_src_script_loader_js_vue_loader_lib_selector_type_script_index_0_tabbar_item_vue__ = __webpack_require__(58);
/* unused harmony namespace reexport */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__babel_loader_vux_loader_src_script_loader_js_vue_loader_lib_selector_type_script_index_0_tabbar_item_vue__ = __webpack_require__(59);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__vue_loader_lib_template_compiler_index_id_data_v_22fc8f9a_hasScoped_false_buble_transforms_vux_loader_src_before_template_compiler_loader_js_vux_loader_src_template_loader_js_vue_loader_lib_selector_type_template_index_0_tabbar_item_vue__ = __webpack_require__(60);
var normalizeComponent = __webpack_require__(0)
/* script */


/* template */

/* template functional */
var __vue_template_functional__ = false
/* styles */
var __vue_styles__ = null
/* scopeId */
var __vue_scopeId__ = null
/* moduleIdentifier (server only) */
var __vue_module_identifier__ = null
var Component = normalizeComponent(
  __WEBPACK_IMPORTED_MODULE_1__babel_loader_vux_loader_src_script_loader_js_vue_loader_lib_selector_type_script_index_0_tabbar_item_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_2__vue_loader_lib_template_compiler_index_id_data_v_22fc8f9a_hasScoped_false_buble_transforms_vux_loader_src_before_template_compiler_loader_js_vux_loader_src_template_loader_js_vue_loader_lib_selector_type_template_index_0_tabbar_item_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)

/* harmony default export */ __webpack_exports__["a"] = (Component.exports);


/***/ }),
/* 26 */,
/* 27 */
/***/ (function(module, exports, __webpack_require__) {

module.exports = { "default": __webpack_require__(167), __esModule: true };

/***/ }),
/* 28 */,
/* 29 */
/***/ (function(module, exports, __webpack_require__) {

/*!
 * vue-infinite-loading v2.4.0
 * (c) 2016-2018 PeachScript
 * MIT License
 */
!function(t,e){ true?module.exports=e():"function"==typeof define&&define.amd?define([],e):"object"==typeof exports?exports.VueInfiniteLoading=e():t.VueInfiniteLoading=e()}(window,function(){return function(t){var e={};function n(i){if(e[i])return e[i].exports;var r=e[i]={i:i,l:!1,exports:{}};return t[i].call(r.exports,r,r.exports,n),r.l=!0,r.exports}return n.m=t,n.c=e,n.d=function(t,e,i){n.o(t,e)||Object.defineProperty(t,e,{enumerable:!0,get:i})},n.r=function(t){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(t,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(t,"__esModule",{value:!0})},n.t=function(t,e){if(1&e&&(t=n(t)),8&e)return t;if(4&e&&"object"==typeof t&&t&&t.__esModule)return t;var i=Object.create(null);if(n.r(i),Object.defineProperty(i,"default",{enumerable:!0,value:t}),2&e&&"string"!=typeof t)for(var r in t)n.d(i,r,function(e){return t[e]}.bind(null,r));return i},n.n=function(t){var e=t&&t.__esModule?function(){return t.default}:function(){return t};return n.d(e,"a",e),e},n.o=function(t,e){return Object.prototype.hasOwnProperty.call(t,e)},n.p="/",n(n.s=9)}([function(t,e,n){var i=n(6);"string"==typeof i&&(i=[[t.i,i,""]]),i.locals&&(t.exports=i.locals);(0,n(3).default)("5d77b852",i,!0,{})},function(t,e,n){var i=n(8);"string"==typeof i&&(i=[[t.i,i,""]]),i.locals&&(t.exports=i.locals);(0,n(3).default)("cc73c0c2",i,!0,{})},function(t,e){t.exports=function(t){var e=[];return e.toString=function(){return this.map(function(e){var n=function(t,e){var n=t[1]||"",i=t[3];if(!i)return n;if(e&&"function"==typeof btoa){var r=function(t){return"/*# sourceMappingURL=data:application/json;charset=utf-8;base64,"+btoa(unescape(encodeURIComponent(JSON.stringify(t))))+" */"}(i),a=i.sources.map(function(t){return"/*# sourceURL="+i.sourceRoot+t+" */"});return[n].concat(a).concat([r]).join("\n")}return[n].join("\n")}(e,t);return e[2]?"@media "+e[2]+"{"+n+"}":n}).join("")},e.i=function(t,n){"string"==typeof t&&(t=[[null,t,""]]);for(var i={},r=0;r<this.length;r++){var a=this[r][0];"number"==typeof a&&(i[a]=!0)}for(r=0;r<t.length;r++){var o=t[r];"number"==typeof o[0]&&i[o[0]]||(n&&!o[2]?o[2]=n:n&&(o[2]="("+o[2]+") and ("+n+")"),e.push(o))}},e}},function(t,e,n){"use strict";function i(t,e){for(var n=[],i={},r=0;r<e.length;r++){var a=e[r],o=a[0],s={id:t+":"+r,css:a[1],media:a[2],sourceMap:a[3]};i[o]?i[o].parts.push(s):n.push(i[o]={id:o,parts:[s]})}return n}n.r(e),n.d(e,"default",function(){return b});var r="undefined"!=typeof document;if("undefined"!=typeof DEBUG&&DEBUG&&!r)throw new Error("vue-style-loader cannot be used in a non-browser environment. Use { target: 'node' } in your Webpack config to indicate a server-rendering environment.");var a={},o=r&&(document.head||document.getElementsByTagName("head")[0]),s=null,l=0,d=!1,c=function(){},u=null,p="data-vue-ssr-id",f="undefined"!=typeof navigator&&/msie [6-9]\b/.test(navigator.userAgent.toLowerCase());function b(t,e,n,r){d=n,u=r||{};var o=i(t,e);return h(o),function(e){for(var n=[],r=0;r<o.length;r++){var s=o[r];(l=a[s.id]).refs--,n.push(l)}e?h(o=i(t,e)):o=[];for(r=0;r<n.length;r++){var l;if(0===(l=n[r]).refs){for(var d=0;d<l.parts.length;d++)l.parts[d]();delete a[l.id]}}}}function h(t){for(var e=0;e<t.length;e++){var n=t[e],i=a[n.id];if(i){i.refs++;for(var r=0;r<i.parts.length;r++)i.parts[r](n.parts[r]);for(;r<n.parts.length;r++)i.parts.push(g(n.parts[r]));i.parts.length>n.parts.length&&(i.parts.length=n.parts.length)}else{var o=[];for(r=0;r<n.parts.length;r++)o.push(g(n.parts[r]));a[n.id]={id:n.id,refs:1,parts:o}}}}function m(){var t=document.createElement("style");return t.type="text/css",o.appendChild(t),t}function g(t){var e,n,i=document.querySelector("style["+p+'~="'+t.id+'"]');if(i){if(d)return c;i.parentNode.removeChild(i)}if(f){var r=l++;i=s||(s=m()),e=y.bind(null,i,r,!1),n=y.bind(null,i,r,!0)}else i=m(),e=function(t,e){var n=e.css,i=e.media,r=e.sourceMap;i&&t.setAttribute("media",i);u.ssrId&&t.setAttribute(p,e.id);r&&(n+="\n/*# sourceURL="+r.sources[0]+" */",n+="\n/*# sourceMappingURL=data:application/json;base64,"+btoa(unescape(encodeURIComponent(JSON.stringify(r))))+" */");if(t.styleSheet)t.styleSheet.cssText=n;else{for(;t.firstChild;)t.removeChild(t.firstChild);t.appendChild(document.createTextNode(n))}}.bind(null,i),n=function(){i.parentNode.removeChild(i)};return e(t),function(i){if(i){if(i.css===t.css&&i.media===t.media&&i.sourceMap===t.sourceMap)return;e(t=i)}else n()}}var v=function(){var t=[];return function(e,n){return t[e]=n,t.filter(Boolean).join("\n")}}();function y(t,e,n,i){var r=n?"":i.css;if(t.styleSheet)t.styleSheet.cssText=v(e,r);else{var a=document.createTextNode(r),o=t.childNodes;o[e]&&t.removeChild(o[e]),o.length?t.insertBefore(a,o[e]):t.appendChild(a)}}},function(t,e){function n(t){return(n="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(t){return typeof t}:function(t){return t&&"function"==typeof Symbol&&t.constructor===Symbol&&t!==Symbol.prototype?"symbol":typeof t})(t)}function i(e){return"function"==typeof Symbol&&"symbol"===n(Symbol.iterator)?t.exports=i=function(t){return n(t)}:t.exports=i=function(t){return t&&"function"==typeof Symbol&&t.constructor===Symbol&&t!==Symbol.prototype?"symbol":n(t)},i(e)}t.exports=i},function(t,e,n){"use strict";var i=n(0);n.n(i).a},function(t,e,n){(t.exports=n(2)(!1)).push([t.i,'.loading-wave-dots[data-v-46b20d22]{position:relative}.loading-wave-dots[data-v-46b20d22] .wave-item{position:absolute;top:50%;left:50%;display:inline-block;margin-top:-4px;width:8px;height:8px;border-radius:50%;-webkit-animation:loading-wave-dots-data-v-46b20d22 linear 2.8s infinite;animation:loading-wave-dots-data-v-46b20d22 linear 2.8s infinite}.loading-wave-dots[data-v-46b20d22] .wave-item:first-child{margin-left:-36px}.loading-wave-dots[data-v-46b20d22] .wave-item:nth-child(2){margin-left:-20px;-webkit-animation-delay:.14s;animation-delay:.14s}.loading-wave-dots[data-v-46b20d22] .wave-item:nth-child(3){margin-left:-4px;-webkit-animation-delay:.28s;animation-delay:.28s}.loading-wave-dots[data-v-46b20d22] .wave-item:nth-child(4){margin-left:12px;-webkit-animation-delay:.42s;animation-delay:.42s}.loading-wave-dots[data-v-46b20d22] .wave-item:last-child{margin-left:28px;-webkit-animation-delay:.56s;animation-delay:.56s}@-webkit-keyframes loading-wave-dots-data-v-46b20d22{0%{-webkit-transform:translateY(0);transform:translateY(0);background:#bbb}10%{-webkit-transform:translateY(-6px);transform:translateY(-6px);background:#999}20%{-webkit-transform:translateY(0);transform:translateY(0);background:#bbb}to{-webkit-transform:translateY(0);transform:translateY(0);background:#bbb}}@keyframes loading-wave-dots-data-v-46b20d22{0%{-webkit-transform:translateY(0);transform:translateY(0);background:#bbb}10%{-webkit-transform:translateY(-6px);transform:translateY(-6px);background:#999}20%{-webkit-transform:translateY(0);transform:translateY(0);background:#bbb}to{-webkit-transform:translateY(0);transform:translateY(0);background:#bbb}}.loading-circles[data-v-46b20d22] .circle-item{width:5px;height:5px;-webkit-animation:loading-circles-data-v-46b20d22 linear .75s infinite;animation:loading-circles-data-v-46b20d22 linear .75s infinite}.loading-circles[data-v-46b20d22] .circle-item:first-child{margin-top:-14.5px;margin-left:-2.5px}.loading-circles[data-v-46b20d22] .circle-item:nth-child(2){margin-top:-11.26px;margin-left:6.26px}.loading-circles[data-v-46b20d22] .circle-item:nth-child(3){margin-top:-2.5px;margin-left:9.5px}.loading-circles[data-v-46b20d22] .circle-item:nth-child(4){margin-top:6.26px;margin-left:6.26px}.loading-circles[data-v-46b20d22] .circle-item:nth-child(5){margin-top:9.5px;margin-left:-2.5px}.loading-circles[data-v-46b20d22] .circle-item:nth-child(6){margin-top:6.26px;margin-left:-11.26px}.loading-circles[data-v-46b20d22] .circle-item:nth-child(7){margin-top:-2.5px;margin-left:-14.5px}.loading-circles[data-v-46b20d22] .circle-item:last-child{margin-top:-11.26px;margin-left:-11.26px}@-webkit-keyframes loading-circles-data-v-46b20d22{0%{background:#dfdfdf}90%{background:#505050}to{background:#dfdfdf}}@keyframes loading-circles-data-v-46b20d22{0%{background:#dfdfdf}90%{background:#505050}to{background:#dfdfdf}}.loading-bubbles[data-v-46b20d22] .bubble-item{background:#666;-webkit-animation:loading-bubbles-data-v-46b20d22 linear .75s infinite;animation:loading-bubbles-data-v-46b20d22 linear .75s infinite}.loading-bubbles[data-v-46b20d22] .bubble-item:first-child{margin-top:-12.5px;margin-left:-.5px}.loading-bubbles[data-v-46b20d22] .bubble-item:nth-child(2){margin-top:-9.26px;margin-left:8.26px}.loading-bubbles[data-v-46b20d22] .bubble-item:nth-child(3){margin-top:-.5px;margin-left:11.5px}.loading-bubbles[data-v-46b20d22] .bubble-item:nth-child(4){margin-top:8.26px;margin-left:8.26px}.loading-bubbles[data-v-46b20d22] .bubble-item:nth-child(5){margin-top:11.5px;margin-left:-.5px}.loading-bubbles[data-v-46b20d22] .bubble-item:nth-child(6){margin-top:8.26px;margin-left:-9.26px}.loading-bubbles[data-v-46b20d22] .bubble-item:nth-child(7){margin-top:-.5px;margin-left:-12.5px}.loading-bubbles[data-v-46b20d22] .bubble-item:last-child{margin-top:-9.26px;margin-left:-9.26px}@-webkit-keyframes loading-bubbles-data-v-46b20d22{0%{width:1px;height:1px;box-shadow:0 0 0 3px #666}90%{width:1px;height:1px;box-shadow:0 0 0 0 #666}to{width:1px;height:1px;box-shadow:0 0 0 3px #666}}@keyframes loading-bubbles-data-v-46b20d22{0%{width:1px;height:1px;box-shadow:0 0 0 3px #666}90%{width:1px;height:1px;box-shadow:0 0 0 0 #666}to{width:1px;height:1px;box-shadow:0 0 0 3px #666}}.loading-default[data-v-46b20d22]{position:relative;border:1px solid #999;-webkit-animation:loading-rotating-data-v-46b20d22 ease 1.5s infinite;animation:loading-rotating-data-v-46b20d22 ease 1.5s infinite}.loading-default[data-v-46b20d22]:before{content:"";position:absolute;display:block;top:0;left:50%;margin-top:-3px;margin-left:-3px;width:6px;height:6px;background-color:#999;border-radius:50%}.loading-spiral[data-v-46b20d22]{border:2px solid #777;border-right-color:transparent;-webkit-animation:loading-rotating-data-v-46b20d22 linear .85s infinite;animation:loading-rotating-data-v-46b20d22 linear .85s infinite}@-webkit-keyframes loading-rotating-data-v-46b20d22{0%{-webkit-transform:rotate(0);transform:rotate(0)}to{-webkit-transform:rotate(1turn);transform:rotate(1turn)}}@keyframes loading-rotating-data-v-46b20d22{0%{-webkit-transform:rotate(0);transform:rotate(0)}to{-webkit-transform:rotate(1turn);transform:rotate(1turn)}}.loading-bubbles[data-v-46b20d22],.loading-circles[data-v-46b20d22]{position:relative}.loading-bubbles[data-v-46b20d22] .bubble-item,.loading-circles[data-v-46b20d22] .circle-item{position:absolute;top:50%;left:50%;display:inline-block;border-radius:50%}.loading-bubbles[data-v-46b20d22] .bubble-item:nth-child(2),.loading-circles[data-v-46b20d22] .circle-item:nth-child(2){-webkit-animation-delay:93ms;animation-delay:93ms}.loading-bubbles[data-v-46b20d22] .bubble-item:nth-child(3),.loading-circles[data-v-46b20d22] .circle-item:nth-child(3){-webkit-animation-delay:.186s;animation-delay:.186s}.loading-bubbles[data-v-46b20d22] .bubble-item:nth-child(4),.loading-circles[data-v-46b20d22] .circle-item:nth-child(4){-webkit-animation-delay:.279s;animation-delay:.279s}.loading-bubbles[data-v-46b20d22] .bubble-item:nth-child(5),.loading-circles[data-v-46b20d22] .circle-item:nth-child(5){-webkit-animation-delay:.372s;animation-delay:.372s}.loading-bubbles[data-v-46b20d22] .bubble-item:nth-child(6),.loading-circles[data-v-46b20d22] .circle-item:nth-child(6){-webkit-animation-delay:.465s;animation-delay:.465s}.loading-bubbles[data-v-46b20d22] .bubble-item:nth-child(7),.loading-circles[data-v-46b20d22] .circle-item:nth-child(7){-webkit-animation-delay:.558s;animation-delay:.558s}.loading-bubbles[data-v-46b20d22] .bubble-item:last-child,.loading-circles[data-v-46b20d22] .circle-item:last-child{-webkit-animation-delay:.651s;animation-delay:.651s}',""])},function(t,e,n){"use strict";var i=n(1);n.n(i).a},function(t,e,n){(t.exports=n(2)(!1)).push([t.i,".infinite-loading-container[data-v-1ff3c730]{clear:both;text-align:center}.infinite-loading-container[data-v-1ff3c730] [class^=loading-]{display:inline-block;margin:5px 0;width:28px;height:28px;font-size:28px;line-height:28px;border-radius:50%}.btn-try-infinite[data-v-1ff3c730]{margin-top:5px;padding:5px 10px;color:#999;font-size:14px;line-height:1;background:transparent;border:1px solid #ccc;border-radius:3px;outline:none;cursor:pointer}.btn-try-infinite[data-v-1ff3c730]:not(:active):hover{opacity:.8}",""])},function(t,e,n){"use strict";n.r(e);var i={throttleLimit:50,loopCheckTimeout:1e3,loopCheckMaxCalls:10},r=function(){var t=!1;try{var e=Object.defineProperty({},"passive",{get:function(){return t={passive:!0},!0}});window.addEventListener("testpassive",e,e),window.remove("testpassive",e,e)}catch(t){}return t}(),a={STATE_CHANGER:["emit `loaded` and `complete` event through component instance of `$refs` may cause error, so it will be deprecated soon, please use the `$state` argument instead (`$state` just the special `$event` variable):","\ntemplate:",'<infinite-loading @infinite="infiniteHandler"></infinite-loading>',"\nscript:\n...\ninfiniteHandler($state) {\n  ajax('https://www.example.com/api/news')\n    .then((res) => {\n      if (res.data.length) {\n        $state.loaded();\n      } else {\n        $state.complete();\n      }\n    });\n}\n...","","more details: https://github.com/PeachScript/vue-infinite-loading/issues/57#issuecomment-324370549"].join("\n"),INFINITE_EVENT:"`:on-infinite` property will be deprecated soon, please use `@infinite` event instead.",IDENTIFIER:"the `reset` event will be deprecated soon, please reset this component by change the `identifier` property."},o={INFINITE_LOOP:["executed the callback function more than ".concat(i.loopCheckMaxCalls," times for a short time, it looks like searched a wrong scroll wrapper that doest not has fixed height or maximum height, please check it. If you want to force to set a element as scroll wrapper ranther than automatic searching, you can do this:"),'\n\x3c!-- add a special attribute for the real scroll wrapper --\x3e\n<div infinite-wrapper>\n  ...\n  \x3c!-- set force-use-infinite-wrapper --\x3e\n  <infinite-loading force-use-infinite-wrapper></infinite-loading>\n</div>\nor\n<div class="infinite-wrapper">\n  ...\n  \x3c!-- set force-use-infinite-wrapper as css selector of the real scroll wrapper --\x3e\n  <infinite-loading force-use-infinite-wrapper=".infinite-wrapper"></infinite-loading>\n</div>\n    ',"more details: https://github.com/PeachScript/vue-infinite-loading/issues/55#issuecomment-316934169"].join("\n")},s={READY:0,LOADING:1,COMPLETE:2,ERROR:3},l={color:"#666",fontSize:"14px",padding:"10px 0"},d={mode:"development",props:{spinner:"default",distance:100,forceUseInfiniteWrapper:!1},system:i,slots:{noResults:"No results :(",noMore:"No more data :)",error:"Opps, something went wrong :(",errorBtnText:"Retry",spinner:""},WARNINGS:a,ERRORS:o,STATUS:s},c=n(4),u=n.n(c),p={BUBBLES:{render:function(t){return t("span",{attrs:{class:"loading-bubbles"}},Array.apply(Array,Array(8)).map(function(){return t("span",{attrs:{class:"bubble-item"}})}))}},CIRCLES:{render:function(t){return t("span",{attrs:{class:"loading-circles"}},Array.apply(Array,Array(8)).map(function(){return t("span",{attrs:{class:"circle-item"}})}))}},DEFAULT:{render:function(t){return t("i",{attrs:{class:"loading-default"}})}},SPIRAL:{render:function(t){return t("i",{attrs:{class:"loading-spiral"}})}},WAVEDOTS:{render:function(t){return t("span",{attrs:{class:"loading-wave-dots"}},Array.apply(Array,Array(5)).map(function(){return t("span",{attrs:{class:"wave-item"}})}))}}},f={name:"Spinner",computed:{spinnerView:function(){return p[(this.$attrs.spinner||"").toUpperCase()]||this.spinnerInConfig},spinnerInConfig:function(){return d.slots.spinner&&"string"==typeof d.slots.spinner?{render:function(){return this._v(d.slots.spinner)}}:"object"===u()(d.slots.spinner)?d.slots.spinner:p[d.props.spinner.toUpperCase()]||p.DEFAULT}}};n(5);function b(t,e,n,i,r,a,o,s){var l,d="function"==typeof t?t.options:t;if(e&&(d.render=e,d.staticRenderFns=n,d._compiled=!0),i&&(d.functional=!0),a&&(d._scopeId="data-v-"+a),o?(l=function(t){(t=t||this.$vnode&&this.$vnode.ssrContext||this.parent&&this.parent.$vnode&&this.parent.$vnode.ssrContext)||"undefined"==typeof __VUE_SSR_CONTEXT__||(t=__VUE_SSR_CONTEXT__),r&&r.call(this,t),t&&t._registeredComponents&&t._registeredComponents.add(o)},d._ssrRegister=l):r&&(l=s?function(){r.call(this,this.$root.$options.shadowRoot)}:r),l)if(d.functional){d._injectStyles=l;var c=d.render;d.render=function(t,e){return l.call(e),c(t,e)}}else{var u=d.beforeCreate;d.beforeCreate=u?[].concat(u,l):[l]}return{exports:t,options:d}}var h=b(f,function(){var t=this.$createElement;return(this._self._c||t)(this.spinnerView,{tag:"component"})},[],!1,null,"46b20d22",null);h.options.__file="Spinner.vue";var m=h.exports;function g(t){"production"!==d.mode&&console.warn("[Vue-infinite-loading warn]: ".concat(t))}function v(t){console.error("[Vue-infinite-loading error]: ".concat(t))}var y={caches:[],throttle:function(t){var e=this;-1===this.caches.indexOf(t)&&(this.caches.push(t),setTimeout(function(){t(),e.caches.splice(e.caches.indexOf(t),1)},d.system.throttleLimit))},reset:function(){this.caches=[]}},w={isChecked:!1,timer:null,times:0,track:function(){var t=this;this.times+=1,clearTimeout(this.timer),this.timer=setTimeout(function(){t.isChecked=!0},d.system.loopCheckTimeout),this.times>d.system.loopCheckMaxCalls&&(v(o.INFINITE_LOOP),this.isChecked=!0)}},x={key:"_infiniteScrollHeight",getScrollElm:function(t){return t===window?document.documentElement:t},save:function(t){var e=this.getScrollElm(t);e[this.key]=e.scrollHeight},restore:function(t){var e=this.getScrollElm(t);"number"==typeof e[this.key]&&(e.scrollTop=e.scrollHeight-e[this.key]+e.scrollTop),this.remove(e)},remove:function(t){void 0!==t[this.key]&&delete t[this.key]}};function k(t){return t.replace(/[A-Z]/g,function(t){return"-".concat(t.toLowerCase())})}var S={name:"InfiniteLoading",data:function(){return{scrollParent:null,scrollHandler:null,isFirstLoad:!0,status:s.READY,slots:d.slots}},components:{Spinner:m},computed:{isShowSpinner:function(){return this.status===s.LOADING},isShowError:function(){return this.status===s.ERROR},isShowNoResults:function(){return this.status===s.COMPLETE&&this.isFirstLoad},isShowNoMore:function(){return this.status===s.COMPLETE&&!this.isFirstLoad},slotStyles:function(){var t=this,e={};return Object.keys(d.slots).forEach(function(n){var i=k(n);(!t.$slots[i]&&!d.slots[n].render||t.$slots[i]&&!t.$slots[i][0].tag)&&(e[n]=l)}),e}},props:{distance:{type:Number,default:d.props.distance},spinner:String,direction:{type:String,default:d.props.direction},forceUseInfiniteWrapper:{type:[Boolean,String],default:d.props.forceUseInfiniteWrapper},identifier:{default:+new Date},onInfinite:Function},watch:{identifier:function(){this.stateChanger.reset()}},mounted:function(){var t=this;this.$watch("forceUseInfiniteWrapper",function(){t.scrollParent=t.getScrollParent()},{immediate:!0}),this.scrollHandler=function(t){this.status===s.READY&&(t&&t.constructor===Event?y.throttle(this.attemptLoad):this.attemptLoad())}.bind(this),setTimeout(this.scrollHandler,1),this.scrollParent.addEventListener("scroll",this.scrollHandler,r),this.$on("$InfiniteLoading:loaded",function(e){t.isFirstLoad=!1,"top"===t.direction&&t.$nextTick(function(){x.restore(t.scrollParent)}),t.status===s.LOADING&&t.$nextTick(t.attemptLoad.bind(null,!0)),e&&e.target===t||g(a.STATE_CHANGER)}),this.$on("$InfiniteLoading:complete",function(e){t.status=s.COMPLETE,t.$nextTick(function(){t.$forceUpdate()}),t.scrollParent.removeEventListener("scroll",t.scrollHandler,r),e&&e.target===t||g(a.STATE_CHANGER)}),this.$on("$InfiniteLoading:reset",function(e){t.status=s.READY,t.isFirstLoad=!0,y.reset(),x.remove(t.scrollParent),t.scrollParent.addEventListener("scroll",t.scrollHandler,r),setTimeout(t.scrollHandler,1),e&&e.target===t||g(a.IDENTIFIER)}),this.stateChanger={loaded:function(){t.$emit("$InfiniteLoading:loaded",{target:t})},complete:function(){t.$emit("$InfiniteLoading:complete",{target:t})},reset:function(){t.$emit("$InfiniteLoading:reset",{target:t})},error:function(){t.status=s.ERROR,y.reset()}},this.onInfinite&&g(a.INFINITE_EVENT)},deactivated:function(){this.status===s.LOADING&&(this.status=s.READY),this.scrollParent.removeEventListener("scroll",this.scrollHandler,r)},activated:function(){this.scrollParent.addEventListener("scroll",this.scrollHandler,r)},methods:{attemptLoad:function(t){var e=this,n=this.getCurrentDistance();this.status!==s.COMPLETE&&n<=this.distance&&this.$el.offsetWidth+this.$el.offsetHeight>0?(this.status=s.LOADING,"top"===this.direction&&this.$nextTick(function(){x.save(e.scrollParent)}),"function"==typeof this.onInfinite?this.onInfinite.call(null,this.stateChanger):this.$emit("infinite",this.stateChanger),!t||this.forceUseInfiniteWrapper||w.isChecked||w.track()):this.status===s.LOADING&&(this.status=s.READY)},getCurrentDistance:function(){var t;"top"===this.direction?t="number"==typeof this.scrollParent.scrollTop?this.scrollParent.scrollTop:this.scrollParent.pageYOffset:t=this.$el.getBoundingClientRect().top-(this.scrollParent===window?window.innerHeight:this.scrollParent.getBoundingClientRect().bottom);return t},getScrollParent:function(){var t,e=arguments.length>0&&void 0!==arguments[0]?arguments[0]:this.$el;return"string"==typeof this.forceUseInfiniteWrapper&&(t=e.querySelector(this.forceUseInfiniteWrapper)),t||("BODY"===e.tagName?t=window:!this.forceUseInfiniteWrapper&&["scroll","auto"].indexOf(getComputedStyle(e).overflowY)>-1?t=e:(e.hasAttribute("infinite-wrapper")||e.hasAttribute("data-infinite-wrapper"))&&(t=e)),t||this.getScrollParent(e.parentNode)}},destroyed:function(){!this.status!==s.COMPLETE&&(y.reset(),x.remove(this.scrollParent),this.scrollParent.removeEventListener("scroll",this.scrollHandler,r))}},E=(n(7),b(S,function(){var t=this,e=t.$createElement,n=t._self._c||e;return n("div",{staticClass:"infinite-loading-container"},[n("div",{directives:[{name:"show",rawName:"v-show",value:t.isShowSpinner,expression:"isShowSpinner"}],staticClass:"infinite-status-prompt",style:t.slotStyles.spinner},[t._t("spinner",[n("spinner",{attrs:{spinner:t.spinner}})])],2),t._v(" "),n("div",{directives:[{name:"show",rawName:"v-show",value:t.isShowNoResults,expression:"isShowNoResults"}],staticClass:"infinite-status-prompt",style:t.slotStyles.noResults},[t._t("no-results",[t.slots.noResults.render?n(t.slots.noResults,{tag:"component"}):[t._v(t._s(t.slots.noResults))]])],2),t._v(" "),n("div",{directives:[{name:"show",rawName:"v-show",value:t.isShowNoMore,expression:"isShowNoMore"}],staticClass:"infinite-status-prompt",style:t.slotStyles.noMore},[t._t("no-more",[t.slots.noMore.render?n(t.slots.noMore,{tag:"component"}):[t._v(t._s(t.slots.noMore))]])],2),t._v(" "),n("div",{directives:[{name:"show",rawName:"v-show",value:t.isShowError,expression:"isShowError"}],staticClass:"infinite-status-prompt",style:t.slotStyles.error},[t._t("error",[t.slots.error.render?n(t.slots.error,{tag:"component",attrs:{trigger:t.attemptLoad}}):[t._v("\n        "+t._s(t.slots.error)+"\n        "),n("br"),t._v(" "),n("button",{staticClass:"btn-try-infinite",domProps:{textContent:t._s(t.slots.errorBtnText)},on:{click:t.attemptLoad}})]],{trigger:t.attemptLoad})],2)])},[],!1,null,"1ff3c730",null));E.options.__file="InfiniteLoading.vue";var C=E.exports;function I(t){d.mode=t.config.productionTip?"development":"production"}Object.defineProperty(C,"install",{configurable:!1,enumerable:!1,value:function(t,e){Object.assign(d.props,e&&e.props),Object.assign(d.slots,e&&e.slots),Object.assign(d.system,e&&e.system),t.component("infinite-loading",C),I(t)}}),"undefined"!=typeof window&&window.Vue&&(window.Vue.component("infinite-loading",C),I(window.Vue));e.default=C}])});

/***/ }),
/* 30 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_vux_loader_src_script_loader_js_vux_loader_src_script_loader_js_vue_loader_lib_selector_type_script_index_0_index_vue__ = __webpack_require__(70);
/* unused harmony namespace reexport */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__babel_loader_vux_loader_src_script_loader_js_vue_loader_lib_selector_type_script_index_0_index_vue__ = __webpack_require__(71);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__vue_loader_lib_template_compiler_index_id_data_v_53121ac7_hasScoped_false_buble_transforms_vux_loader_src_before_template_compiler_loader_js_vux_loader_src_template_loader_js_vue_loader_lib_selector_type_template_index_0_index_vue__ = __webpack_require__(72);
var normalizeComponent = __webpack_require__(0)
/* script */


/* template */

/* template functional */
var __vue_template_functional__ = false
/* styles */
var __vue_styles__ = null
/* scopeId */
var __vue_scopeId__ = null
/* moduleIdentifier (server only) */
var __vue_module_identifier__ = null
var Component = normalizeComponent(
  __WEBPACK_IMPORTED_MODULE_1__babel_loader_vux_loader_src_script_loader_js_vue_loader_lib_selector_type_script_index_0_index_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_2__vue_loader_lib_template_compiler_index_id_data_v_53121ac7_hasScoped_false_buble_transforms_vux_loader_src_before_template_compiler_loader_js_vux_loader_src_template_loader_js_vue_loader_lib_selector_type_template_index_0_index_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)

/* harmony default export */ __webpack_exports__["a"] = (Component.exports);


/***/ }),
/* 31 */,
/* 32 */,
/* 33 */,
/* 34 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__ym_abnor_index__ = __webpack_require__(171);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__ym_footer_index_vue__ = __webpack_require__(176);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__ym_article_index_vue__ = __webpack_require__(181);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_3__ym_uploader_index_vue__ = __webpack_require__(186);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_4__ym_grid_index_vue__ = __webpack_require__(191);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_5__ym_agree_index_vue__ = __webpack_require__(195);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_6__ln_grid_index_vue__ = __webpack_require__(200);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_7__ln_footer_index_vue__ = __webpack_require__(205);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_8__ln_xgrid_index_vue__ = __webpack_require__(210);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_9__ln_adv_index_vue__ = __webpack_require__(214);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_10__ln_banner_index_vue__ = __webpack_require__(218);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_11__ln_notice_index_vue__ = __webpack_require__(223);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_12__ln_cube_index_vue__ = __webpack_require__(235);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_13__ln_nav_index_vue__ = __webpack_require__(240);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_14__ln_twonav_index_vue__ = __webpack_require__(245);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_15__ln_goods_index_vue__ = __webpack_require__(250);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_16__ln_doors_index_vue__ = __webpack_require__(255);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_17__ln_agree_index_vue__ = __webpack_require__(264);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_18__ln_uploader_index_vue__ = __webpack_require__(269);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_19__ln_panel_index_vue__ = __webpack_require__(274);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_20__ln_header_index_vue__ = __webpack_require__(279);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_21__ln_button_index_vue__ = __webpack_require__(284);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_22__ln_tarbar_index_vue__ = __webpack_require__(289);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_23__ln_previewer_index_vue__ = __webpack_require__(294);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_24__ln_bannerx_index_vue__ = __webpack_require__(299);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_25__ln_sign_index_vue__ = __webpack_require__(303);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_26__ln_filter_bar__ = __webpack_require__(308);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_27__ln_loading_indexApp__ = __webpack_require__(318);
/* harmony reexport (binding) */ __webpack_require__.d(__webpack_exports__, "i", function() { return __WEBPACK_IMPORTED_MODULE_0__ym_abnor_index__["a"]; });
/* unused harmony reexport YmFooter */
/* unused harmony reexport YmArticle */
/* unused harmony reexport YmUploader */
/* unused harmony reexport YmGrid */
/* harmony reexport (binding) */ __webpack_require__.d(__webpack_exports__, "j", function() { return __WEBPACK_IMPORTED_MODULE_5__ym_agree_index_vue__["a"]; });
/* unused harmony reexport LnGrid */
/* unused harmony reexport LnFooter */
/* unused harmony reexport LnXgrid */
/* unused harmony reexport LnAdv */
/* unused harmony reexport LnBanner */
/* unused harmony reexport LnNotice */
/* unused harmony reexport LnCube */
/* unused harmony reexport LnNav */
/* unused harmony reexport LnTwonav */
/* unused harmony reexport LnGoods */
/* unused harmony reexport LnDoors */
/* harmony reexport (binding) */ __webpack_require__.d(__webpack_exports__, "a", function() { return __WEBPACK_IMPORTED_MODULE_17__ln_agree_index_vue__["a"]; });
/* harmony reexport (binding) */ __webpack_require__.d(__webpack_exports__, "h", function() { return __WEBPACK_IMPORTED_MODULE_18__ln_uploader_index_vue__["a"]; });
/* harmony reexport (binding) */ __webpack_require__.d(__webpack_exports__, "e", function() { return __WEBPACK_IMPORTED_MODULE_19__ln_panel_index_vue__["a"]; });
/* unused harmony reexport LnHeader */
/* harmony reexport (binding) */ __webpack_require__.d(__webpack_exports__, "b", function() { return __WEBPACK_IMPORTED_MODULE_21__ln_button_index_vue__["a"]; });
/* unused harmony reexport LnTarbar */
/* harmony reexport (binding) */ __webpack_require__.d(__webpack_exports__, "f", function() { return __WEBPACK_IMPORTED_MODULE_23__ln_previewer_index_vue__["a"]; });
/* unused harmony reexport LnBannerx */
/* harmony reexport (binding) */ __webpack_require__.d(__webpack_exports__, "g", function() { return __WEBPACK_IMPORTED_MODULE_25__ln_sign_index_vue__["a"]; });
/* harmony reexport (binding) */ __webpack_require__.d(__webpack_exports__, "c", function() { return __WEBPACK_IMPORTED_MODULE_26__ln_filter_bar__["a"]; });
/* unused harmony reexport LnFilterBarPop */
/* harmony reexport (binding) */ __webpack_require__.d(__webpack_exports__, "d", function() { return __WEBPACK_IMPORTED_MODULE_27__ln_loading_indexApp__["a"]; });






























/***/ }),
/* 35 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_vux_loader_src_script_loader_js_vux_loader_src_script_loader_js_vue_loader_lib_selector_type_script_index_0_index_vue__ = __webpack_require__(97);
/* unused harmony namespace reexport */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__babel_loader_vux_loader_src_script_loader_js_vue_loader_lib_selector_type_script_index_0_index_vue__ = __webpack_require__(98);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__vue_loader_lib_template_compiler_index_id_data_v_02f41544_hasScoped_false_buble_transforms_vux_loader_src_before_template_compiler_loader_js_vux_loader_src_template_loader_js_vue_loader_lib_selector_type_template_index_0_index_vue__ = __webpack_require__(99);
function injectStyle (ssrContext) {
  __webpack_require__(96)
}
var normalizeComponent = __webpack_require__(0)
/* script */


/* template */

/* template functional */
var __vue_template_functional__ = false
/* styles */
var __vue_styles__ = injectStyle
/* scopeId */
var __vue_scopeId__ = null
/* moduleIdentifier (server only) */
var __vue_module_identifier__ = null
var Component = normalizeComponent(
  __WEBPACK_IMPORTED_MODULE_1__babel_loader_vux_loader_src_script_loader_js_vue_loader_lib_selector_type_script_index_0_index_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_2__vue_loader_lib_template_compiler_index_id_data_v_02f41544_hasScoped_false_buble_transforms_vux_loader_src_before_template_compiler_loader_js_vux_loader_src_template_loader_js_vue_loader_lib_selector_type_template_index_0_index_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)

/* harmony default export */ __webpack_exports__["a"] = (Component.exports);


/***/ }),
/* 36 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony default export */ __webpack_exports__["a"] = (function () {
  return {
    title: [String, Number],
    value: [String, Number, Array],
    isLink: Boolean,
    isLoading: Boolean,
    inlineDesc: [String, Number],
    primary: {
      type: String,
      default: 'title'
    },
    link: [String, Object],
    valueAlign: [String, Boolean, Number],
    borderIntent: {
      type: Boolean,
      default: true
    },
    disabled: Boolean,
    arrowDirection: String,
    alignItems: String
  };
});

/***/ }),
/* 37 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony default export */ __webpack_exports__["a"] = (function (self, name) {
  if (self.$parent && typeof self.$parent[name] !== 'undefined') {
    return self.$parent[name];
  }
  if (self.$parent && self.$parent.$parent && typeof self.$parent.$parent[name] !== 'undefined') {
    return self.$parent.$parent[name];
  }
});

/***/ }),
/* 38 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";

function gtIOS6() {
  var userAgent = window.navigator.userAgent;
  var ios = userAgent.match(/(iPad|iPhone|iPod)\s+OS\s([\d_.]+)/);
  return ios && ios[2] && parseInt(ios[2].replace(/_/g, '.'), 10) >= 6;
}

function isSupportSticky() {
  var prefixTestList = ['', '-webkit-', '-ms-', '-moz-', '-o-'];
  var stickyText = '';
  for (var i = 0; i < prefixTestList.length; i++) {
    stickyText += 'position:' + prefixTestList[i] + 'sticky';
  }

  var div = document.createElement('div');
  var body = document.body;
  div.style.cssText = 'display:none' + stickyText;
  body.appendChild(div);
  var isSupport = /sticky/i.test(window.getComputedStyle(div).position);
  body.removeChild(div);
  div = null;
  return isSupport;
}

/* harmony default export */ __webpack_exports__["a"] = (function (nav) {
  var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};

  var scrollBox = options.scrollBox || window;
  var offset = options.offset || 0;
  var checkStickySupport = options.checkStickySupport === true || false;
  if (typeof scrollBox === 'string') {
    scrollBox = document.getElementById(scrollBox);
    if (!scrollBox) {
      if (false) {
        console.error('[VUX] sticky:scroll-box element doesn\'t exist');
      }
      return;
    }
  }

  var navOffsetY = nav.offsetTop - offset;
  scrollBox.removeEventListener('scroll', scrollBox.e);

  var getTop = function getTop() {
    if (scrollBox === window) {
      return document.documentElement && document.documentElement.scrollTop || document.body.scrollTop;
    } else {
      return scrollBox.scrollTop;
    }
  };

  var getFillElem = function getFillElem(el) {
    var next = el.nextSibling;

    while (next.nodeType !== 1) {
      next = next.nextSibling;
    }
    if (next.classList.contains('vux-sticky-fill')) {
      return next;
    }

    return el.parentNode;
  };

  var scrollHandler = function scrollHandler() {
    var distance = getTop();
    if (distance > navOffsetY) {
      nav.style.top = offset + 'px';
      nav.classList.add('vux-fixed');
    } else {
      nav.classList.remove('vux-fixed');
    }
  };

  if (checkStickySupport && (gtIOS6() || isSupportSticky())) {
    nav.style.top = offset + 'px';

    nav.classList.add('vux-sticky');
  } else {
    if (nav.classList.contains('vux-fixed')) {
      var top = getTop();
      navOffsetY = getFillElem(nav).offsetTop - offset;
      if (top < navOffsetY) {
        nav.classList.remove('vux-fixed');
      }
    } else {
      navOffsetY = nav.offsetTop - offset;
    }
    scrollBox.e = scrollHandler;
    scrollBox.addEventListener('scroll', scrollHandler);
  }
});

/***/ }),
/* 39 */,
/* 40 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony export (immutable) */ __webpack_exports__["a"] = go;
/* unused harmony export getUrl */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_babel_runtime_helpers_typeof__ = __webpack_require__(20);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_babel_runtime_helpers_typeof___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0_babel_runtime_helpers_typeof__);

function go(url, $router) {
    if (/^javas/.test(url) || !url) return;
    var useRouter = (typeof url === 'undefined' ? 'undefined' : __WEBPACK_IMPORTED_MODULE_0_babel_runtime_helpers_typeof___default()(url)) === 'object' || $router && typeof url === 'string' && !/http/.test(url);
    if (useRouter) {
        if ((typeof url === 'undefined' ? 'undefined' : __WEBPACK_IMPORTED_MODULE_0_babel_runtime_helpers_typeof___default()(url)) === 'object' && url.replace === true) {
            $router.replace(url);
        } else {
            url === 'BACK' ? $router.go(-1) : $router.push(url);
        }
    } else {
        window.location.href = url;
    }
}

function getUrl(url, $router) {
    if ($router && !$router._history && typeof url === 'string' && !/http/.test(url)) {
        return '#!' + url;
    }
    return url && (typeof url === 'undefined' ? 'undefined' : __WEBPACK_IMPORTED_MODULE_0_babel_runtime_helpers_typeof___default()(url)) !== 'object' ? url : 'javascript:void(0);';
}

/***/ }),
/* 41 */,
/* 42 */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),
/* 43 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__mixins_multi_items__ = __webpack_require__(5);




/* unused harmony default export */ var _unused_webpack_default_export = ({
  mounted: function mounted() {
    var _this = this;

    if (false) {
      this.$nextTick(function () {
        var $el = _this.$el;
        var position = window.getComputedStyle($el).position;
        if (position === 'fixed') {
          return;
        } else if (position === 'absolute') {
          if (document.documentElement.offsetHeight !== window.innerHeight) {
            console.warn('[VUX warn] tabbar 定位默认为 absolute，如果你没有使用 100% 布局(view-box)，需要手动设置 style position 为 fixed');
          }
        }
      });
    }
  },

  name: 'tabbar',
  mixins: [__WEBPACK_IMPORTED_MODULE_0__mixins_multi_items__["b" /* parentMixin */]],
  props: {
    iconClass: String
  }
});

/***/ }),
/* 44 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__mixins_multi_items__ = __webpack_require__(5);




/* harmony default export */ __webpack_exports__["a"] = ({
  mounted: function mounted() {
    var _this = this;

    if (false) {
      this.$nextTick(function () {
        var $el = _this.$el;
        var position = window.getComputedStyle($el).position;
        if (position === 'fixed') {
          return;
        } else if (position === 'absolute') {
          if (document.documentElement.offsetHeight !== window.innerHeight) {
            console.warn('[VUX warn] tabbar 定位默认为 absolute，如果你没有使用 100% 布局(view-box)，需要手动设置 style position 为 fixed');
          }
        }
      });
    }
  },

  name: 'tabbar',
  mixins: [__WEBPACK_IMPORTED_MODULE_0__mixins_multi_items__["b" /* parentMixin */]],
  props: {
    iconClass: String
  }
});

/***/ }),
/* 45 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{staticClass:"weui-tabbar"},[_vm._t("default")],2)}
var staticRenderFns = []
var esExports = { render: render, staticRenderFns: staticRenderFns }
/* harmony default export */ __webpack_exports__["a"] = (esExports);

/***/ }),
/* 46 */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),
/* 47 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";


/* unused harmony default export */ var _unused_webpack_default_export = ({
  name: 'badge',
  props: {
    text: [String, Number]
  }
});

/***/ }),
/* 48 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";


/* harmony default export */ __webpack_exports__["a"] = ({
  name: 'badge',
  props: {
    text: [String, Number]
  }
});

/***/ }),
/* 49 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('span',{class:['vux-badge', {'vux-badge-dot': typeof _vm.text === 'undefined', 'vux-badge-single': typeof _vm.text !== 'undefined' && _vm.text.toString().length === 1}],domProps:{"textContent":_vm._s(_vm.text)}})}
var staticRenderFns = []
var esExports = { render: render, staticRenderFns: staticRenderFns }
/* harmony default export */ __webpack_exports__["a"] = (esExports);

/***/ }),
/* 50 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony default export */ __webpack_exports__["a"] = ({
  created: function created() {
    this.uuid = Math.random().toString(36).substring(3, 8);
  }
});

/***/ }),
/* 51 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_vux_loader_src_script_loader_js_vux_loader_src_script_loader_js_vue_loader_lib_selector_type_script_index_0_index_vue__ = __webpack_require__(156);
/* unused harmony namespace reexport */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__babel_loader_vux_loader_src_script_loader_js_vue_loader_lib_selector_type_script_index_0_index_vue__ = __webpack_require__(157);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__vue_loader_lib_template_compiler_index_id_data_v_613daa96_hasScoped_false_buble_transforms_vux_loader_src_before_template_compiler_loader_js_vux_loader_src_template_loader_js_vue_loader_lib_selector_type_template_index_0_index_vue__ = __webpack_require__(158);
function injectStyle (ssrContext) {
  __webpack_require__(155)
}
var normalizeComponent = __webpack_require__(0)
/* script */


/* template */

/* template functional */
var __vue_template_functional__ = false
/* styles */
var __vue_styles__ = injectStyle
/* scopeId */
var __vue_scopeId__ = null
/* moduleIdentifier (server only) */
var __vue_module_identifier__ = null
var Component = normalizeComponent(
  __WEBPACK_IMPORTED_MODULE_1__babel_loader_vux_loader_src_script_loader_js_vue_loader_lib_selector_type_script_index_0_index_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_2__vue_loader_lib_template_compiler_index_id_data_v_613daa96_hasScoped_false_buble_transforms_vux_loader_src_before_template_compiler_loader_js_vux_loader_src_template_loader_js_vue_loader_lib_selector_type_template_index_0_index_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)

/* harmony default export */ __webpack_exports__["a"] = (Component.exports);


/***/ }),
/* 52 */,
/* 53 */,
/* 54 */,
/* 55 */,
/* 56 */,
/* 57 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


function find(array, predicate, context) {
  if (typeof Array.prototype.find === 'function') {
    return array.find(predicate, context);
  }

  context = context || this;
  var length = array.length;
  var i;

  if (typeof predicate !== 'function') {
    throw new TypeError(predicate + ' is not a function');
  }

  for (i = 0; i < length; i++) {
    if (predicate.call(context, array[i], i, array)) {
      return array[i];
    }
  }
}

module.exports = find;


/***/ }),
/* 58 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__mixins_multi_items__ = __webpack_require__(5);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__badge__ = __webpack_require__(17);





/* unused harmony default export */ var _unused_webpack_default_export = ({
  name: 'tabbar-item',
  components: {
    Badge: __WEBPACK_IMPORTED_MODULE_1__badge__["a" /* default */]
  },
  mounted: function mounted() {
    if (!this.$slots.icon) {
      this.simple = true;
    }
    if (this.$slots['icon-active']) {
      this.hasActiveIcon = true;
    }
  },

  mixins: [__WEBPACK_IMPORTED_MODULE_0__mixins_multi_items__["a" /* childMixin */]],
  props: {
    showDot: {
      type: Boolean,
      default: false
    },
    badge: String,
    link: [String, Object],
    iconClass: String
  },
  computed: {
    isActive: function isActive() {
      return this.$parent.index === this.currentIndex;
    }
  },
  data: function data() {
    return {
      simple: false,
      hasActiveIcon: false
    };
  }
});

/***/ }),
/* 59 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__mixins_multi_items__ = __webpack_require__(5);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__badge__ = __webpack_require__(17);





/* harmony default export */ __webpack_exports__["a"] = ({
  name: 'tabbar-item',
  components: {
    Badge: __WEBPACK_IMPORTED_MODULE_1__badge__["a" /* default */]
  },
  mounted: function mounted() {
    if (!this.$slots.icon) {
      this.simple = true;
    }
    if (this.$slots['icon-active']) {
      this.hasActiveIcon = true;
    }
  },

  mixins: [__WEBPACK_IMPORTED_MODULE_0__mixins_multi_items__["a" /* childMixin */]],
  props: {
    showDot: {
      type: Boolean,
      default: false
    },
    badge: String,
    link: [String, Object],
    iconClass: String
  },
  computed: {
    isActive: function isActive() {
      return this.$parent.index === this.currentIndex;
    }
  },
  data: function data() {
    return {
      simple: false,
      hasActiveIcon: false
    };
  }
});

/***/ }),
/* 60 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('a',{staticClass:"weui-tabbar__item",class:{'weui-bar__item_on': _vm.isActive, 'vux-tabbar-simple': _vm.simple},attrs:{"href":"javascript:;"},on:{"click":function($event){_vm.onItemClick(true)}}},[(!_vm.simple)?_c('div',{staticClass:"weui-tabbar__icon",class:[_vm.iconClass || _vm.$parent.iconClass, {'vux-reddot': _vm.showDot}]},[(!_vm.simple && !(_vm.hasActiveIcon && _vm.isActive))?_vm._t("icon"):_vm._e(),_vm._v(" "),(!_vm.simple && _vm.hasActiveIcon && _vm.isActive)?_vm._t("icon-active"):_vm._e(),_vm._v(" "),(_vm.badge)?_c('sup',[_c('badge',{attrs:{"text":_vm.badge}})],1):_vm._e()],2):_vm._e(),_vm._v(" "),_c('p',{staticClass:"weui-tabbar__label"},[_vm._t("label")],2)])}
var staticRenderFns = []
var esExports = { render: render, staticRenderFns: staticRenderFns }
/* harmony default export */ __webpack_exports__["a"] = (esExports);

/***/ }),
/* 61 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_babel_runtime_helpers_classCallCheck__ = __webpack_require__(74);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_babel_runtime_helpers_classCallCheck___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0_babel_runtime_helpers_classCallCheck__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_babel_runtime_helpers_createClass__ = __webpack_require__(75);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_babel_runtime_helpers_createClass___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_1_babel_runtime_helpers_createClass__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2_object_assign__ = __webpack_require__(28);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2_object_assign___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_2_object_assign__);



var arrayFrom = function arrayFrom(nodeList) {
  return Array.prototype.slice.call(nodeList);
};

var Swiper = function () {
  function Swiper(options) {
    __WEBPACK_IMPORTED_MODULE_0_babel_runtime_helpers_classCallCheck___default()(this, Swiper);

    this._default = {
      container: '.vux-swiper',
      item: '.vux-swiper-item',
      direction: 'vertical',
      activeClass: 'active',
      threshold: 50,
      duration: 300,
      auto: false,
      loop: false,
      interval: 3000,
      height: 'auto',
      minMovingDistance: 0
    };
    this._options = __WEBPACK_IMPORTED_MODULE_2_object_assign___default()(this._default, options);
    this._options.height = this._options.height.replace('px', '');
    this._start = {};
    this._move = {};
    this._end = {};
    this._eventHandlers = {};
    this._prev = this._current = this._goto = 0;
    this._width = this._height = this._distance = 0;
    this._offset = [];
    this.$box = this._options.container;
    this.$container = this._options.container.querySelector('.vux-swiper');
    this.$items = this.$container.querySelectorAll(this._options.item);
    this.count = this.$items.length;
    this.realCount = this.$items.length;
    this._position = [];
    this._firstItemIndex = 0;
    this._isMoved = false;
    if (!this.count) {
      return;
    }
    this._init();
    this._auto();
    this._bind();
    this._onResize();
    return this;
  }

  __WEBPACK_IMPORTED_MODULE_1_babel_runtime_helpers_createClass___default()(Swiper, [{
    key: '_auto',
    value: function _auto() {
      var me = this;
      me.stop();
      if (me._options.auto) {
        me.timer = setTimeout(function () {
          me.next();
        }, me._options.interval);
      }
    }
  }, {
    key: 'updateItemWidth',
    value: function updateItemWidth() {
      this._width = this.$box.offsetWidth || document.documentElement.offsetWidth;
      this._distance = this._options.direction === 'horizontal' ? this._width : this._height;
    }
  }, {
    key: 'stop',
    value: function stop() {
      this.timer && clearTimeout(this.timer);
    }
  }, {
    key: '_loop',
    value: function _loop() {
      return this._options.loop && this.realCount >= 3;
    }
  }, {
    key: '_onResize',
    value: function _onResize() {
      var me = this;
      this.resizeHandler = function () {
        setTimeout(function () {
          me.updateItemWidth();
          me._setOffset();
          me._setTransform();
        }, 100);
      };
      window.addEventListener('orientationchange', this.resizeHandler, false);
    }
  }, {
    key: '_init',
    value: function _init() {
      this._height = this._options.height === 'auto' ? 'auto' : this._options.height - 0;
      this.updateItemWidth();
      this._initPosition();
      this._activate(this._current);
      this._setOffset();
      this._setTransform();
      if (this._loop()) {
        this._loopRender();
      }
    }
  }, {
    key: '_initPosition',
    value: function _initPosition() {
      for (var i = 0; i < this.realCount; i++) {
        this._position.push(i);
      }
    }
  }, {
    key: '_movePosition',
    value: function _movePosition(position) {
      var me = this;
      if (position > 0) {
        var firstIndex = me._position.splice(0, 1);
        me._position.push(firstIndex[0]);
      } else if (position < 0) {
        var lastIndex = me._position.pop();
        me._position.unshift(lastIndex);
      }
    }
  }, {
    key: '_setOffset',
    value: function _setOffset() {
      var me = this;
      var index = me._position.indexOf(me._current);
      me._offset = [];
      arrayFrom(me.$items).forEach(function ($item, key) {
        me._offset.push((key - index) * me._distance);
      });
    }
  }, {
    key: '_setTransition',
    value: function _setTransition(duration) {
      duration = duration || this._options.duration || 'none';
      var transition = duration === 'none' ? 'none' : duration + 'ms';
      arrayFrom(this.$items).forEach(function ($item, key) {
        $item.style.webkitTransition = transition;
        $item.style.transition = transition;
      });
    }
  }, {
    key: '_setTransform',
    value: function _setTransform(offset) {
      var me = this;
      offset = offset || 0;
      arrayFrom(me.$items).forEach(function ($item, key) {
        var distance = me._offset[key] + offset;
        var transform = 'translate3d(' + distance + 'px, 0, 0)';
        if (me._options.direction === 'vertical') {
          transform = 'translate3d(0, ' + distance + 'px, 0)';
        }
        $item.style.webkitTransform = transform;
        $item.style.transform = transform;
        me._isMoved = true;
      });
    }
  }, {
    key: '_bind',
    value: function _bind() {
      var _this = this;

      var me = this;
      me.touchstartHandler = function (e) {
        me.stop();
        me._start.x = e.changedTouches[0].pageX;
        me._start.y = e.changedTouches[0].pageY;
        me._setTransition('none');
        me._isMoved = false;
      };
      me.touchmoveHandler = function (e) {
        if (me.count === 1) {
          return;
        }
        me._move.x = e.changedTouches[0].pageX;
        me._move.y = e.changedTouches[0].pageY;
        var distanceX = me._move.x - me._start.x;
        var distanceY = me._move.y - me._start.y;
        var distance = distanceY;
        var noScrollerY = Math.abs(distanceX) > Math.abs(distanceY);
        if (me._options.direction === 'horizontal' && noScrollerY) {
          distance = distanceX;
        }

        if (!_this._options.loop && (_this._current === _this.count - 1 || _this._current === 0)) {
          distance = distance / 3;
        }
        if ((me._options.minMovingDistance && Math.abs(distance) >= me._options.minMovingDistance || !me._options.minMovingDistance) && noScrollerY || me._isMoved) {
          me._setTransform(distance);
        }

        noScrollerY && e.preventDefault();
      };

      me.touchendHandler = function (e) {
        if (me.count === 1) {
          return;
        }
        me._end.x = e.changedTouches[0].pageX;
        me._end.y = e.changedTouches[0].pageY;

        var distance = me._end.y - me._start.y;
        if (me._options.direction === 'horizontal') {
          distance = me._end.x - me._start.x;
        }

        distance = me.getDistance(distance);
        if (distance !== 0 && me._options.minMovingDistance && Math.abs(distance) < me._options.minMovingDistance && !me._isMoved) {
          return;
        }
        if (distance > me._options.threshold) {
          me.move(-1);
        } else if (distance < -me._options.threshold) {
          me.move(1);
        } else {
          me.move(0);
        }

        me._loopRender();
      };

      me.transitionEndHandler = function (e) {
        me._activate(me._current);
        var cb = me._eventHandlers.swiped;
        cb && cb.apply(me, [me._prev % me.count, me._current % me.count]);
        me._auto();
        me._loopRender();
        e.preventDefault();
      };
      me.$container.addEventListener('touchstart', me.touchstartHandler, false);
      me.$container.addEventListener('touchmove', me.touchmoveHandler, false);
      me.$container.addEventListener('touchend', me.touchendHandler, false);
      me.$items[1] && me.$items[1].addEventListener('webkitTransitionEnd', me.transitionEndHandler, false);
    }
  }, {
    key: '_loopRender',
    value: function _loopRender() {
      var me = this;
      if (me._loop()) {
        if (me._offset[me._offset.length - 1] === 0) {
          me.$container.appendChild(me.$items[0]);
          me._loopEvent(1);
        } else if (me._offset[0] === 0) {
          me.$container.insertBefore(me.$items[me.$items.length - 1], me.$container.firstChild);
          me._loopEvent(-1);
        }
      }
    }
  }, {
    key: '_loopEvent',
    value: function _loopEvent(num) {
      var me = this;
      me._itemDestoy();
      me.$items = me.$container.querySelectorAll(me._options.item);
      me.$items[1] && me.$items[1].addEventListener('webkitTransitionEnd', me.transitionEndHandler, false);
      me._movePosition(num);
      me._setOffset();
      me._setTransform();
    }
  }, {
    key: 'getDistance',
    value: function getDistance(distance) {
      if (this._loop()) {
        return distance;
      } else {
        if (distance > 0 && this._current === 0) {
          return 0;
        } else if (distance < 0 && this._current === this.realCount - 1) {
          return 0;
        } else {
          return distance;
        }
      }
    }
  }, {
    key: '_moveIndex',
    value: function _moveIndex(num) {
      if (num !== 0) {
        this._prev = this._current;
        this._current += this.realCount;
        this._current += num;
        this._current %= this.realCount;
      }
    }
  }, {
    key: '_activate',
    value: function _activate(index) {
      var clazz = this._options.activeClass;
      Array.prototype.forEach.call(this.$items, function ($item, key) {
        $item.classList.remove(clazz);
        if (index === Number($item.dataset.index)) {
          $item.classList.add(clazz);
        }
      });
    }
  }, {
    key: 'go',
    value: function go(index) {
      var me = this;
      me.stop();

      index = index || 0;
      index += this.realCount;
      index = index % this.realCount;
      index = this._position.indexOf(index) - this._position.indexOf(this._current);

      me._moveIndex(index);
      me._setOffset();
      me._setTransition();
      me._setTransform();
      me._auto();
      return this;
    }
  }, {
    key: 'next',
    value: function next() {
      this.move(1);
      return this;
    }
  }, {
    key: 'move',
    value: function move(num) {
      this.go(this._current + num);
      return this;
    }
  }, {
    key: 'on',
    value: function on(event, callback) {
      if (this._eventHandlers[event]) {
        console.error('[swiper] event ' + event + ' is already register');
      }
      if (typeof callback !== 'function') {
        console.error('[swiper] parameter callback must be a function');
      }
      this._eventHandlers[event] = callback;
      return this;
    }
  }, {
    key: '_itemDestoy',
    value: function _itemDestoy() {
      var _this2 = this;

      this.$items.length && arrayFrom(this.$items).forEach(function (item) {
        item.removeEventListener('webkitTransitionEnd', _this2.transitionEndHandler, false);
      });
    }
  }, {
    key: 'destroy',
    value: function destroy() {
      this.stop();
      this._current = 0;
      this._setTransform(0);
      window.removeEventListener('orientationchange', this.resizeHandler, false);
      this.$container.removeEventListener('touchstart', this.touchstartHandler, false);
      this.$container.removeEventListener('touchmove', this.touchmoveHandler, false);
      this.$container.removeEventListener('touchend', this.touchendHandler, false);
      this._itemDestoy();

      if (this._options.loop && this.count === 2) {
        var $item = this.$container.querySelector(this._options.item + '-clone');
        $item && this.$container.removeChild($item);
        $item = this.$container.querySelector(this._options.item + '-clone');
        $item && this.$container.removeChild($item);
      }
    }
  }]);

  return Swiper;
}();

/* harmony default export */ __webpack_exports__["a"] = (Swiper);

/***/ }),
/* 62 */,
/* 63 */,
/* 64 */,
/* 65 */,
/* 66 */,
/* 67 */,
/* 68 */,
/* 69 */,
/* 70 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";


/* unused harmony default export */ var _unused_webpack_default_export = ({
  name: 'box',
  props: {
    gap: String
  }
});

/***/ }),
/* 71 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";


/* harmony default export */ __webpack_exports__["a"] = ({
  name: 'box',
  props: {
    gap: String
  }
});

/***/ }),
/* 72 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{style:({margin:_vm.gap})},[_vm._t("default")],2)}
var staticRenderFns = []
var esExports = { render: render, staticRenderFns: staticRenderFns }
/* harmony default export */ __webpack_exports__["a"] = (esExports);

/***/ }),
/* 73 */,
/* 74 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


exports.__esModule = true;

exports.default = function (instance, Constructor) {
  if (!(instance instanceof Constructor)) {
    throw new TypeError("Cannot call a class as a function");
  }
};

/***/ }),
/* 75 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


exports.__esModule = true;

var _defineProperty = __webpack_require__(81);

var _defineProperty2 = _interopRequireDefault(_defineProperty);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

exports.default = function () {
  function defineProperties(target, props) {
    for (var i = 0; i < props.length; i++) {
      var descriptor = props[i];
      descriptor.enumerable = descriptor.enumerable || false;
      descriptor.configurable = true;
      if ("value" in descriptor) descriptor.writable = true;
      (0, _defineProperty2.default)(target, descriptor.key, descriptor);
    }
  }

  return function (Constructor, protoProps, staticProps) {
    if (protoProps) defineProperties(Constructor.prototype, protoProps);
    if (staticProps) defineProperties(Constructor, staticProps);
    return Constructor;
  };
}();

/***/ }),
/* 76 */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),
/* 77 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__inline_desc__ = __webpack_require__(11);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__libs_router__ = __webpack_require__(10);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__props__ = __webpack_require__(36);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_3__libs_clean_style__ = __webpack_require__(33);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_4__libs_get_parent_prop__ = __webpack_require__(37);








/* unused harmony default export */ var _unused_webpack_default_export = ({
  name: 'cell',
  components: {
    InlineDesc: __WEBPACK_IMPORTED_MODULE_0__inline_desc__["a" /* default */]
  },
  props: Object(__WEBPACK_IMPORTED_MODULE_2__props__["a" /* default */])(),
  created: function created() {
    if (false) {
      console.warn('[VUX] 抱歉，当前组件[cell]要求更新依赖 vux-loader@latest');
    }
  },
  beforeMount: function beforeMount() {
    this.hasTitleSlot = !!this.$slots.title;

    if (this.$slots.value && "production" === 'development') {
      console.warn('[VUX] [cell] slot=value 已经废弃，请使用默认 slot 替代');
    }
  },

  computed: {
    labelStyles: function labelStyles() {
      return Object(__WEBPACK_IMPORTED_MODULE_3__libs_clean_style__["a" /* default */])({
        width: Object(__WEBPACK_IMPORTED_MODULE_4__libs_get_parent_prop__["a" /* default */])(this, 'labelWidth'),
        textAlign: Object(__WEBPACK_IMPORTED_MODULE_4__libs_get_parent_prop__["a" /* default */])(this, 'labelAlign'),
        marginRight: Object(__WEBPACK_IMPORTED_MODULE_4__libs_get_parent_prop__["a" /* default */])(this, 'labelMarginRight')
      });
    },
    valueClass: function valueClass() {
      return {
        'vux-cell-primary': this.primary === 'content' || this.valueAlign === 'left',
        'vux-cell-align-left': this.valueAlign === 'left',
        'vux-cell-arrow-transition': !!this.arrowDirection,
        'vux-cell-arrow-up': this.arrowDirection === 'up',
        'vux-cell-arrow-down': this.arrowDirection === 'down'
      };
    },
    labelClass: function labelClass() {
      return {
        'vux-cell-justify': this.$parent.labelAlign === 'justify' || this.$parent.$parent.labelAlign === 'justify'
      };
    },
    style: function style() {
      if (this.alignItems) {
        return {
          alignItems: this.alignItems
        };
      }
    }
  },
  methods: {
    onClick: function onClick() {
      !this.disabled && Object(__WEBPACK_IMPORTED_MODULE_1__libs_router__["b" /* go */])(this.link, this.$router);
    }
  },
  data: function data() {
    return {
      hasTitleSlot: true,
      hasMounted: false
    };
  }
});

/***/ }),
/* 78 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__inline_desc__ = __webpack_require__(11);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__libs_router__ = __webpack_require__(10);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__props__ = __webpack_require__(36);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_3__libs_clean_style__ = __webpack_require__(33);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_4__libs_get_parent_prop__ = __webpack_require__(37);








/* harmony default export */ __webpack_exports__["a"] = ({
  name: 'cell',
  components: {
    InlineDesc: __WEBPACK_IMPORTED_MODULE_0__inline_desc__["a" /* default */]
  },
  props: Object(__WEBPACK_IMPORTED_MODULE_2__props__["a" /* default */])(),
  created: function created() {
    if (false) {
      console.warn('[VUX] 抱歉，当前组件[cell]要求更新依赖 vux-loader@latest');
    }
  },
  beforeMount: function beforeMount() {
    this.hasTitleSlot = !!this.$slots.title;

    if (this.$slots.value && "production" === 'development') {
      console.warn('[VUX] [cell] slot=value 已经废弃，请使用默认 slot 替代');
    }
  },

  computed: {
    labelStyles: function labelStyles() {
      return Object(__WEBPACK_IMPORTED_MODULE_3__libs_clean_style__["a" /* default */])({
        width: Object(__WEBPACK_IMPORTED_MODULE_4__libs_get_parent_prop__["a" /* default */])(this, 'labelWidth'),
        textAlign: Object(__WEBPACK_IMPORTED_MODULE_4__libs_get_parent_prop__["a" /* default */])(this, 'labelAlign'),
        marginRight: Object(__WEBPACK_IMPORTED_MODULE_4__libs_get_parent_prop__["a" /* default */])(this, 'labelMarginRight')
      });
    },
    valueClass: function valueClass() {
      return {
        'vux-cell-primary': this.primary === 'content' || this.valueAlign === 'left',
        'vux-cell-align-left': this.valueAlign === 'left',
        'vux-cell-arrow-transition': !!this.arrowDirection,
        'vux-cell-arrow-up': this.arrowDirection === 'up',
        'vux-cell-arrow-down': this.arrowDirection === 'down'
      };
    },
    labelClass: function labelClass() {
      return {
        'vux-cell-justify': this.$parent.labelAlign === 'justify' || this.$parent.$parent.labelAlign === 'justify'
      };
    },
    style: function style() {
      if (this.alignItems) {
        return {
          alignItems: this.alignItems
        };
      }
    }
  },
  methods: {
    onClick: function onClick() {
      !this.disabled && Object(__WEBPACK_IMPORTED_MODULE_1__libs_router__["b" /* go */])(this.link, this.$router);
    }
  },
  data: function data() {
    return {
      hasTitleSlot: true,
      hasMounted: false
    };
  }
});

/***/ }),
/* 79 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{staticClass:"weui-cell",class:{
    'vux-tap-active': _vm.isLink || !!_vm.link,
    'weui-cell_access': _vm.isLink || !!_vm.link,
    'vux-cell-no-border-intent': !_vm.borderIntent,
    'vux-cell-disabled': _vm.disabled
  },style:(_vm.style),on:{"click":_vm.onClick}},[_c('div',{staticClass:"weui-cell__hd"},[_vm._t("icon")],2),_vm._v(" "),_c('div',{staticClass:"vux-cell-bd",class:{'vux-cell-primary': _vm.primary === 'title' && _vm.valueAlign !== 'left'}},[_c('p',[(_vm.title || _vm.hasTitleSlot)?_c('label',{staticClass:"vux-label",class:_vm.labelClass,style:(_vm.labelStyles)},[_vm._t("title",[_vm._v(_vm._s(_vm.title))])],2):_vm._e(),_vm._v(" "),_vm._t("after-title")],2),_vm._v(" "),_c('inline-desc',[_vm._t("inline-desc",[_vm._v(_vm._s(_vm.inlineDesc))])],2)],1),_vm._v(" "),_c('div',{staticClass:"weui-cell__ft",class:_vm.valueClass},[_vm._t("value"),_vm._v(" "),_vm._t("default",[_vm._v(_vm._s(_vm.value))]),_vm._v(" "),(_vm.isLoading)?_c('i',{staticClass:"weui-loading"}):_vm._e()],2),_vm._v(" "),_vm._t("child")],2)}
var staticRenderFns = []
var esExports = { render: render, staticRenderFns: staticRenderFns }
/* harmony default export */ __webpack_exports__["a"] = (esExports);

/***/ }),
/* 80 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vux_loader_src_script_loader_js_node_modules_vux_loader_src_script_loader_js_node_modules_vue_loader_lib_selector_type_script_index_0_ln_filter_bar_pop_vue__ = __webpack_require__(313);
/* unused harmony namespace reexport */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__babel_loader_node_modules_vux_loader_src_script_loader_js_node_modules_vue_loader_lib_selector_type_script_index_0_ln_filter_bar_pop_vue__ = __webpack_require__(314);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__node_modules_vue_loader_lib_template_compiler_index_id_data_v_3c99f5b8_hasScoped_false_buble_transforms_node_modules_vux_loader_src_before_template_compiler_loader_js_node_modules_vux_loader_src_template_loader_js_node_modules_vue_loader_lib_selector_type_template_index_0_ln_filter_bar_pop_vue__ = __webpack_require__(315);
function injectStyle (ssrContext) {
  __webpack_require__(312)
}
var normalizeComponent = __webpack_require__(0)
/* script */


/* template */

/* template functional */
var __vue_template_functional__ = false
/* styles */
var __vue_styles__ = injectStyle
/* scopeId */
var __vue_scopeId__ = null
/* moduleIdentifier (server only) */
var __vue_module_identifier__ = null
var Component = normalizeComponent(
  __WEBPACK_IMPORTED_MODULE_1__babel_loader_node_modules_vux_loader_src_script_loader_js_node_modules_vue_loader_lib_selector_type_script_index_0_ln_filter_bar_pop_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_2__node_modules_vue_loader_lib_template_compiler_index_id_data_v_3c99f5b8_hasScoped_false_buble_transforms_node_modules_vux_loader_src_before_template_compiler_loader_js_node_modules_vux_loader_src_template_loader_js_node_modules_vue_loader_lib_selector_type_template_index_0_ln_filter_bar_pop_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)

/* harmony default export */ __webpack_exports__["a"] = (Component.exports);


/***/ }),
/* 81 */
/***/ (function(module, exports, __webpack_require__) {

module.exports = { "default": __webpack_require__(87), __esModule: true };

/***/ }),
/* 82 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_vux_loader_src_script_loader_js_vux_loader_src_script_loader_js_vue_loader_lib_selector_type_script_index_0_index_vue__ = __webpack_require__(152);
/* unused harmony namespace reexport */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__babel_loader_vux_loader_src_script_loader_js_vue_loader_lib_selector_type_script_index_0_index_vue__ = __webpack_require__(153);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__vue_loader_lib_template_compiler_index_id_data_v_76aee302_hasScoped_false_buble_transforms_vux_loader_src_before_template_compiler_loader_js_vux_loader_src_template_loader_js_vue_loader_lib_selector_type_template_index_0_index_vue__ = __webpack_require__(154);
function injectStyle (ssrContext) {
  __webpack_require__(150)
  __webpack_require__(151)
}
var normalizeComponent = __webpack_require__(0)
/* script */


/* template */

/* template functional */
var __vue_template_functional__ = false
/* styles */
var __vue_styles__ = injectStyle
/* scopeId */
var __vue_scopeId__ = null
/* moduleIdentifier (server only) */
var __vue_module_identifier__ = null
var Component = normalizeComponent(
  __WEBPACK_IMPORTED_MODULE_1__babel_loader_vux_loader_src_script_loader_js_vue_loader_lib_selector_type_script_index_0_index_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_2__vue_loader_lib_template_compiler_index_id_data_v_76aee302_hasScoped_false_buble_transforms_vux_loader_src_before_template_compiler_loader_js_vux_loader_src_template_loader_js_vue_loader_lib_selector_type_template_index_0_index_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)

/* harmony default export */ __webpack_exports__["a"] = (Component.exports);


/***/ }),
/* 83 */
/***/ (function(module, exports, __webpack_require__) {

var __WEBPACK_AMD_DEFINE_FACTORY__, __WEBPACK_AMD_DEFINE_RESULT__;/*! PhotoSwipe - v4.1.3-rc.1 - 2017-09-23
* http://photoswipe.com
* Copyright (c) 2017 Dmitry Semenov; */
(function (root, factory) { 
	if (true) {
		!(__WEBPACK_AMD_DEFINE_FACTORY__ = (factory),
				__WEBPACK_AMD_DEFINE_RESULT__ = (typeof __WEBPACK_AMD_DEFINE_FACTORY__ === 'function' ?
				(__WEBPACK_AMD_DEFINE_FACTORY__.call(exports, __webpack_require__, exports, module)) :
				__WEBPACK_AMD_DEFINE_FACTORY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));
	} else if (typeof exports === 'object') {
		module.exports = factory();
	} else {
		root.PhotoSwipe = factory();
	}
})(this, function () {

	'use strict';
	var PhotoSwipe = function(template, UiClass, items, options){

/*>>framework-bridge*/
/**
 *
 * Set of generic functions used by gallery.
 * 
 * You're free to modify anything here as long as functionality is kept.
 * 
 */
var framework = {
	features: null,
	bind: function(target, type, listener, unbind) {
		var methodName = (unbind ? 'remove' : 'add') + 'EventListener';
		type = type.split(' ');
		for(var i = 0; i < type.length; i++) {
			if(type[i]) {
				target[methodName]( type[i], listener, false);
			}
		}
	},
	isArray: function(obj) {
		return (obj instanceof Array);
	},
	createEl: function(classes, tag) {
		var el = document.createElement(tag || 'div');
		if(classes) {
			el.className = classes;
		}
		return el;
	},
	getScrollY: function() {
		var yOffset = window.pageYOffset;
		return yOffset !== undefined ? yOffset : document.documentElement.scrollTop;
	},
	unbind: function(target, type, listener) {
		framework.bind(target,type,listener,true);
	},
	removeClass: function(el, className) {
		var reg = new RegExp('(\\s|^)' + className + '(\\s|$)');
		el.className = el.className.replace(reg, ' ').replace(/^\s\s*/, '').replace(/\s\s*$/, ''); 
	},
	addClass: function(el, className) {
		if( !framework.hasClass(el,className) ) {
			el.className += (el.className ? ' ' : '') + className;
		}
	},
	hasClass: function(el, className) {
		return el.className && new RegExp('(^|\\s)' + className + '(\\s|$)').test(el.className);
	},
	getChildByClass: function(parentEl, childClassName) {
		var node = parentEl.firstChild;
		while(node) {
			if( framework.hasClass(node, childClassName) ) {
				return node;
			}
			node = node.nextSibling;
		}
	},
	arraySearch: function(array, value, key) {
		var i = array.length;
		while(i--) {
			if(array[i][key] === value) {
				return i;
			} 
		}
		return -1;
	},
	extend: function(o1, o2, preventOverwrite) {
		for (var prop in o2) {
			if (o2.hasOwnProperty(prop)) {
				if(preventOverwrite && o1.hasOwnProperty(prop)) {
					continue;
				}
				o1[prop] = o2[prop];
			}
		}
	},
	easing: {
		sine: {
			out: function(k) {
				return Math.sin(k * (Math.PI / 2));
			},
			inOut: function(k) {
				return - (Math.cos(Math.PI * k) - 1) / 2;
			}
		},
		cubic: {
			out: function(k) {
				return --k * k * k + 1;
			}
		}
		/*
			elastic: {
				out: function ( k ) {

					var s, a = 0.1, p = 0.4;
					if ( k === 0 ) return 0;
					if ( k === 1 ) return 1;
					if ( !a || a < 1 ) { a = 1; s = p / 4; }
					else s = p * Math.asin( 1 / a ) / ( 2 * Math.PI );
					return ( a * Math.pow( 2, - 10 * k) * Math.sin( ( k - s ) * ( 2 * Math.PI ) / p ) + 1 );

				},
			},
			back: {
				out: function ( k ) {
					var s = 1.70158;
					return --k * k * ( ( s + 1 ) * k + s ) + 1;
				}
			}
		*/
	},

	/**
	 * 
	 * @return {object}
	 * 
	 * {
	 *  raf : request animation frame function
	 *  caf : cancel animation frame function
	 *  transfrom : transform property key (with vendor), or null if not supported
	 *  oldIE : IE8 or below
	 * }
	 * 
	 */
	detectFeatures: function() {
		if(framework.features) {
			return framework.features;
		}
		var helperEl = framework.createEl(),
			helperStyle = helperEl.style,
			vendor = '',
			features = {};

		// IE8 and below
		features.oldIE = document.all && !document.addEventListener;

		features.touch = 'ontouchstart' in window;

		if(window.requestAnimationFrame) {
			features.raf = window.requestAnimationFrame;
			features.caf = window.cancelAnimationFrame;
		}

		features.pointerEvent = navigator.pointerEnabled || navigator.msPointerEnabled;

		// fix false-positive detection of old Android in new IE
		// (IE11 ua string contains "Android 4.0")
		
		if(!features.pointerEvent) { 

			var ua = navigator.userAgent;

			// Detect if device is iPhone or iPod and if it's older than iOS 8
			// http://stackoverflow.com/a/14223920
			// 
			// This detection is made because of buggy top/bottom toolbars
			// that don't trigger window.resize event.
			// For more info refer to _isFixedPosition variable in core.js

			if (/iP(hone|od)/.test(navigator.platform)) {
				var v = (navigator.appVersion).match(/OS (\d+)_(\d+)_?(\d+)?/);
				if(v && v.length > 0) {
					v = parseInt(v[1], 10);
					if(v >= 1 && v < 8 ) {
						features.isOldIOSPhone = true;
					}
				}
			}

			// Detect old Android (before KitKat)
			// due to bugs related to position:fixed
			// http://stackoverflow.com/questions/7184573/pick-up-the-android-version-in-the-browser-by-javascript
			
			var match = ua.match(/Android\s([0-9\.]*)/);
			var androidversion =  match ? match[1] : 0;
			androidversion = parseFloat(androidversion);
			if(androidversion >= 1 ) {
				if(androidversion < 4.4) {
					features.isOldAndroid = true; // for fixed position bug & performance
				}
				features.androidVersion = androidversion; // for touchend bug
			}	
			features.isMobileOpera = /opera mini|opera mobi/i.test(ua);

			// p.s. yes, yes, UA sniffing is bad, propose your solution for above bugs.
		}
		
		var styleChecks = ['transform', 'perspective', 'animationName'],
			vendors = ['', 'webkit','Moz','ms','O'],
			styleCheckItem,
			styleName;

		for(var i = 0; i < 4; i++) {
			vendor = vendors[i];

			for(var a = 0; a < 3; a++) {
				styleCheckItem = styleChecks[a];

				// uppercase first letter of property name, if vendor is present
				styleName = vendor + (vendor ? 
										styleCheckItem.charAt(0).toUpperCase() + styleCheckItem.slice(1) : 
										styleCheckItem);
			
				if(!features[styleCheckItem] && styleName in helperStyle ) {
					features[styleCheckItem] = styleName;
				}
			}

			if(vendor && !features.raf) {
				vendor = vendor.toLowerCase();
				features.raf = window[vendor+'RequestAnimationFrame'];
				if(features.raf) {
					features.caf = window[vendor+'CancelAnimationFrame'] || 
									window[vendor+'CancelRequestAnimationFrame'];
				}
			}
		}
			
		if(!features.raf) {
			var lastTime = 0;
			features.raf = function(fn) {
				var currTime = new Date().getTime();
				var timeToCall = Math.max(0, 16 - (currTime - lastTime));
				var id = window.setTimeout(function() { fn(currTime + timeToCall); }, timeToCall);
				lastTime = currTime + timeToCall;
				return id;
			};
			features.caf = function(id) { clearTimeout(id); };
		}

		// Detect SVG support
		features.svg = !!document.createElementNS && 
						!!document.createElementNS('http://www.w3.org/2000/svg', 'svg').createSVGRect;

		framework.features = features;

		return features;
	}
};

framework.detectFeatures();

// Override addEventListener for old versions of IE
if(framework.features.oldIE) {

	framework.bind = function(target, type, listener, unbind) {
		
		type = type.split(' ');

		var methodName = (unbind ? 'detach' : 'attach') + 'Event',
			evName,
			_handleEv = function() {
				listener.handleEvent.call(listener);
			};

		for(var i = 0; i < type.length; i++) {
			evName = type[i];
			if(evName) {

				if(typeof listener === 'object' && listener.handleEvent) {
					if(!unbind) {
						listener['oldIE' + evName] = _handleEv;
					} else {
						if(!listener['oldIE' + evName]) {
							return false;
						}
					}

					target[methodName]( 'on' + evName, listener['oldIE' + evName]);
				} else {
					target[methodName]( 'on' + evName, listener);
				}

			}
		}
	};
	
}

/*>>framework-bridge*/

/*>>core*/
//function(template, UiClass, items, options)

var self = this;

/**
 * Static vars, don't change unless you know what you're doing.
 */
var DOUBLE_TAP_RADIUS = 25, 
	NUM_HOLDERS = 3;

/**
 * Options
 */
var _options = {
	allowPanToNext:true,
	spacing: 0.12,
	bgOpacity: 1,
	mouseUsed: false,
	loop: true,
	pinchToClose: true,
	closeOnScroll: true,
	closeOnVerticalDrag: true,
	verticalDragRange: 0.75,
	hideAnimationDuration: 333,
	showAnimationDuration: 333,
	showHideOpacity: false,
	focus: true,
	escKey: true,
	arrowKeys: true,
	mainScrollEndFriction: 0.35,
	panEndFriction: 0.35,
	isClickableElement: function(el) {
        return el.tagName === 'A';
    },
    getDoubleTapZoom: function(isMouseClick, item) {
    	if(isMouseClick) {
    		return 1;
    	} else {
    		return item.initialZoomLevel < 0.7 ? 1 : 1.33;
    	}
    },
    maxSpreadZoom: 1.33,
	modal: true,

	// not fully implemented yet
	scaleMode: 'fit' // TODO
};
framework.extend(_options, options);


/**
 * Private helper variables & functions
 */

var _getEmptyPoint = function() { 
		return {x:0,y:0}; 
	};

var _isOpen,
	_isDestroying,
	_closedByScroll,
	_currentItemIndex,
	_containerStyle,
	_containerShiftIndex,
	_currPanDist = _getEmptyPoint(),
	_startPanOffset = _getEmptyPoint(),
	_panOffset = _getEmptyPoint(),
	_upMoveEvents, // drag move, drag end & drag cancel events array
	_downEvents, // drag start events array
	_globalEventHandlers,
	_viewportSize = {},
	_currZoomLevel,
	_startZoomLevel,
	_translatePrefix,
	_translateSufix,
	_updateSizeInterval,
	_itemsNeedUpdate,
	_currPositionIndex = 0,
	_offset = {},
	_slideSize = _getEmptyPoint(), // size of slide area, including spacing
	_itemHolders,
	_prevItemIndex,
	_indexDiff = 0, // difference of indexes since last content update
	_dragStartEvent,
	_dragMoveEvent,
	_dragEndEvent,
	_dragCancelEvent,
	_transformKey,
	_pointerEventEnabled,
	_isFixedPosition = true,
	_likelyTouchDevice,
	_modules = [],
	_requestAF,
	_cancelAF,
	_initalClassName,
	_initalWindowScrollY,
	_oldIE,
	_currentWindowScrollY,
	_features,
	_windowVisibleSize = {},
	_renderMaxResolution = false,
	_orientationChangeTimeout,


	// Registers PhotoSWipe module (History, Controller ...)
	_registerModule = function(name, module) {
		framework.extend(self, module.publicMethods);
		_modules.push(name);
	},

	_getLoopedId = function(index) {
		var numSlides = _getNumItems();
		if(index > numSlides - 1) {
			return index - numSlides;
		} else  if(index < 0) {
			return numSlides + index;
		}
		return index;
	},
	
	// Micro bind/trigger
	_listeners = {},
	_listen = function(name, fn) {
		if(!_listeners[name]) {
			_listeners[name] = [];
		}
		return _listeners[name].push(fn);
	},
	_shout = function(name) {
		var listeners = _listeners[name];

		if(listeners) {
			var args = Array.prototype.slice.call(arguments);
			args.shift();

			for(var i = 0; i < listeners.length; i++) {
				listeners[i].apply(self, args);
			}
		}
	},

	_getCurrentTime = function() {
		return new Date().getTime();
	},
	_applyBgOpacity = function(opacity) {
		_bgOpacity = opacity;
		self.bg.style.opacity = opacity * _options.bgOpacity;
	},

	_applyZoomTransform = function(styleObj,x,y,zoom,item) {
		if(!_renderMaxResolution || (item && item !== self.currItem) ) {
			zoom = zoom / (item ? item.fitRatio : self.currItem.fitRatio);	
		}
			
		styleObj[_transformKey] = _translatePrefix + x + 'px, ' + y + 'px' + _translateSufix + ' scale(' + zoom + ')';
	},
	_applyCurrentZoomPan = function( allowRenderResolution ) {
		if(_currZoomElementStyle) {

			if(allowRenderResolution) {
				if(_currZoomLevel > self.currItem.fitRatio) {
					if(!_renderMaxResolution) {
						_setImageSize(self.currItem, false, true);
						_renderMaxResolution = true;
					}
				} else {
					if(_renderMaxResolution) {
						_setImageSize(self.currItem);
						_renderMaxResolution = false;
					}
				}
			}
			

			_applyZoomTransform(_currZoomElementStyle, _panOffset.x, _panOffset.y, _currZoomLevel);
		}
	},
	_applyZoomPanToItem = function(item) {
		if(item.container) {

			_applyZoomTransform(item.container.style, 
								item.initialPosition.x, 
								item.initialPosition.y, 
								item.initialZoomLevel,
								item);
		}
	},
	_setTranslateX = function(x, elStyle) {
		elStyle[_transformKey] = _translatePrefix + x + 'px, 0px' + _translateSufix;
	},
	_moveMainScroll = function(x, dragging) {

		if(!_options.loop && dragging) {
			var newSlideIndexOffset = _currentItemIndex + (_slideSize.x * _currPositionIndex - x) / _slideSize.x,
				delta = Math.round(x - _mainScrollPos.x);

			if( (newSlideIndexOffset < 0 && delta > 0) || 
				(newSlideIndexOffset >= _getNumItems() - 1 && delta < 0) ) {
				x = _mainScrollPos.x + delta * _options.mainScrollEndFriction;
			} 
		}
		
		_mainScrollPos.x = x;
		_setTranslateX(x, _containerStyle);
	},
	_calculatePanOffset = function(axis, zoomLevel) {
		var m = _midZoomPoint[axis] - _offset[axis];
		return _startPanOffset[axis] + _currPanDist[axis] + m - m * ( zoomLevel / _startZoomLevel );
	},
	
	_equalizePoints = function(p1, p2) {
		p1.x = p2.x;
		p1.y = p2.y;
		if(p2.id) {
			p1.id = p2.id;
		}
	},
	_roundPoint = function(p) {
		p.x = Math.round(p.x);
		p.y = Math.round(p.y);
	},

	_mouseMoveTimeout = null,
	_onFirstMouseMove = function() {
		// Wait until mouse move event is fired at least twice during 100ms
		// We do this, because some mobile browsers trigger it on touchstart
		if(_mouseMoveTimeout ) { 
			framework.unbind(document, 'mousemove', _onFirstMouseMove);
			framework.addClass(template, 'pswp--has_mouse');
			_options.mouseUsed = true;
			_shout('mouseUsed');
		}
		_mouseMoveTimeout = setTimeout(function() {
			_mouseMoveTimeout = null;
		}, 100);
	},

	_bindEvents = function() {
		framework.bind(document, 'keydown', self);

		if(_features.transform) {
			// don't bind click event in browsers that don't support transform (mostly IE8)
			framework.bind(self.scrollWrap, 'click', self);
		}
		

		if(!_options.mouseUsed) {
			framework.bind(document, 'mousemove', _onFirstMouseMove);
		}

		framework.bind(window, 'resize scroll orientationchange', self);

		_shout('bindEvents');
	},

	_unbindEvents = function() {
		framework.unbind(window, 'resize scroll orientationchange', self);
		framework.unbind(window, 'scroll', _globalEventHandlers.scroll);
		framework.unbind(document, 'keydown', self);
		framework.unbind(document, 'mousemove', _onFirstMouseMove);

		if(_features.transform) {
			framework.unbind(self.scrollWrap, 'click', self);
		}

		if(_isDragging) {
			framework.unbind(window, _upMoveEvents, self);
		}

		clearTimeout(_orientationChangeTimeout);

		_shout('unbindEvents');
	},
	
	_calculatePanBounds = function(zoomLevel, update) {
		var bounds = _calculateItemSize( self.currItem, _viewportSize, zoomLevel );
		if(update) {
			_currPanBounds = bounds;
		}
		return bounds;
	},
	
	_getMinZoomLevel = function(item) {
		if(!item) {
			item = self.currItem;
		}
		return item.initialZoomLevel;
	},
	_getMaxZoomLevel = function(item) {
		if(!item) {
			item = self.currItem;
		}
		return item.w > 0 ? _options.maxSpreadZoom : 1;
	},

	// Return true if offset is out of the bounds
	_modifyDestPanOffset = function(axis, destPanBounds, destPanOffset, destZoomLevel) {
		if(destZoomLevel === self.currItem.initialZoomLevel) {
			destPanOffset[axis] = self.currItem.initialPosition[axis];
			return true;
		} else {
			destPanOffset[axis] = _calculatePanOffset(axis, destZoomLevel); 

			if(destPanOffset[axis] > destPanBounds.min[axis]) {
				destPanOffset[axis] = destPanBounds.min[axis];
				return true;
			} else if(destPanOffset[axis] < destPanBounds.max[axis] ) {
				destPanOffset[axis] = destPanBounds.max[axis];
				return true;
			}
		}
		return false;
	},

	_setupTransforms = function() {

		if(_transformKey) {
			// setup 3d transforms
			var allow3dTransform = _features.perspective && !_likelyTouchDevice;
			_translatePrefix = 'translate' + (allow3dTransform ? '3d(' : '(');
			_translateSufix = _features.perspective ? ', 0px)' : ')';	
			return;
		}

		// Override zoom/pan/move functions in case old browser is used (most likely IE)
		// (so they use left/top/width/height, instead of CSS transform)
	
		_transformKey = 'left';
		framework.addClass(template, 'pswp--ie');

		_setTranslateX = function(x, elStyle) {
			elStyle.left = x + 'px';
		};
		_applyZoomPanToItem = function(item) {

			var zoomRatio = item.fitRatio > 1 ? 1 : item.fitRatio,
				s = item.container.style,
				w = zoomRatio * item.w,
				h = zoomRatio * item.h;

			s.width = w + 'px';
			s.height = h + 'px';
			s.left = item.initialPosition.x + 'px';
			s.top = item.initialPosition.y + 'px';

		};
		_applyCurrentZoomPan = function() {
			if(_currZoomElementStyle) {

				var s = _currZoomElementStyle,
					item = self.currItem,
					zoomRatio = item.fitRatio > 1 ? 1 : item.fitRatio,
					w = zoomRatio * item.w,
					h = zoomRatio * item.h;

				s.width = w + 'px';
				s.height = h + 'px';


				s.left = _panOffset.x + 'px';
				s.top = _panOffset.y + 'px';
			}
			
		};
	},

	_onKeyDown = function(e) {
		var keydownAction = '';
		if(_options.escKey && e.keyCode === 27) { 
			keydownAction = 'close';
		} else if(_options.arrowKeys) {
			if(e.keyCode === 37) {
				keydownAction = 'prev';
			} else if(e.keyCode === 39) { 
				keydownAction = 'next';
			}
		}

		if(keydownAction) {
			// don't do anything if special key pressed to prevent from overriding default browser actions
			// e.g. in Chrome on Mac cmd+arrow-left returns to previous page
			if( !e.ctrlKey && !e.altKey && !e.shiftKey && !e.metaKey ) {
				if(e.preventDefault) {
					e.preventDefault();
				} else {
					e.returnValue = false;
				} 
				self[keydownAction]();
			}
		}
	},

	_onGlobalClick = function(e) {
		if(!e) {
			return;
		}

		// don't allow click event to pass through when triggering after drag or some other gesture
		if(_moved || _zoomStarted || _mainScrollAnimating || _verticalDragInitiated) {
			e.preventDefault();
			e.stopPropagation();
		}
	},

	_updatePageScrollOffset = function() {
		self.setScrollOffset(0, framework.getScrollY());		
	};
	


	



// Micro animation engine
var _animations = {},
	_numAnimations = 0,
	_stopAnimation = function(name) {
		if(_animations[name]) {
			if(_animations[name].raf) {
				_cancelAF( _animations[name].raf );
			}
			_numAnimations--;
			delete _animations[name];
		}
	},
	_registerStartAnimation = function(name) {
		if(_animations[name]) {
			_stopAnimation(name);
		}
		if(!_animations[name]) {
			_numAnimations++;
			_animations[name] = {};
		}
	},
	_stopAllAnimations = function() {
		for (var prop in _animations) {

			if( _animations.hasOwnProperty( prop ) ) {
				_stopAnimation(prop);
			} 
			
		}
	},
	_animateProp = function(name, b, endProp, d, easingFn, onUpdate, onComplete) {
		var startAnimTime = _getCurrentTime(), t;
		_registerStartAnimation(name);

		var animloop = function(){
			if ( _animations[name] ) {
				
				t = _getCurrentTime() - startAnimTime; // time diff
				//b - beginning (start prop)
				//d - anim duration

				if ( t >= d ) {
					_stopAnimation(name);
					onUpdate(endProp);
					if(onComplete) {
						onComplete();
					}
					return;
				}
				onUpdate( (endProp - b) * easingFn(t/d) + b );

				_animations[name].raf = _requestAF(animloop);
			}
		};
		animloop();
	};
	


var publicMethods = {

	// make a few local variables and functions public
	shout: _shout,
	listen: _listen,
	viewportSize: _viewportSize,
	options: _options,

	isMainScrollAnimating: function() {
		return _mainScrollAnimating;
	},
	getZoomLevel: function() {
		return _currZoomLevel;
	},
	getCurrentIndex: function() {
		return _currentItemIndex;
	},
	isDragging: function() {
		return _isDragging;
	},	
	isZooming: function() {
		return _isZooming;
	},
	setScrollOffset: function(x,y) {
		_offset.x = x;
		_currentWindowScrollY = _offset.y = y;
		_shout('updateScrollOffset', _offset);
	},
	applyZoomPan: function(zoomLevel,panX,panY,allowRenderResolution) {
		_panOffset.x = panX;
		_panOffset.y = panY;
		_currZoomLevel = zoomLevel;
		_applyCurrentZoomPan( allowRenderResolution );
	},

	init: function() {

		if(_isOpen || _isDestroying) {
			return;
		}

		var i;

		self.framework = framework; // basic functionality
		self.template = template; // root DOM element of PhotoSwipe
		self.bg = framework.getChildByClass(template, 'pswp__bg');

		_initalClassName = template.className;
		_isOpen = true;
				
		_features = framework.detectFeatures();
		_requestAF = _features.raf;
		_cancelAF = _features.caf;
		_transformKey = _features.transform;
		_oldIE = _features.oldIE;
		
		self.scrollWrap = framework.getChildByClass(template, 'pswp__scroll-wrap');
		self.container = framework.getChildByClass(self.scrollWrap, 'pswp__container');

		_containerStyle = self.container.style; // for fast access

		// Objects that hold slides (there are only 3 in DOM)
		self.itemHolders = _itemHolders = [
			{el:self.container.children[0] , wrap:0, index: -1},
			{el:self.container.children[1] , wrap:0, index: -1},
			{el:self.container.children[2] , wrap:0, index: -1}
		];

		// hide nearby item holders until initial zoom animation finishes (to avoid extra Paints)
		_itemHolders[0].el.style.display = _itemHolders[2].el.style.display = 'none';

		_setupTransforms();

		// Setup global events
		_globalEventHandlers = {
			resize: self.updateSize,

			// Fixes: iOS 10.3 resize event
			// does not update scrollWrap.clientWidth instantly after resize
			// https://github.com/dimsemenov/PhotoSwipe/issues/1315
			orientationchange: function() {
				clearTimeout(_orientationChangeTimeout);
				_orientationChangeTimeout = setTimeout(function() {
					if(_viewportSize.x !== self.scrollWrap.clientWidth) {
						self.updateSize();
					}
				}, 500);
			},
			scroll: _updatePageScrollOffset,
			keydown: _onKeyDown,
			click: _onGlobalClick
		};

		// disable show/hide effects on old browsers that don't support CSS animations or transforms, 
		// old IOS, Android and Opera mobile. Blackberry seems to work fine, even older models.
		var oldPhone = _features.isOldIOSPhone || _features.isOldAndroid || _features.isMobileOpera;
		if(!_features.animationName || !_features.transform || oldPhone) {
			_options.showAnimationDuration = _options.hideAnimationDuration = 0;
		}

		// init modules
		for(i = 0; i < _modules.length; i++) {
			self['init' + _modules[i]]();
		}
		
		// init
		if(UiClass) {
			var ui = self.ui = new UiClass(self, framework);
			ui.init();
		}

		_shout('firstUpdate');
		_currentItemIndex = _currentItemIndex || _options.index || 0;
		// validate index
		if( isNaN(_currentItemIndex) || _currentItemIndex < 0 || _currentItemIndex >= _getNumItems() ) {
			_currentItemIndex = 0;
		}
		self.currItem = _getItemAt( _currentItemIndex );

		
		if(_features.isOldIOSPhone || _features.isOldAndroid) {
			_isFixedPosition = false;
		}
		
		template.setAttribute('aria-hidden', 'false');
		if(_options.modal) {
			if(!_isFixedPosition) {
				template.style.position = 'absolute';
				template.style.top = framework.getScrollY() + 'px';
			} else {
				template.style.position = 'fixed';
			}
		}

		if(_currentWindowScrollY === undefined) {
			_shout('initialLayout');
			_currentWindowScrollY = _initalWindowScrollY = framework.getScrollY();
		}
		
		// add classes to root element of PhotoSwipe
		var rootClasses = 'pswp--open ';
		if(_options.mainClass) {
			rootClasses += _options.mainClass + ' ';
		}
		if(_options.showHideOpacity) {
			rootClasses += 'pswp--animate_opacity ';
		}
		rootClasses += _likelyTouchDevice ? 'pswp--touch' : 'pswp--notouch';
		rootClasses += _features.animationName ? ' pswp--css_animation' : '';
		rootClasses += _features.svg ? ' pswp--svg' : '';
		framework.addClass(template, rootClasses);

		self.updateSize();

		// initial update
		_containerShiftIndex = -1;
		_indexDiff = null;
		for(i = 0; i < NUM_HOLDERS; i++) {
			_setTranslateX( (i+_containerShiftIndex) * _slideSize.x, _itemHolders[i].el.style);
		}

		if(!_oldIE) {
			framework.bind(self.scrollWrap, _downEvents, self); // no dragging for old IE
		}	

		_listen('initialZoomInEnd', function() {
			self.setContent(_itemHolders[0], _currentItemIndex-1);
			self.setContent(_itemHolders[2], _currentItemIndex+1);

			_itemHolders[0].el.style.display = _itemHolders[2].el.style.display = 'block';

			if(_options.focus) {
				// focus causes layout, 
				// which causes lag during the animation, 
				// that's why we delay it untill the initial zoom transition ends
				template.focus();
			}
			 

			_bindEvents();
		});

		// set content for center slide (first time)
		self.setContent(_itemHolders[1], _currentItemIndex);
		
		self.updateCurrItem();

		_shout('afterInit');

		if(!_isFixedPosition) {

			// On all versions of iOS lower than 8.0, we check size of viewport every second.
			// 
			// This is done to detect when Safari top & bottom bars appear, 
			// as this action doesn't trigger any events (like resize). 
			// 
			// On iOS8 they fixed this.
			// 
			// 10 Nov 2014: iOS 7 usage ~40%. iOS 8 usage 56%.
			
			_updateSizeInterval = setInterval(function() {
				if(!_numAnimations && !_isDragging && !_isZooming && (_currZoomLevel === self.currItem.initialZoomLevel)  ) {
					self.updateSize();
				}
			}, 1000);
		}

		framework.addClass(template, 'pswp--visible');
	},

	// Close the gallery, then destroy it
	close: function() {
		if(!_isOpen) {
			return;
		}

		_isOpen = false;
		_isDestroying = true;
		_shout('close');
		_unbindEvents();

		_showOrHide(self.currItem, null, true, self.destroy);
	},

	// destroys the gallery (unbinds events, cleans up intervals and timeouts to avoid memory leaks)
	destroy: function() {
		_shout('destroy');

		if(_showOrHideTimeout) {
			clearTimeout(_showOrHideTimeout);
		}
		
		template.setAttribute('aria-hidden', 'true');
		template.className = _initalClassName;

		if(_updateSizeInterval) {
			clearInterval(_updateSizeInterval);
		}

		framework.unbind(self.scrollWrap, _downEvents, self);

		// we unbind scroll event at the end, as closing animation may depend on it
		framework.unbind(window, 'scroll', self);

		_stopDragUpdateLoop();

		_stopAllAnimations();

		_listeners = null;
	},

	/**
	 * Pan image to position
	 * @param {Number} x     
	 * @param {Number} y     
	 * @param {Boolean} force Will ignore bounds if set to true.
	 */
	panTo: function(x,y,force) {
		if(!force) {
			if(x > _currPanBounds.min.x) {
				x = _currPanBounds.min.x;
			} else if(x < _currPanBounds.max.x) {
				x = _currPanBounds.max.x;
			}

			if(y > _currPanBounds.min.y) {
				y = _currPanBounds.min.y;
			} else if(y < _currPanBounds.max.y) {
				y = _currPanBounds.max.y;
			}
		}
		
		_panOffset.x = x;
		_panOffset.y = y;
		_applyCurrentZoomPan();
	},
	
	handleEvent: function (e) {
		e = e || window.event;
		if(_globalEventHandlers[e.type]) {
			_globalEventHandlers[e.type](e);
		}
	},


	goTo: function(index) {

		index = _getLoopedId(index);

		var diff = index - _currentItemIndex;
		_indexDiff = diff;

		_currentItemIndex = index;
		self.currItem = _getItemAt( _currentItemIndex );
		_currPositionIndex -= diff;
		
		_moveMainScroll(_slideSize.x * _currPositionIndex);
		

		_stopAllAnimations();
		_mainScrollAnimating = false;

		self.updateCurrItem();
	},
	next: function() {
		self.goTo( _currentItemIndex + 1);
	},
	prev: function() {
		self.goTo( _currentItemIndex - 1);
	},

	// update current zoom/pan objects
	updateCurrZoomItem: function(emulateSetContent) {
		if(emulateSetContent) {
			_shout('beforeChange', 0);
		}

		// itemHolder[1] is middle (current) item
		if(_itemHolders[1].el.children.length) {
			var zoomElement = _itemHolders[1].el.children[0];
			if( framework.hasClass(zoomElement, 'pswp__zoom-wrap') ) {
				_currZoomElementStyle = zoomElement.style;
			} else {
				_currZoomElementStyle = null;
			}
		} else {
			_currZoomElementStyle = null;
		}
		
		_currPanBounds = self.currItem.bounds;	
		_startZoomLevel = _currZoomLevel = self.currItem.initialZoomLevel;

		_panOffset.x = _currPanBounds.center.x;
		_panOffset.y = _currPanBounds.center.y;

		if(emulateSetContent) {
			_shout('afterChange');
		}
	},


	invalidateCurrItems: function() {
		_itemsNeedUpdate = true;
		for(var i = 0; i < NUM_HOLDERS; i++) {
			if( _itemHolders[i].item ) {
				_itemHolders[i].item.needsUpdate = true;
			}
		}
	},

	updateCurrItem: function(beforeAnimation) {

		if(_indexDiff === 0) {
			return;
		}

		var diffAbs = Math.abs(_indexDiff),
			tempHolder;

		if(beforeAnimation && diffAbs < 2) {
			return;
		}


		self.currItem = _getItemAt( _currentItemIndex );
		_renderMaxResolution = false;
		
		_shout('beforeChange', _indexDiff);

		if(diffAbs >= NUM_HOLDERS) {
			_containerShiftIndex += _indexDiff + (_indexDiff > 0 ? -NUM_HOLDERS : NUM_HOLDERS);
			diffAbs = NUM_HOLDERS;
		}
		for(var i = 0; i < diffAbs; i++) {
			if(_indexDiff > 0) {
				tempHolder = _itemHolders.shift();
				_itemHolders[NUM_HOLDERS-1] = tempHolder; // move first to last

				_containerShiftIndex++;
				_setTranslateX( (_containerShiftIndex+2) * _slideSize.x, tempHolder.el.style);
				self.setContent(tempHolder, _currentItemIndex - diffAbs + i + 1 + 1);
			} else {
				tempHolder = _itemHolders.pop();
				_itemHolders.unshift( tempHolder ); // move last to first

				_containerShiftIndex--;
				_setTranslateX( _containerShiftIndex * _slideSize.x, tempHolder.el.style);
				self.setContent(tempHolder, _currentItemIndex + diffAbs - i - 1 - 1);
			}
			
		}

		// reset zoom/pan on previous item
		if(_currZoomElementStyle && Math.abs(_indexDiff) === 1) {

			var prevItem = _getItemAt(_prevItemIndex);
			if(prevItem.initialZoomLevel !== _currZoomLevel) {
				_calculateItemSize(prevItem , _viewportSize );
				_setImageSize(prevItem);
				_applyZoomPanToItem( prevItem ); 				
			}

		}

		// reset diff after update
		_indexDiff = 0;

		self.updateCurrZoomItem();

		_prevItemIndex = _currentItemIndex;

		_shout('afterChange');
		
	},



	updateSize: function(force) {
		
		if(!_isFixedPosition && _options.modal) {
			var windowScrollY = framework.getScrollY();
			if(_currentWindowScrollY !== windowScrollY) {
				template.style.top = windowScrollY + 'px';
				_currentWindowScrollY = windowScrollY;
			}
			if(!force && _windowVisibleSize.x === window.innerWidth && _windowVisibleSize.y === window.innerHeight) {
				return;
			}
			_windowVisibleSize.x = window.innerWidth;
			_windowVisibleSize.y = window.innerHeight;

			//template.style.width = _windowVisibleSize.x + 'px';
			template.style.height = _windowVisibleSize.y + 'px';
		}



		_viewportSize.x = self.scrollWrap.clientWidth;
		_viewportSize.y = self.scrollWrap.clientHeight;

		_updatePageScrollOffset();

		_slideSize.x = _viewportSize.x + Math.round(_viewportSize.x * _options.spacing);
		_slideSize.y = _viewportSize.y;

		_moveMainScroll(_slideSize.x * _currPositionIndex);

		_shout('beforeResize'); // even may be used for example to switch image sources


		// don't re-calculate size on inital size update
		if(_containerShiftIndex !== undefined) {

			var holder,
				item,
				hIndex;

			for(var i = 0; i < NUM_HOLDERS; i++) {
				holder = _itemHolders[i];
				_setTranslateX( (i+_containerShiftIndex) * _slideSize.x, holder.el.style);

				hIndex = _currentItemIndex+i-1;

				if(_options.loop && _getNumItems() > 2) {
					hIndex = _getLoopedId(hIndex);
				}

				// update zoom level on items and refresh source (if needsUpdate)
				item = _getItemAt( hIndex );

				// re-render gallery item if `needsUpdate`,
				// or doesn't have `bounds` (entirely new slide object)
				if( item && (_itemsNeedUpdate || item.needsUpdate || !item.bounds) ) {

					self.cleanSlide( item );
					
					self.setContent( holder, hIndex );

					// if "center" slide
					if(i === 1) {
						self.currItem = item;
						self.updateCurrZoomItem(true);
					}

					item.needsUpdate = false;

				} else if(holder.index === -1 && hIndex >= 0) {
					// add content first time
					self.setContent( holder, hIndex );
				}
				if(item && item.container) {
					_calculateItemSize(item, _viewportSize);
					_setImageSize(item);
					_applyZoomPanToItem( item );
				}
				
			}
			_itemsNeedUpdate = false;
		}	

		_startZoomLevel = _currZoomLevel = self.currItem.initialZoomLevel;
		_currPanBounds = self.currItem.bounds;

		if(_currPanBounds) {
			_panOffset.x = _currPanBounds.center.x;
			_panOffset.y = _currPanBounds.center.y;
			_applyCurrentZoomPan( true );
		}
		
		_shout('resize');
	},
	
	// Zoom current item to
	zoomTo: function(destZoomLevel, centerPoint, speed, easingFn, updateFn) {
		/*
			if(destZoomLevel === 'fit') {
				destZoomLevel = self.currItem.fitRatio;
			} else if(destZoomLevel === 'fill') {
				destZoomLevel = self.currItem.fillRatio;
			}
		*/

		if(centerPoint) {
			_startZoomLevel = _currZoomLevel;
			_midZoomPoint.x = Math.abs(centerPoint.x) - _panOffset.x ;
			_midZoomPoint.y = Math.abs(centerPoint.y) - _panOffset.y ;
			_equalizePoints(_startPanOffset, _panOffset);
		}

		var destPanBounds = _calculatePanBounds(destZoomLevel, false),
			destPanOffset = {};

		_modifyDestPanOffset('x', destPanBounds, destPanOffset, destZoomLevel);
		_modifyDestPanOffset('y', destPanBounds, destPanOffset, destZoomLevel);

		var initialZoomLevel = _currZoomLevel;
		var initialPanOffset = {
			x: _panOffset.x,
			y: _panOffset.y
		};

		_roundPoint(destPanOffset);

		var onUpdate = function(now) {
			if(now === 1) {
				_currZoomLevel = destZoomLevel;
				_panOffset.x = destPanOffset.x;
				_panOffset.y = destPanOffset.y;
			} else {
				_currZoomLevel = (destZoomLevel - initialZoomLevel) * now + initialZoomLevel;
				_panOffset.x = (destPanOffset.x - initialPanOffset.x) * now + initialPanOffset.x;
				_panOffset.y = (destPanOffset.y - initialPanOffset.y) * now + initialPanOffset.y;
			}

			if(updateFn) {
				updateFn(now);
			}

			_applyCurrentZoomPan( now === 1 );
		};

		if(speed) {
			_animateProp('customZoomTo', 0, 1, speed, easingFn || framework.easing.sine.inOut, onUpdate);
		} else {
			onUpdate(1);
		}
	}


};


/*>>core*/

/*>>gestures*/
/**
 * Mouse/touch/pointer event handlers.
 * 
 * separated from @core.js for readability
 */

var MIN_SWIPE_DISTANCE = 30,
	DIRECTION_CHECK_OFFSET = 10; // amount of pixels to drag to determine direction of swipe

var _gestureStartTime,
	_gestureCheckSpeedTime,

	// pool of objects that are used during dragging of zooming
	p = {}, // first point
	p2 = {}, // second point (for zoom gesture)
	delta = {},
	_currPoint = {},
	_startPoint = {},
	_currPointers = [],
	_startMainScrollPos = {},
	_releaseAnimData,
	_posPoints = [], // array of points during dragging, used to determine type of gesture
	_tempPoint = {},

	_isZoomingIn,
	_verticalDragInitiated,
	_oldAndroidTouchEndTimeout,
	_currZoomedItemIndex = 0,
	_centerPoint = _getEmptyPoint(),
	_lastReleaseTime = 0,
	_isDragging, // at least one pointer is down
	_isMultitouch, // at least two _pointers are down
	_zoomStarted, // zoom level changed during zoom gesture
	_moved,
	_dragAnimFrame,
	_mainScrollShifted,
	_currentPoints, // array of current touch points
	_isZooming,
	_currPointsDistance,
	_startPointsDistance,
	_currPanBounds,
	_mainScrollPos = _getEmptyPoint(),
	_currZoomElementStyle,
	_mainScrollAnimating, // true, if animation after swipe gesture is running
	_midZoomPoint = _getEmptyPoint(),
	_currCenterPoint = _getEmptyPoint(),
	_direction,
	_isFirstMove,
	_opacityChanged,
	_bgOpacity,
	_wasOverInitialZoom,

	_isEqualPoints = function(p1, p2) {
		return p1.x === p2.x && p1.y === p2.y;
	},
	_isNearbyPoints = function(touch0, touch1) {
		return Math.abs(touch0.x - touch1.x) < DOUBLE_TAP_RADIUS && Math.abs(touch0.y - touch1.y) < DOUBLE_TAP_RADIUS;
	},
	_calculatePointsDistance = function(p1, p2) {
		_tempPoint.x = Math.abs( p1.x - p2.x );
		_tempPoint.y = Math.abs( p1.y - p2.y );
		return Math.sqrt(_tempPoint.x * _tempPoint.x + _tempPoint.y * _tempPoint.y);
	},
	_stopDragUpdateLoop = function() {
		if(_dragAnimFrame) {
			_cancelAF(_dragAnimFrame);
			_dragAnimFrame = null;
		}
	},
	_dragUpdateLoop = function() {
		if(_isDragging) {
			_dragAnimFrame = _requestAF(_dragUpdateLoop);
			_renderMovement();
		}
	},
	_canPan = function() {
		return !(_options.scaleMode === 'fit' && _currZoomLevel ===  self.currItem.initialZoomLevel);
	},
	
	// find the closest parent DOM element
	_closestElement = function(el, fn) {
	  	if(!el || el === document) {
	  		return false;
	  	}

	  	// don't search elements above pswp__scroll-wrap
	  	if(el.getAttribute('class') && el.getAttribute('class').indexOf('pswp__scroll-wrap') > -1 ) {
	  		return false;
	  	}

	  	if( fn(el) ) {
	  		return el;
	  	}

	  	return _closestElement(el.parentNode, fn);
	},

	_preventObj = {},
	_preventDefaultEventBehaviour = function(e, isDown) {
	    _preventObj.prevent = !_closestElement(e.target, _options.isClickableElement);

		_shout('preventDragEvent', e, isDown, _preventObj);
		return _preventObj.prevent;

	},
	_convertTouchToPoint = function(touch, p) {
		p.x = touch.pageX;
		p.y = touch.pageY;
		p.id = touch.identifier;
		return p;
	},
	_findCenterOfPoints = function(p1, p2, pCenter) {
		pCenter.x = (p1.x + p2.x) * 0.5;
		pCenter.y = (p1.y + p2.y) * 0.5;
	},
	_pushPosPoint = function(time, x, y) {
		if(time - _gestureCheckSpeedTime > 50) {
			var o = _posPoints.length > 2 ? _posPoints.shift() : {};
			o.x = x;
			o.y = y; 
			_posPoints.push(o);
			_gestureCheckSpeedTime = time;
		}
	},

	_calculateVerticalDragOpacityRatio = function() {
		var yOffset = _panOffset.y - self.currItem.initialPosition.y; // difference between initial and current position
		return 1 -  Math.abs( yOffset / (_viewportSize.y / 2)  );
	},

	
	// points pool, reused during touch events
	_ePoint1 = {},
	_ePoint2 = {},
	_tempPointsArr = [],
	_tempCounter,
	_getTouchPoints = function(e) {
		// clean up previous points, without recreating array
		while(_tempPointsArr.length > 0) {
			_tempPointsArr.pop();
		}

		if(!_pointerEventEnabled) {
			if(e.type.indexOf('touch') > -1) {

				if(e.touches && e.touches.length > 0) {
					_tempPointsArr[0] = _convertTouchToPoint(e.touches[0], _ePoint1);
					if(e.touches.length > 1) {
						_tempPointsArr[1] = _convertTouchToPoint(e.touches[1], _ePoint2);
					}
				}
				
			} else {
				_ePoint1.x = e.pageX;
				_ePoint1.y = e.pageY;
				_ePoint1.id = '';
				_tempPointsArr[0] = _ePoint1;//_ePoint1;
			}
		} else {
			_tempCounter = 0;
			// we can use forEach, as pointer events are supported only in modern browsers
			_currPointers.forEach(function(p) {
				if(_tempCounter === 0) {
					_tempPointsArr[0] = p;
				} else if(_tempCounter === 1) {
					_tempPointsArr[1] = p;
				}
				_tempCounter++;

			});
		}
		return _tempPointsArr;
	},

	_panOrMoveMainScroll = function(axis, delta) {

		var panFriction,
			overDiff = 0,
			newOffset = _panOffset[axis] + delta[axis],
			startOverDiff,
			dir = delta[axis] > 0,
			newMainScrollPosition = _mainScrollPos.x + delta.x,
			mainScrollDiff = _mainScrollPos.x - _startMainScrollPos.x,
			newPanPos,
			newMainScrollPos;

		// calculate fdistance over the bounds and friction
		if(newOffset > _currPanBounds.min[axis] || newOffset < _currPanBounds.max[axis]) {
			panFriction = _options.panEndFriction;
			// Linear increasing of friction, so at 1/4 of viewport it's at max value. 
			// Looks not as nice as was expected. Left for history.
			// panFriction = (1 - (_panOffset[axis] + delta[axis] + panBounds.min[axis]) / (_viewportSize[axis] / 4) );
		} else {
			panFriction = 1;
		}
		
		newOffset = _panOffset[axis] + delta[axis] * panFriction;

		// move main scroll or start panning
		if(_options.allowPanToNext || _currZoomLevel === self.currItem.initialZoomLevel) {


			if(!_currZoomElementStyle) {
				
				newMainScrollPos = newMainScrollPosition;

			} else if(_direction === 'h' && axis === 'x' && !_zoomStarted ) {
				
				if(dir) {
					if(newOffset > _currPanBounds.min[axis]) {
						panFriction = _options.panEndFriction;
						overDiff = _currPanBounds.min[axis] - newOffset;
						startOverDiff = _currPanBounds.min[axis] - _startPanOffset[axis];
					}
					
					// drag right
					if( (startOverDiff <= 0 || mainScrollDiff < 0) && _getNumItems() > 1 ) {
						newMainScrollPos = newMainScrollPosition;
						if(mainScrollDiff < 0 && newMainScrollPosition > _startMainScrollPos.x) {
							newMainScrollPos = _startMainScrollPos.x;
						}
					} else {
						if(_currPanBounds.min.x !== _currPanBounds.max.x) {
							newPanPos = newOffset;
						}
						
					}

				} else {

					if(newOffset < _currPanBounds.max[axis] ) {
						panFriction =_options.panEndFriction;
						overDiff = newOffset - _currPanBounds.max[axis];
						startOverDiff = _startPanOffset[axis] - _currPanBounds.max[axis];
					}

					if( (startOverDiff <= 0 || mainScrollDiff > 0) && _getNumItems() > 1 ) {
						newMainScrollPos = newMainScrollPosition;

						if(mainScrollDiff > 0 && newMainScrollPosition < _startMainScrollPos.x) {
							newMainScrollPos = _startMainScrollPos.x;
						}

					} else {
						if(_currPanBounds.min.x !== _currPanBounds.max.x) {
							newPanPos = newOffset;
						}
					}

				}


				//
			}

			if(axis === 'x') {

				if(newMainScrollPos !== undefined) {
					_moveMainScroll(newMainScrollPos, true);
					if(newMainScrollPos === _startMainScrollPos.x) {
						_mainScrollShifted = false;
					} else {
						_mainScrollShifted = true;
					}
				}

				if(_currPanBounds.min.x !== _currPanBounds.max.x) {
					if(newPanPos !== undefined) {
						_panOffset.x = newPanPos;
					} else if(!_mainScrollShifted) {
						_panOffset.x += delta.x * panFriction;
					}
				}

				return newMainScrollPos !== undefined;
			}

		}

		if(!_mainScrollAnimating) {
			
			if(!_mainScrollShifted) {
				if(_currZoomLevel > self.currItem.fitRatio) {
					_panOffset[axis] += delta[axis] * panFriction;
				
				}
			}

			
		}
		
	},

	// Pointerdown/touchstart/mousedown handler
	_onDragStart = function(e) {

		// Allow dragging only via left mouse button.
		// As this handler is not added in IE8 - we ignore e.which
		// 
		// http://www.quirksmode.org/js/events_properties.html
		// https://developer.mozilla.org/en-US/docs/Web/API/event.button
		if(e.type === 'mousedown' && e.button > 0  ) {
			return;
		}

		if(_initialZoomRunning) {
			e.preventDefault();
			return;
		}

		if(_oldAndroidTouchEndTimeout && e.type === 'mousedown') {
			return;
		}

		if(_preventDefaultEventBehaviour(e, true)) {
			e.preventDefault();
		}



		_shout('pointerDown');

		if(_pointerEventEnabled) {
			var pointerIndex = framework.arraySearch(_currPointers, e.pointerId, 'id');
			if(pointerIndex < 0) {
				pointerIndex = _currPointers.length;
			}
			_currPointers[pointerIndex] = {x:e.pageX, y:e.pageY, id: e.pointerId};
		}
		


		var startPointsList = _getTouchPoints(e),
			numPoints = startPointsList.length;

		_currentPoints = null;

		_stopAllAnimations();

		// init drag
		if(!_isDragging || numPoints === 1) {

			

			_isDragging = _isFirstMove = true;
			framework.bind(window, _upMoveEvents, self);

			_isZoomingIn = 
				_wasOverInitialZoom = 
				_opacityChanged = 
				_verticalDragInitiated = 
				_mainScrollShifted = 
				_moved = 
				_isMultitouch = 
				_zoomStarted = false;

			_direction = null;

			_shout('firstTouchStart', startPointsList);

			_equalizePoints(_startPanOffset, _panOffset);

			_currPanDist.x = _currPanDist.y = 0;
			_equalizePoints(_currPoint, startPointsList[0]);
			_equalizePoints(_startPoint, _currPoint);

			//_equalizePoints(_startMainScrollPos, _mainScrollPos);
			_startMainScrollPos.x = _slideSize.x * _currPositionIndex;

			_posPoints = [{
				x: _currPoint.x,
				y: _currPoint.y
			}];

			_gestureCheckSpeedTime = _gestureStartTime = _getCurrentTime();

			//_mainScrollAnimationEnd(true);
			_calculatePanBounds( _currZoomLevel, true );
			
			// Start rendering
			_stopDragUpdateLoop();
			_dragUpdateLoop();
			
		}

		// init zoom
		if(!_isZooming && numPoints > 1 && !_mainScrollAnimating && !_mainScrollShifted) {
			_startZoomLevel = _currZoomLevel;
			_zoomStarted = false; // true if zoom changed at least once

			_isZooming = _isMultitouch = true;
			_currPanDist.y = _currPanDist.x = 0;

			_equalizePoints(_startPanOffset, _panOffset);

			_equalizePoints(p, startPointsList[0]);
			_equalizePoints(p2, startPointsList[1]);

			_findCenterOfPoints(p, p2, _currCenterPoint);

			_midZoomPoint.x = Math.abs(_currCenterPoint.x) - _panOffset.x;
			_midZoomPoint.y = Math.abs(_currCenterPoint.y) - _panOffset.y;
			_currPointsDistance = _startPointsDistance = _calculatePointsDistance(p, p2);
		}


	},

	// Pointermove/touchmove/mousemove handler
	_onDragMove = function(e) {

		e.preventDefault();

		if(_pointerEventEnabled) {
			var pointerIndex = framework.arraySearch(_currPointers, e.pointerId, 'id');
			if(pointerIndex > -1) {
				var p = _currPointers[pointerIndex];
				p.x = e.pageX;
				p.y = e.pageY; 
			}
		}

		if(_isDragging) {
			var touchesList = _getTouchPoints(e);
			if(!_direction && !_moved && !_isZooming) {

				if(_mainScrollPos.x !== _slideSize.x * _currPositionIndex) {
					// if main scroll position is shifted – direction is always horizontal
					_direction = 'h';
				} else {
					var diff = Math.abs(touchesList[0].x - _currPoint.x) - Math.abs(touchesList[0].y - _currPoint.y);
					// check the direction of movement
					if(Math.abs(diff) >= DIRECTION_CHECK_OFFSET) {
						_direction = diff > 0 ? 'h' : 'v';
						_currentPoints = touchesList;
					}
				}
				
			} else {
				_currentPoints = touchesList;
			}
		}	
	},
	// 
	_renderMovement =  function() {

		if(!_currentPoints) {
			return;
		}

		var numPoints = _currentPoints.length;

		if(numPoints === 0) {
			return;
		}

		_equalizePoints(p, _currentPoints[0]);

		delta.x = p.x - _currPoint.x;
		delta.y = p.y - _currPoint.y;

		if(_isZooming && numPoints > 1) {
			// Handle behaviour for more than 1 point

			_currPoint.x = p.x;
			_currPoint.y = p.y;
		
			// check if one of two points changed
			if( !delta.x && !delta.y && _isEqualPoints(_currentPoints[1], p2) ) {
				return;
			}

			_equalizePoints(p2, _currentPoints[1]);


			if(!_zoomStarted) {
				_zoomStarted = true;
				_shout('zoomGestureStarted');
			}
			
			// Distance between two points
			var pointsDistance = _calculatePointsDistance(p,p2);

			var zoomLevel = _calculateZoomLevel(pointsDistance);

			// slightly over the of initial zoom level
			if(zoomLevel > self.currItem.initialZoomLevel + self.currItem.initialZoomLevel / 15) {
				_wasOverInitialZoom = true;
			}

			// Apply the friction if zoom level is out of the bounds
			var zoomFriction = 1,
				minZoomLevel = _getMinZoomLevel(),
				maxZoomLevel = _getMaxZoomLevel();

			if ( zoomLevel < minZoomLevel ) {
				
				if(_options.pinchToClose && !_wasOverInitialZoom && _startZoomLevel <= self.currItem.initialZoomLevel) {
					// fade out background if zooming out
					var minusDiff = minZoomLevel - zoomLevel;
					var percent = 1 - minusDiff / (minZoomLevel / 1.2);

					_applyBgOpacity(percent);
					_shout('onPinchClose', percent);
					_opacityChanged = true;
				} else {
					zoomFriction = (minZoomLevel - zoomLevel) / minZoomLevel;
					if(zoomFriction > 1) {
						zoomFriction = 1;
					}
					zoomLevel = minZoomLevel - zoomFriction * (minZoomLevel / 3);
				}
				
			} else if ( zoomLevel > maxZoomLevel ) {
				// 1.5 - extra zoom level above the max. E.g. if max is x6, real max 6 + 1.5 = 7.5
				zoomFriction = (zoomLevel - maxZoomLevel) / ( minZoomLevel * 6 );
				if(zoomFriction > 1) {
					zoomFriction = 1;
				}
				zoomLevel = maxZoomLevel + zoomFriction * minZoomLevel;
			}

			if(zoomFriction < 0) {
				zoomFriction = 0;
			}

			// distance between touch points after friction is applied
			_currPointsDistance = pointsDistance;

			// _centerPoint - The point in the middle of two pointers
			_findCenterOfPoints(p, p2, _centerPoint);
		
			// paning with two pointers pressed
			_currPanDist.x += _centerPoint.x - _currCenterPoint.x;
			_currPanDist.y += _centerPoint.y - _currCenterPoint.y;
			_equalizePoints(_currCenterPoint, _centerPoint);

			_panOffset.x = _calculatePanOffset('x', zoomLevel);
			_panOffset.y = _calculatePanOffset('y', zoomLevel);

			_isZoomingIn = zoomLevel > _currZoomLevel;
			_currZoomLevel = zoomLevel;
			_applyCurrentZoomPan();

		} else {

			// handle behaviour for one point (dragging or panning)

			if(!_direction) {
				return;
			}

			if(_isFirstMove) {
				_isFirstMove = false;

				// subtract drag distance that was used during the detection direction  

				if( Math.abs(delta.x) >= DIRECTION_CHECK_OFFSET) {
					delta.x -= _currentPoints[0].x - _startPoint.x;
				}
				
				if( Math.abs(delta.y) >= DIRECTION_CHECK_OFFSET) {
					delta.y -= _currentPoints[0].y - _startPoint.y;
				}
			}

			_currPoint.x = p.x;
			_currPoint.y = p.y;

			// do nothing if pointers position hasn't changed
			if(delta.x === 0 && delta.y === 0) {
				return;
			}

			if(_direction === 'v' && _options.closeOnVerticalDrag) {
				if(!_canPan()) {
					_currPanDist.y += delta.y;
					_panOffset.y += delta.y;

					var opacityRatio = _calculateVerticalDragOpacityRatio();

					_verticalDragInitiated = true;
					_shout('onVerticalDrag', opacityRatio);

					_applyBgOpacity(opacityRatio);
					_applyCurrentZoomPan();
					return ;
				}
			}

			_pushPosPoint(_getCurrentTime(), p.x, p.y);

			_moved = true;
			_currPanBounds = self.currItem.bounds;
			
			var mainScrollChanged = _panOrMoveMainScroll('x', delta);
			if(!mainScrollChanged) {
				_panOrMoveMainScroll('y', delta);

				_roundPoint(_panOffset);
				_applyCurrentZoomPan();
			}

		}

	},
	
	// Pointerup/pointercancel/touchend/touchcancel/mouseup event handler
	_onDragRelease = function(e) {

		if(_features.isOldAndroid ) {

			if(_oldAndroidTouchEndTimeout && e.type === 'mouseup') {
				return;
			}

			// on Android (v4.1, 4.2, 4.3 & possibly older) 
			// ghost mousedown/up event isn't preventable via e.preventDefault,
			// which causes fake mousedown event
			// so we block mousedown/up for 600ms
			if( e.type.indexOf('touch') > -1 ) {
				clearTimeout(_oldAndroidTouchEndTimeout);
				_oldAndroidTouchEndTimeout = setTimeout(function() {
					_oldAndroidTouchEndTimeout = 0;
				}, 600);
			}
			
		}

		_shout('pointerUp');

		if(_preventDefaultEventBehaviour(e, false)) {
			e.preventDefault();
		}

		var releasePoint;

		if(_pointerEventEnabled) {
			var pointerIndex = framework.arraySearch(_currPointers, e.pointerId, 'id');
			
			if(pointerIndex > -1) {
				releasePoint = _currPointers.splice(pointerIndex, 1)[0];

				if(navigator.pointerEnabled) {
					releasePoint.type = e.pointerType || 'mouse';
				} else {
					var MSPOINTER_TYPES = {
						4: 'mouse', // event.MSPOINTER_TYPE_MOUSE
						2: 'touch', // event.MSPOINTER_TYPE_TOUCH 
						3: 'pen' // event.MSPOINTER_TYPE_PEN
					};
					releasePoint.type = MSPOINTER_TYPES[e.pointerType];

					if(!releasePoint.type) {
						releasePoint.type = e.pointerType || 'mouse';
					}
				}

			}
		}

		var touchList = _getTouchPoints(e),
			gestureType,
			numPoints = touchList.length;

		if(e.type === 'mouseup') {
			numPoints = 0;
		}

		// Do nothing if there were 3 touch points or more
		if(numPoints === 2) {
			_currentPoints = null;
			return true;
		}

		// if second pointer released
		if(numPoints === 1) {
			_equalizePoints(_startPoint, touchList[0]);
		}				


		// pointer hasn't moved, send "tap release" point
		if(numPoints === 0 && !_direction && !_mainScrollAnimating) {
			if(!releasePoint) {
				if(e.type === 'mouseup') {
					releasePoint = {x: e.pageX, y: e.pageY, type:'mouse'};
				} else if(e.changedTouches && e.changedTouches[0]) {
					releasePoint = {x: e.changedTouches[0].pageX, y: e.changedTouches[0].pageY, type:'touch'};
				}		
			}

			_shout('touchRelease', e, releasePoint);
		}

		// Difference in time between releasing of two last touch points (zoom gesture)
		var releaseTimeDiff = -1;

		// Gesture completed, no pointers left
		if(numPoints === 0) {
			_isDragging = false;
			framework.unbind(window, _upMoveEvents, self);

			_stopDragUpdateLoop();

			if(_isZooming) {
				// Two points released at the same time
				releaseTimeDiff = 0;
			} else if(_lastReleaseTime !== -1) {
				releaseTimeDiff = _getCurrentTime() - _lastReleaseTime;
			}
		}
		_lastReleaseTime = numPoints === 1 ? _getCurrentTime() : -1;
		
		if(releaseTimeDiff !== -1 && releaseTimeDiff < 150) {
			gestureType = 'zoom';
		} else {
			gestureType = 'swipe';
		}

		if(_isZooming && numPoints < 2) {
			_isZooming = false;

			// Only second point released
			if(numPoints === 1) {
				gestureType = 'zoomPointerUp';
			}
			_shout('zoomGestureEnded');
		}

		_currentPoints = null;
		if(!_moved && !_zoomStarted && !_mainScrollAnimating && !_verticalDragInitiated) {
			// nothing to animate
			return;
		}
	
		_stopAllAnimations();

		
		if(!_releaseAnimData) {
			_releaseAnimData = _initDragReleaseAnimationData();
		}
		
		_releaseAnimData.calculateSwipeSpeed('x');


		if(_verticalDragInitiated) {

			var opacityRatio = _calculateVerticalDragOpacityRatio();

			if(opacityRatio < _options.verticalDragRange) {
				self.close();
			} else {
				var initalPanY = _panOffset.y,
					initialBgOpacity = _bgOpacity;

				_animateProp('verticalDrag', 0, 1, 300, framework.easing.cubic.out, function(now) {
					
					_panOffset.y = (self.currItem.initialPosition.y - initalPanY) * now + initalPanY;

					_applyBgOpacity(  (1 - initialBgOpacity) * now + initialBgOpacity );
					_applyCurrentZoomPan();
				});

				_shout('onVerticalDrag', 1);
			}

			return;
		}


		// main scroll 
		if(  (_mainScrollShifted || _mainScrollAnimating) && numPoints === 0) {
			var itemChanged = _finishSwipeMainScrollGesture(gestureType, _releaseAnimData);
			if(itemChanged) {
				return;
			}
			gestureType = 'zoomPointerUp';
		}

		// prevent zoom/pan animation when main scroll animation runs
		if(_mainScrollAnimating) {
			return;
		}
		
		// Complete simple zoom gesture (reset zoom level if it's out of the bounds)  
		if(gestureType !== 'swipe') {
			_completeZoomGesture();
			return;
		}
	
		// Complete pan gesture if main scroll is not shifted, and it's possible to pan current image
		if(!_mainScrollShifted && _currZoomLevel > self.currItem.fitRatio) {
			_completePanGesture(_releaseAnimData);
		}
	},


	// Returns object with data about gesture
	// It's created only once and then reused
	_initDragReleaseAnimationData  = function() {
		// temp local vars
		var lastFlickDuration,
			tempReleasePos;

		// s = this
		var s = {
			lastFlickOffset: {},
			lastFlickDist: {},
			lastFlickSpeed: {},
			slowDownRatio:  {},
			slowDownRatioReverse:  {},
			speedDecelerationRatio:  {},
			speedDecelerationRatioAbs:  {},
			distanceOffset:  {},
			backAnimDestination: {},
			backAnimStarted: {},
			calculateSwipeSpeed: function(axis) {
				

				if( _posPoints.length > 1) {
					lastFlickDuration = _getCurrentTime() - _gestureCheckSpeedTime + 50;
					tempReleasePos = _posPoints[_posPoints.length-2][axis];
				} else {
					lastFlickDuration = _getCurrentTime() - _gestureStartTime; // total gesture duration
					tempReleasePos = _startPoint[axis];
				}
				s.lastFlickOffset[axis] = _currPoint[axis] - tempReleasePos;
				s.lastFlickDist[axis] = Math.abs(s.lastFlickOffset[axis]);
				if(s.lastFlickDist[axis] > 20) {
					s.lastFlickSpeed[axis] = s.lastFlickOffset[axis] / lastFlickDuration;
				} else {
					s.lastFlickSpeed[axis] = 0;
				}
				if( Math.abs(s.lastFlickSpeed[axis]) < 0.1 ) {
					s.lastFlickSpeed[axis] = 0;
				}
				
				s.slowDownRatio[axis] = 0.95;
				s.slowDownRatioReverse[axis] = 1 - s.slowDownRatio[axis];
				s.speedDecelerationRatio[axis] = 1;
			},

			calculateOverBoundsAnimOffset: function(axis, speed) {
				if(!s.backAnimStarted[axis]) {

					if(_panOffset[axis] > _currPanBounds.min[axis]) {
						s.backAnimDestination[axis] = _currPanBounds.min[axis];
						
					} else if(_panOffset[axis] < _currPanBounds.max[axis]) {
						s.backAnimDestination[axis] = _currPanBounds.max[axis];
					}

					if(s.backAnimDestination[axis] !== undefined) {
						s.slowDownRatio[axis] = 0.7;
						s.slowDownRatioReverse[axis] = 1 - s.slowDownRatio[axis];
						if(s.speedDecelerationRatioAbs[axis] < 0.05) {

							s.lastFlickSpeed[axis] = 0;
							s.backAnimStarted[axis] = true;

							_animateProp('bounceZoomPan'+axis,_panOffset[axis], 
								s.backAnimDestination[axis], 
								speed || 300, 
								framework.easing.sine.out, 
								function(pos) {
									_panOffset[axis] = pos;
									_applyCurrentZoomPan();
								}
							);

						}
					}
				}
			},

			// Reduces the speed by slowDownRatio (per 10ms)
			calculateAnimOffset: function(axis) {
				if(!s.backAnimStarted[axis]) {
					s.speedDecelerationRatio[axis] = s.speedDecelerationRatio[axis] * (s.slowDownRatio[axis] + 
												s.slowDownRatioReverse[axis] - 
												s.slowDownRatioReverse[axis] * s.timeDiff / 10);

					s.speedDecelerationRatioAbs[axis] = Math.abs(s.lastFlickSpeed[axis] * s.speedDecelerationRatio[axis]);
					s.distanceOffset[axis] = s.lastFlickSpeed[axis] * s.speedDecelerationRatio[axis] * s.timeDiff;
					_panOffset[axis] += s.distanceOffset[axis];

				}
			},

			panAnimLoop: function() {
				if ( _animations.zoomPan ) {
					_animations.zoomPan.raf = _requestAF(s.panAnimLoop);

					s.now = _getCurrentTime();
					s.timeDiff = s.now - s.lastNow;
					s.lastNow = s.now;
					
					s.calculateAnimOffset('x');
					s.calculateAnimOffset('y');

					_applyCurrentZoomPan();
					
					s.calculateOverBoundsAnimOffset('x');
					s.calculateOverBoundsAnimOffset('y');


					if (s.speedDecelerationRatioAbs.x < 0.05 && s.speedDecelerationRatioAbs.y < 0.05) {

						// round pan position
						_panOffset.x = Math.round(_panOffset.x);
						_panOffset.y = Math.round(_panOffset.y);
						_applyCurrentZoomPan();
						
						_stopAnimation('zoomPan');
						return;
					}
				}

			}
		};
		return s;
	},

	_completePanGesture = function(animData) {
		// calculate swipe speed for Y axis (paanning)
		animData.calculateSwipeSpeed('y');

		_currPanBounds = self.currItem.bounds;
		
		animData.backAnimDestination = {};
		animData.backAnimStarted = {};

		// Avoid acceleration animation if speed is too low
		if(Math.abs(animData.lastFlickSpeed.x) <= 0.05 && Math.abs(animData.lastFlickSpeed.y) <= 0.05 ) {
			animData.speedDecelerationRatioAbs.x = animData.speedDecelerationRatioAbs.y = 0;

			// Run pan drag release animation. E.g. if you drag image and release finger without momentum.
			animData.calculateOverBoundsAnimOffset('x');
			animData.calculateOverBoundsAnimOffset('y');
			return true;
		}

		// Animation loop that controls the acceleration after pan gesture ends
		_registerStartAnimation('zoomPan');
		animData.lastNow = _getCurrentTime();
		animData.panAnimLoop();
	},


	_finishSwipeMainScrollGesture = function(gestureType, _releaseAnimData) {
		var itemChanged;
		if(!_mainScrollAnimating) {
			_currZoomedItemIndex = _currentItemIndex;
		}


		
		var itemsDiff;

		if(gestureType === 'swipe') {
			var totalShiftDist = _currPoint.x - _startPoint.x,
				isFastLastFlick = _releaseAnimData.lastFlickDist.x < 10;

			// if container is shifted for more than MIN_SWIPE_DISTANCE, 
			// and last flick gesture was in right direction
			if(totalShiftDist > MIN_SWIPE_DISTANCE && 
				(isFastLastFlick || _releaseAnimData.lastFlickOffset.x > 20) ) {
				// go to prev item
				itemsDiff = -1;
			} else if(totalShiftDist < -MIN_SWIPE_DISTANCE && 
				(isFastLastFlick || _releaseAnimData.lastFlickOffset.x < -20) ) {
				// go to next item
				itemsDiff = 1;
			}
		}

		var nextCircle;

		if(itemsDiff) {
			
			_currentItemIndex += itemsDiff;

			if(_currentItemIndex < 0) {
				_currentItemIndex = _options.loop ? _getNumItems()-1 : 0;
				nextCircle = true;
			} else if(_currentItemIndex >= _getNumItems()) {
				_currentItemIndex = _options.loop ? 0 : _getNumItems()-1;
				nextCircle = true;
			}

			if(!nextCircle || _options.loop) {
				_indexDiff += itemsDiff;
				_currPositionIndex -= itemsDiff;
				itemChanged = true;
			}
			

			
		}

		var animateToX = _slideSize.x * _currPositionIndex;
		var animateToDist = Math.abs( animateToX - _mainScrollPos.x );
		var finishAnimDuration;


		if(!itemChanged && animateToX > _mainScrollPos.x !== _releaseAnimData.lastFlickSpeed.x > 0) {
			// "return to current" duration, e.g. when dragging from slide 0 to -1
			finishAnimDuration = 333; 
		} else {
			finishAnimDuration = Math.abs(_releaseAnimData.lastFlickSpeed.x) > 0 ? 
									animateToDist / Math.abs(_releaseAnimData.lastFlickSpeed.x) : 
									333;

			finishAnimDuration = Math.min(finishAnimDuration, 400);
			finishAnimDuration = Math.max(finishAnimDuration, 250);
		}

		if(_currZoomedItemIndex === _currentItemIndex) {
			itemChanged = false;
		}
		
		_mainScrollAnimating = true;
		
		_shout('mainScrollAnimStart');

		_animateProp('mainScroll', _mainScrollPos.x, animateToX, finishAnimDuration, framework.easing.cubic.out, 
			_moveMainScroll,
			function() {
				_stopAllAnimations();
				_mainScrollAnimating = false;
				_currZoomedItemIndex = -1;
				
				if(itemChanged || _currZoomedItemIndex !== _currentItemIndex) {
					self.updateCurrItem();
				}
				
				_shout('mainScrollAnimComplete');
			}
		);

		if(itemChanged) {
			self.updateCurrItem(true);
		}

		return itemChanged;
	},

	_calculateZoomLevel = function(touchesDistance) {
		return  1 / _startPointsDistance * touchesDistance * _startZoomLevel;
	},

	// Resets zoom if it's out of bounds
	_completeZoomGesture = function() {
		var destZoomLevel = _currZoomLevel,
			minZoomLevel = _getMinZoomLevel(),
			maxZoomLevel = _getMaxZoomLevel();

		if ( _currZoomLevel < minZoomLevel ) {
			destZoomLevel = minZoomLevel;
		} else if ( _currZoomLevel > maxZoomLevel ) {
			destZoomLevel = maxZoomLevel;
		}

		var destOpacity = 1,
			onUpdate,
			initialOpacity = _bgOpacity;

		if(_opacityChanged && !_isZoomingIn && !_wasOverInitialZoom && _currZoomLevel < minZoomLevel) {
			//_closedByScroll = true;
			self.close();
			return true;
		}

		if(_opacityChanged) {
			onUpdate = function(now) {
				_applyBgOpacity(  (destOpacity - initialOpacity) * now + initialOpacity );
			};
		}

		self.zoomTo(destZoomLevel, 0, 200,  framework.easing.cubic.out, onUpdate);
		return true;
	};


_registerModule('Gestures', {
	publicMethods: {

		initGestures: function() {

			// helper function that builds touch/pointer/mouse events
			var addEventNames = function(pref, down, move, up, cancel) {
				_dragStartEvent = pref + down;
				_dragMoveEvent = pref + move;
				_dragEndEvent = pref + up;
				if(cancel) {
					_dragCancelEvent = pref + cancel;
				} else {
					_dragCancelEvent = '';
				}
			};

			_pointerEventEnabled = _features.pointerEvent;
			if(_pointerEventEnabled && _features.touch) {
				// we don't need touch events, if browser supports pointer events
				_features.touch = false;
			}

			if(_pointerEventEnabled) {
				if(navigator.pointerEnabled) {
					addEventNames('pointer', 'down', 'move', 'up', 'cancel');
				} else {
					// IE10 pointer events are case-sensitive
					addEventNames('MSPointer', 'Down', 'Move', 'Up', 'Cancel');
				}
			} else if(_features.touch) {
				addEventNames('touch', 'start', 'move', 'end', 'cancel');
				_likelyTouchDevice = true;
			} else {
				addEventNames('mouse', 'down', 'move', 'up');	
			}

			_upMoveEvents = _dragMoveEvent + ' ' + _dragEndEvent  + ' ' +  _dragCancelEvent;
			_downEvents = _dragStartEvent;

			if(_pointerEventEnabled && !_likelyTouchDevice) {
				_likelyTouchDevice = (navigator.maxTouchPoints > 1) || (navigator.msMaxTouchPoints > 1);
			}
			// make variable public
			self.likelyTouchDevice = _likelyTouchDevice; 
			
			_globalEventHandlers[_dragStartEvent] = _onDragStart;
			_globalEventHandlers[_dragMoveEvent] = _onDragMove;
			_globalEventHandlers[_dragEndEvent] = _onDragRelease; // the Kraken

			if(_dragCancelEvent) {
				_globalEventHandlers[_dragCancelEvent] = _globalEventHandlers[_dragEndEvent];
			}

			// Bind mouse events on device with detected hardware touch support, in case it supports multiple types of input.
			if(_features.touch) {
				_downEvents += ' mousedown';
				_upMoveEvents += ' mousemove mouseup';
				_globalEventHandlers.mousedown = _globalEventHandlers[_dragStartEvent];
				_globalEventHandlers.mousemove = _globalEventHandlers[_dragMoveEvent];
				_globalEventHandlers.mouseup = _globalEventHandlers[_dragEndEvent];
			}

			if(!_likelyTouchDevice) {
				// don't allow pan to next slide from zoomed state on Desktop
				_options.allowPanToNext = false;
			}
		}

	}
});


/*>>gestures*/

/*>>show-hide-transition*/
/**
 * show-hide-transition.js:
 *
 * Manages initial opening or closing transition.
 *
 * If you're not planning to use transition for gallery at all,
 * you may set options hideAnimationDuration and showAnimationDuration to 0,
 * and just delete startAnimation function.
 * 
 */


var _showOrHideTimeout,
	_showOrHide = function(item, img, out, completeFn) {

		if(_showOrHideTimeout) {
			clearTimeout(_showOrHideTimeout);
		}

		_initialZoomRunning = true;
		_initialContentSet = true;
		
		// dimensions of small thumbnail {x:,y:,w:}.
		// Height is optional, as calculated based on large image.
		var thumbBounds; 
		if(item.initialLayout) {
			thumbBounds = item.initialLayout;
			item.initialLayout = null;
		} else {
			thumbBounds = _options.getThumbBoundsFn && _options.getThumbBoundsFn(_currentItemIndex);
		}

		var duration = out ? _options.hideAnimationDuration : _options.showAnimationDuration;

		var onComplete = function() {
			_stopAnimation('initialZoom');
			if(!out) {
				_applyBgOpacity(1);
				if(img) {
					img.style.display = 'block';
				}
				framework.addClass(template, 'pswp--animated-in');
				_shout('initialZoom' + (out ? 'OutEnd' : 'InEnd'));
			} else {
				self.template.removeAttribute('style');
				self.bg.removeAttribute('style');
			}

			if(completeFn) {
				completeFn();
			}
			_initialZoomRunning = false;
		};

		// if bounds aren't provided, just open gallery without animation
		if(!duration || !thumbBounds || thumbBounds.x === undefined) {

			_shout('initialZoom' + (out ? 'Out' : 'In') );

			_currZoomLevel = item.initialZoomLevel;
			_equalizePoints(_panOffset,  item.initialPosition );
			_applyCurrentZoomPan();

			template.style.opacity = out ? 0 : 1;
			_applyBgOpacity(1);

			if(duration) {
				setTimeout(function() {
					onComplete();
				}, duration);
			} else {
				onComplete();
			}

			return;
		}

		var startAnimation = function() {
			var closeWithRaf = _closedByScroll,
				fadeEverything = !self.currItem.src || self.currItem.loadError || _options.showHideOpacity;
			
			// apply hw-acceleration to image
			if(item.miniImg) {
				item.miniImg.style.webkitBackfaceVisibility = 'hidden';
			}

			if(!out) {
				_currZoomLevel = thumbBounds.w / item.w;
				_panOffset.x = thumbBounds.x;
				_panOffset.y = thumbBounds.y - _initalWindowScrollY;

				self[fadeEverything ? 'template' : 'bg'].style.opacity = 0.001;
				_applyCurrentZoomPan();
			}

			_registerStartAnimation('initialZoom');
			
			if(out && !closeWithRaf) {
				framework.removeClass(template, 'pswp--animated-in');
			}

			if(fadeEverything) {
				if(out) {
					framework[ (closeWithRaf ? 'remove' : 'add') + 'Class' ](template, 'pswp--animate_opacity');
				} else {
					setTimeout(function() {
						framework.addClass(template, 'pswp--animate_opacity');
					}, 30);
				}
			}

			_showOrHideTimeout = setTimeout(function() {

				_shout('initialZoom' + (out ? 'Out' : 'In') );
				

				if(!out) {

					// "in" animation always uses CSS transitions (instead of rAF).
					// CSS transition work faster here, 
					// as developer may also want to animate other things, 
					// like ui on top of sliding area, which can be animated just via CSS
					
					_currZoomLevel = item.initialZoomLevel;
					_equalizePoints(_panOffset,  item.initialPosition );
					_applyCurrentZoomPan();
					_applyBgOpacity(1);

					if(fadeEverything) {
						template.style.opacity = 1;
					} else {
						_applyBgOpacity(1);
					}

					_showOrHideTimeout = setTimeout(onComplete, duration + 20);
				} else {

					// "out" animation uses rAF only when PhotoSwipe is closed by browser scroll, to recalculate position
					var destZoomLevel = thumbBounds.w / item.w,
						initialPanOffset = {
							x: _panOffset.x,
							y: _panOffset.y
						},
						initialZoomLevel = _currZoomLevel,
						initalBgOpacity = _bgOpacity,
						onUpdate = function(now) {
							
							if(now === 1) {
								_currZoomLevel = destZoomLevel;
								_panOffset.x = thumbBounds.x;
								_panOffset.y = thumbBounds.y  - _currentWindowScrollY;
							} else {
								_currZoomLevel = (destZoomLevel - initialZoomLevel) * now + initialZoomLevel;
								_panOffset.x = (thumbBounds.x - initialPanOffset.x) * now + initialPanOffset.x;
								_panOffset.y = (thumbBounds.y - _currentWindowScrollY - initialPanOffset.y) * now + initialPanOffset.y;
							}
							
							_applyCurrentZoomPan();
							if(fadeEverything) {
								template.style.opacity = 1 - now;
							} else {
								_applyBgOpacity( initalBgOpacity - now * initalBgOpacity );
							}
						};

					if(closeWithRaf) {
						_animateProp('initialZoom', 0, 1, duration, framework.easing.cubic.out, onUpdate, onComplete);
					} else {
						onUpdate(1);
						_showOrHideTimeout = setTimeout(onComplete, duration + 20);
					}
				}
			
			}, out ? 25 : 90); // Main purpose of this delay is to give browser time to paint and
					// create composite layers of PhotoSwipe UI parts (background, controls, caption, arrows).
					// Which avoids lag at the beginning of scale transition.
		};
		startAnimation();

		
	};

/*>>show-hide-transition*/

/*>>items-controller*/
/**
*
* Controller manages gallery items, their dimensions, and their content.
* 
*/

var _items,
	_tempPanAreaSize = {},
	_imagesToAppendPool = [],
	_initialContentSet,
	_initialZoomRunning,
	_controllerDefaultOptions = {
		index: 0,
		errorMsg: '<div class="pswp__error-msg"><a href="%url%" target="_blank">The image</a> could not be loaded.</div>',
		forceProgressiveLoading: false, // TODO
		preload: [1,1],
		getNumItemsFn: function() {
			return _items.length;
		}
	};


var _getItemAt,
	_getNumItems,
	_initialIsLoop,
	_getZeroBounds = function() {
		return {
			center:{x:0,y:0}, 
			max:{x:0,y:0}, 
			min:{x:0,y:0}
		};
	},
	_calculateSingleItemPanBounds = function(item, realPanElementW, realPanElementH ) {
		var bounds = item.bounds;

		// position of element when it's centered
		bounds.center.x = Math.round((_tempPanAreaSize.x - realPanElementW) / 2);
		bounds.center.y = Math.round((_tempPanAreaSize.y - realPanElementH) / 2) + item.vGap.top;

		// maximum pan position
		bounds.max.x = (realPanElementW > _tempPanAreaSize.x) ? 
							Math.round(_tempPanAreaSize.x - realPanElementW) : 
							bounds.center.x;
		
		bounds.max.y = (realPanElementH > _tempPanAreaSize.y) ? 
							Math.round(_tempPanAreaSize.y - realPanElementH) + item.vGap.top : 
							bounds.center.y;
		
		// minimum pan position
		bounds.min.x = (realPanElementW > _tempPanAreaSize.x) ? 0 : bounds.center.x;
		bounds.min.y = (realPanElementH > _tempPanAreaSize.y) ? item.vGap.top : bounds.center.y;
	},
	_calculateItemSize = function(item, viewportSize, zoomLevel) {

		if (typeof item !== 'object') {
			return;
		}

		if (item.src && !item.loadError) {
			var isInitial = !zoomLevel;
			
			if(isInitial) {
				if(!item.vGap) {
					item.vGap = {top:0,bottom:0};
				}
				// allows overriding vertical margin for individual items
				_shout('parseVerticalMargin', item);
			}


			_tempPanAreaSize.x = viewportSize.x;
			_tempPanAreaSize.y = viewportSize.y - item.vGap.top - item.vGap.bottom;

			if (isInitial) {
				var hRatio = _tempPanAreaSize.x / item.w;
				var vRatio = _tempPanAreaSize.y / item.h;

				item.fitRatio = hRatio < vRatio ? hRatio : vRatio;
				//item.fillRatio = hRatio > vRatio ? hRatio : vRatio;

				var scaleMode = _options.scaleMode;

				if (scaleMode === 'orig') {
					zoomLevel = 1;
				} else if (scaleMode === 'fit') {
					zoomLevel = item.fitRatio;
				}

				if (zoomLevel > 1) {
					zoomLevel = 1;
				}

				item.initialZoomLevel = zoomLevel;
				
				if(!item.bounds) {
					// reuse bounds object
					item.bounds = _getZeroBounds(); 
				}
			}

			if(!zoomLevel) {
				return;
			}

			_calculateSingleItemPanBounds(item, item.w * zoomLevel, item.h * zoomLevel);

			if (isInitial && zoomLevel === item.initialZoomLevel) {
				item.initialPosition = item.bounds.center;
			}

			return item.bounds;
		} else {
			item.w = item.h = 0;
			item.initialZoomLevel = item.fitRatio = 1;
			item.bounds = _getZeroBounds();
			item.initialPosition = item.bounds.center;

			// if it's not image, we return zero bounds (content is not zoomable)
			return item.bounds;
		}
		
	},

	


	_appendImage = function(index, item, baseDiv, img, preventAnimation, keepPlaceholder) {
		

		if(item.loadError) {
			return;
		}

		if(img) {

			item.imageAppended = true;
			_setImageSize(item, img, (item === self.currItem && _renderMaxResolution) );
			
			baseDiv.appendChild(img);

			if(keepPlaceholder) {
				setTimeout(function() {
					if(item && item.loaded && item.placeholder) {
						item.placeholder.style.display = 'none';
						item.placeholder = null;
					}
				}, 500);
			}
		}
	},
	


	_preloadImage = function(item) {
		item.loading = true;
		item.loaded = false;
		var img = item.img = framework.createEl('pswp__img', 'img');
		var onComplete = function() {
			item.loading = false;
			item.loaded = true;

			if(item.loadComplete) {
				item.loadComplete(item);
			} else {
				item.img = null; // no need to store image object
			}
			img.onload = img.onerror = null;
			img = null;
		};
		img.onload = onComplete;
		img.onerror = function() {
			item.loadError = true;
			onComplete();
		};		

		img.src = item.src;// + '?a=' + Math.random();

		return img;
	},
	_checkForError = function(item, cleanUp) {
		if(item.src && item.loadError && item.container) {

			if(cleanUp) {
				item.container.innerHTML = '';
			}

			item.container.innerHTML = _options.errorMsg.replace('%url%',  item.src );
			return true;
			
		}
	},
	_setImageSize = function(item, img, maxRes) {
		if(!item.src) {
			return;
		}

		if(!img) {
			img = item.container.lastChild;
		}

		var w = maxRes ? item.w : Math.round(item.w * item.fitRatio),
			h = maxRes ? item.h : Math.round(item.h * item.fitRatio);
		
		if(item.placeholder && !item.loaded) {
			item.placeholder.style.width = w + 'px';
			item.placeholder.style.height = h + 'px';
		}

		img.style.width = w + 'px';
		img.style.height = h + 'px';
	},
	_appendImagesPool = function() {

		if(_imagesToAppendPool.length) {
			var poolItem;

			for(var i = 0; i < _imagesToAppendPool.length; i++) {
				poolItem = _imagesToAppendPool[i];
				if( poolItem.holder.index === poolItem.index ) {
					_appendImage(poolItem.index, poolItem.item, poolItem.baseDiv, poolItem.img, false, poolItem.clearPlaceholder);
				}
			}
			_imagesToAppendPool = [];
		}
	};
	


_registerModule('Controller', {

	publicMethods: {

		lazyLoadItem: function(index) {
			index = _getLoopedId(index);
			var item = _getItemAt(index);

			if(!item || ((item.loaded || item.loading) && !_itemsNeedUpdate)) {
				return;
			}

			_shout('gettingData', index, item);

			if (!item.src) {
				return;
			}

			_preloadImage(item);
		},
		initController: function() {
			framework.extend(_options, _controllerDefaultOptions, true);
			self.items = _items = items;
			_getItemAt = self.getItemAt;
			_getNumItems = _options.getNumItemsFn; //self.getNumItems;



			_initialIsLoop = _options.loop;
			if(_getNumItems() < 3) {
				_options.loop = false; // disable loop if less then 3 items
			}

			_listen('beforeChange', function(diff) {

				var p = _options.preload,
					isNext = diff === null ? true : (diff >= 0),
					preloadBefore = Math.min(p[0], _getNumItems() ),
					preloadAfter = Math.min(p[1], _getNumItems() ),
					i;


				for(i = 1; i <= (isNext ? preloadAfter : preloadBefore); i++) {
					self.lazyLoadItem(_currentItemIndex+i);
				}
				for(i = 1; i <= (isNext ? preloadBefore : preloadAfter); i++) {
					self.lazyLoadItem(_currentItemIndex-i);
				}
			});

			_listen('initialLayout', function() {
				self.currItem.initialLayout = _options.getThumbBoundsFn && _options.getThumbBoundsFn(_currentItemIndex);
			});

			_listen('mainScrollAnimComplete', _appendImagesPool);
			_listen('initialZoomInEnd', _appendImagesPool);



			_listen('destroy', function() {
				var item;
				for(var i = 0; i < _items.length; i++) {
					item = _items[i];
					// remove reference to DOM elements, for GC
					if(item.container) {
						item.container = null; 
					}
					if(item.placeholder) {
						item.placeholder = null;
					}
					if(item.img) {
						item.img = null;
					}
					if(item.preloader) {
						item.preloader = null;
					}
					if(item.loadError) {
						item.loaded = item.loadError = false;
					}
				}
				_imagesToAppendPool = null;
			});
		},


		getItemAt: function(index) {
			if (index >= 0) {
				return _items[index] !== undefined ? _items[index] : false;
			}
			return false;
		},

		allowProgressiveImg: function() {
			// 1. Progressive image loading isn't working on webkit/blink 
			//    when hw-acceleration (e.g. translateZ) is applied to IMG element.
			//    That's why in PhotoSwipe parent element gets zoom transform, not image itself.
			//    
			// 2. Progressive image loading sometimes blinks in webkit/blink when applying animation to parent element.
			//    That's why it's disabled on touch devices (mainly because of swipe transition)
			//    
			// 3. Progressive image loading sometimes doesn't work in IE (up to 11).

			// Don't allow progressive loading on non-large touch devices
			return _options.forceProgressiveLoading || !_likelyTouchDevice || _options.mouseUsed || screen.width > 1200; 
			// 1200 - to eliminate touch devices with large screen (like Chromebook Pixel)
		},

		setContent: function(holder, index) {

			if(_options.loop) {
				index = _getLoopedId(index);
			}

			var prevItem = self.getItemAt(holder.index);
			if(prevItem) {
				prevItem.container = null;
			}
	
			var item = self.getItemAt(index),
				img;
			
			if(!item) {
				holder.el.innerHTML = '';
				return;
			}

			// allow to override data
			_shout('gettingData', index, item);

			holder.index = index;
			holder.item = item;

			// base container DIV is created only once for each of 3 holders
			var baseDiv = item.container = framework.createEl('pswp__zoom-wrap'); 

			

			if(!item.src && item.html) {
				if(item.html.tagName) {
					baseDiv.appendChild(item.html);
				} else {
					baseDiv.innerHTML = item.html;
				}
			}

			_checkForError(item);

			_calculateItemSize(item, _viewportSize);
			
			if(item.src && !item.loadError && !item.loaded) {

				item.loadComplete = function(item) {

					// gallery closed before image finished loading
					if(!_isOpen) {
						return;
					}

					// check if holder hasn't changed while image was loading
					if(holder && holder.index === index ) {
						if( _checkForError(item, true) ) {
							item.loadComplete = item.img = null;
							_calculateItemSize(item, _viewportSize);
							_applyZoomPanToItem(item);

							if(holder.index === _currentItemIndex) {
								// recalculate dimensions
								self.updateCurrZoomItem();
							}
							return;
						}
						if( !item.imageAppended ) {
							if(_features.transform && (_mainScrollAnimating || _initialZoomRunning) ) {
								_imagesToAppendPool.push({
									item:item,
									baseDiv:baseDiv,
									img:item.img,
									index:index,
									holder:holder,
									clearPlaceholder:true
								});
							} else {
								_appendImage(index, item, baseDiv, item.img, _mainScrollAnimating || _initialZoomRunning, true);
							}
						} else {
							// remove preloader & mini-img
							if(!_initialZoomRunning && item.placeholder) {
								item.placeholder.style.display = 'none';
								item.placeholder = null;
							}
						}
					}

					item.loadComplete = null;
					item.img = null; // no need to store image element after it's added

					_shout('imageLoadComplete', index, item);
				};

				if(framework.features.transform) {
					
					var placeholderClassName = 'pswp__img pswp__img--placeholder'; 
					placeholderClassName += (item.msrc ? '' : ' pswp__img--placeholder--blank');

					var placeholder = framework.createEl(placeholderClassName, item.msrc ? 'img' : '');
					if(item.msrc) {
						placeholder.src = item.msrc;
					}
					
					_setImageSize(item, placeholder);

					baseDiv.appendChild(placeholder);
					item.placeholder = placeholder;

				}
				

				

				if(!item.loading) {
					_preloadImage(item);
				}


				if( self.allowProgressiveImg() ) {
					// just append image
					if(!_initialContentSet && _features.transform) {
						_imagesToAppendPool.push({
							item:item, 
							baseDiv:baseDiv, 
							img:item.img, 
							index:index, 
							holder:holder
						});
					} else {
						_appendImage(index, item, baseDiv, item.img, true, true);
					}
				}
				
			} else if(item.src && !item.loadError) {
				// image object is created every time, due to bugs of image loading & delay when switching images
				img = framework.createEl('pswp__img', 'img');
				img.style.opacity = 1;
				img.src = item.src;
				_setImageSize(item, img);
				_appendImage(index, item, baseDiv, img, true);
			}
			

			if(!_initialContentSet && index === _currentItemIndex) {
				_currZoomElementStyle = baseDiv.style;
				_showOrHide(item, (img ||item.img) );
			} else {
				_applyZoomPanToItem(item);
			}

			holder.el.innerHTML = '';
			holder.el.appendChild(baseDiv);
		},

		cleanSlide: function( item ) {
			if(item.img ) {
				item.img.onload = item.img.onerror = null;
			}
			item.loaded = item.loading = item.img = item.imageAppended = false;
		}

	}
});

/*>>items-controller*/

/*>>tap*/
/**
 * tap.js:
 *
 * Displatches tap and double-tap events.
 * 
 */

var tapTimer,
	tapReleasePoint = {},
	_dispatchTapEvent = function(origEvent, releasePoint, pointerType) {		
		var e = document.createEvent( 'CustomEvent' ),
			eDetail = {
				origEvent:origEvent, 
				target:origEvent.target, 
				releasePoint: releasePoint, 
				pointerType:pointerType || 'touch'
			};

		e.initCustomEvent( 'pswpTap', true, true, eDetail );
		origEvent.target.dispatchEvent(e);
	};

_registerModule('Tap', {
	publicMethods: {
		initTap: function() {
			_listen('firstTouchStart', self.onTapStart);
			_listen('touchRelease', self.onTapRelease);
			_listen('destroy', function() {
				tapReleasePoint = {};
				tapTimer = null;
			});
		},
		onTapStart: function(touchList) {
			if(touchList.length > 1) {
				clearTimeout(tapTimer);
				tapTimer = null;
			}
		},
		onTapRelease: function(e, releasePoint) {
			if(!releasePoint) {
				return;
			}

			if(!_moved && !_isMultitouch && !_numAnimations) {
				var p0 = releasePoint;
				if(tapTimer) {
					clearTimeout(tapTimer);
					tapTimer = null;

					// Check if taped on the same place
					if ( _isNearbyPoints(p0, tapReleasePoint) ) {
						_shout('doubleTap', p0);
						return;
					}
				}

				if(releasePoint.type === 'mouse') {
					_dispatchTapEvent(e, releasePoint, 'mouse');
					return;
				}

				var clickedTagName = e.target.tagName.toUpperCase();
				// avoid double tap delay on buttons and elements that have class pswp__single-tap
				if(clickedTagName === 'BUTTON' || framework.hasClass(e.target, 'pswp__single-tap') ) {
					_dispatchTapEvent(e, releasePoint);
					return;
				}

				_equalizePoints(tapReleasePoint, p0);

				tapTimer = setTimeout(function() {
					_dispatchTapEvent(e, releasePoint);
					tapTimer = null;
				}, 300);
			}
		}
	}
});

/*>>tap*/

/*>>desktop-zoom*/
/**
 *
 * desktop-zoom.js:
 *
 * - Binds mousewheel event for paning zoomed image.
 * - Manages "dragging", "zoomed-in", "zoom-out" classes.
 *   (which are used for cursors and zoom icon)
 * - Adds toggleDesktopZoom function.
 * 
 */

var _wheelDelta;
	
_registerModule('DesktopZoom', {

	publicMethods: {

		initDesktopZoom: function() {

			if(_oldIE) {
				// no zoom for old IE (<=8)
				return;
			}

			if(_likelyTouchDevice) {
				// if detected hardware touch support, we wait until mouse is used,
				// and only then apply desktop-zoom features
				_listen('mouseUsed', function() {
					self.setupDesktopZoom();
				});
			} else {
				self.setupDesktopZoom(true);
			}

		},

		setupDesktopZoom: function(onInit) {

			_wheelDelta = {};

			var events = 'wheel mousewheel DOMMouseScroll';
			
			_listen('bindEvents', function() {
				framework.bind(template, events,  self.handleMouseWheel);
			});

			_listen('unbindEvents', function() {
				if(_wheelDelta) {
					framework.unbind(template, events, self.handleMouseWheel);
				}
			});

			self.mouseZoomedIn = false;

			var hasDraggingClass,
				updateZoomable = function() {
					if(self.mouseZoomedIn) {
						framework.removeClass(template, 'pswp--zoomed-in');
						self.mouseZoomedIn = false;
					}
					if(_currZoomLevel < 1) {
						framework.addClass(template, 'pswp--zoom-allowed');
					} else {
						framework.removeClass(template, 'pswp--zoom-allowed');
					}
					removeDraggingClass();
				},
				removeDraggingClass = function() {
					if(hasDraggingClass) {
						framework.removeClass(template, 'pswp--dragging');
						hasDraggingClass = false;
					}
				};

			_listen('resize' , updateZoomable);
			_listen('afterChange' , updateZoomable);
			_listen('pointerDown', function() {
				if(self.mouseZoomedIn) {
					hasDraggingClass = true;
					framework.addClass(template, 'pswp--dragging');
				}
			});
			_listen('pointerUp', removeDraggingClass);

			if(!onInit) {
				updateZoomable();
			}
			
		},

		handleMouseWheel: function(e) {

			if(_currZoomLevel <= self.currItem.fitRatio) {
				if( _options.modal ) {

					if (!_options.closeOnScroll || _numAnimations || _isDragging) {
						e.preventDefault();
					} else if(_transformKey && Math.abs(e.deltaY) > 2) {
						// close PhotoSwipe
						// if browser supports transforms & scroll changed enough
						_closedByScroll = true;
						self.close();
					}

				}
				return true;
			}

			// allow just one event to fire
			e.stopPropagation();

			// https://developer.mozilla.org/en-US/docs/Web/Events/wheel
			_wheelDelta.x = 0;

			if('deltaX' in e) {
				if(e.deltaMode === 1 /* DOM_DELTA_LINE */) {
					// 18 - average line height
					_wheelDelta.x = e.deltaX * 18;
					_wheelDelta.y = e.deltaY * 18;
				} else {
					_wheelDelta.x = e.deltaX;
					_wheelDelta.y = e.deltaY;
				}
			} else if('wheelDelta' in e) {
				if(e.wheelDeltaX) {
					_wheelDelta.x = -0.16 * e.wheelDeltaX;
				}
				if(e.wheelDeltaY) {
					_wheelDelta.y = -0.16 * e.wheelDeltaY;
				} else {
					_wheelDelta.y = -0.16 * e.wheelDelta;
				}
			} else if('detail' in e) {
				_wheelDelta.y = e.detail;
			} else {
				return;
			}

			_calculatePanBounds(_currZoomLevel, true);

			var newPanX = _panOffset.x - _wheelDelta.x,
				newPanY = _panOffset.y - _wheelDelta.y;

			// only prevent scrolling in nonmodal mode when not at edges
			if (_options.modal ||
				(
				newPanX <= _currPanBounds.min.x && newPanX >= _currPanBounds.max.x &&
				newPanY <= _currPanBounds.min.y && newPanY >= _currPanBounds.max.y
				) ) {
				e.preventDefault();
			}

			// TODO: use rAF instead of mousewheel?
			self.panTo(newPanX, newPanY);
		},

		toggleDesktopZoom: function(centerPoint) {
			centerPoint = centerPoint || {x:_viewportSize.x/2 + _offset.x, y:_viewportSize.y/2 + _offset.y };

			var doubleTapZoomLevel = _options.getDoubleTapZoom(true, self.currItem);
			var zoomOut = _currZoomLevel === doubleTapZoomLevel;
			
			self.mouseZoomedIn = !zoomOut;

			self.zoomTo(zoomOut ? self.currItem.initialZoomLevel : doubleTapZoomLevel, centerPoint, 333);
			framework[ (!zoomOut ? 'add' : 'remove') + 'Class'](template, 'pswp--zoomed-in');
		}

	}
});


/*>>desktop-zoom*/

/*>>history*/
/**
 *
 * history.js:
 *
 * - Back button to close gallery.
 * 
 * - Unique URL for each slide: example.com/&pid=1&gid=3
 *   (where PID is picture index, and GID and gallery index)
 *   
 * - Switch URL when slides change.
 * 
 */


var _historyDefaultOptions = {
	history: true,
	galleryUID: 1
};

var _historyUpdateTimeout,
	_hashChangeTimeout,
	_hashAnimCheckTimeout,
	_hashChangedByScript,
	_hashChangedByHistory,
	_hashReseted,
	_initialHash,
	_historyChanged,
	_closedFromURL,
	_urlChangedOnce,
	_windowLoc,

	_supportsPushState,

	_getHash = function() {
		return _windowLoc.hash.substring(1);
	},
	_cleanHistoryTimeouts = function() {

		if(_historyUpdateTimeout) {
			clearTimeout(_historyUpdateTimeout);
		}

		if(_hashAnimCheckTimeout) {
			clearTimeout(_hashAnimCheckTimeout);
		}
	},

	// pid - Picture index
	// gid - Gallery index
	_parseItemIndexFromURL = function() {
		var hash = _getHash(),
			params = {};

		if(hash.length < 5) { // pid=1
			return params;
		}

		var i, vars = hash.split('&');
		for (i = 0; i < vars.length; i++) {
			if(!vars[i]) {
				continue;
			}
			var pair = vars[i].split('=');	
			if(pair.length < 2) {
				continue;
			}
			params[pair[0]] = pair[1];
		}
		if(_options.galleryPIDs) {
			// detect custom pid in hash and search for it among the items collection
			var searchfor = params.pid;
			params.pid = 0; // if custom pid cannot be found, fallback to the first item
			for(i = 0; i < _items.length; i++) {
				if(_items[i].pid === searchfor) {
					params.pid = i;
					break;
				}
			}
		} else {
			params.pid = parseInt(params.pid,10)-1;
		}
		if( params.pid < 0 ) {
			params.pid = 0;
		}
		return params;
	},
	_updateHash = function() {

		if(_hashAnimCheckTimeout) {
			clearTimeout(_hashAnimCheckTimeout);
		}


		if(_numAnimations || _isDragging) {
			// changing browser URL forces layout/paint in some browsers, which causes noticable lag during animation
			// that's why we update hash only when no animations running
			_hashAnimCheckTimeout = setTimeout(_updateHash, 500);
			return;
		}
		
		if(_hashChangedByScript) {
			clearTimeout(_hashChangeTimeout);
		} else {
			_hashChangedByScript = true;
		}


		var pid = (_currentItemIndex + 1);
		var item = _getItemAt( _currentItemIndex );
		if(item.hasOwnProperty('pid')) {
			// carry forward any custom pid assigned to the item
			pid = item.pid;
		}
		var newHash = _initialHash + '&'  +  'gid=' + _options.galleryUID + '&' + 'pid=' + pid;

		if(!_historyChanged) {
			if(_windowLoc.hash.indexOf(newHash) === -1) {
				_urlChangedOnce = true;
			}
			// first time - add new hisory record, then just replace
		}

		var newURL = _windowLoc.href.split('#')[0] + '#' +  newHash;

		if( _supportsPushState ) {

			if('#' + newHash !== window.location.hash) {
				history[_historyChanged ? 'replaceState' : 'pushState']('', document.title, newURL);
			}

		} else {
			if(_historyChanged) {
				_windowLoc.replace( newURL );
			} else {
				_windowLoc.hash = newHash;
			}
		}
		
		

		_historyChanged = true;
		_hashChangeTimeout = setTimeout(function() {
			_hashChangedByScript = false;
		}, 60);
	};



	

_registerModule('History', {

	

	publicMethods: {
		initHistory: function() {

			framework.extend(_options, _historyDefaultOptions, true);

			if( !_options.history ) {
				return;
			}


			_windowLoc = window.location;
			_urlChangedOnce = false;
			_closedFromURL = false;
			_historyChanged = false;
			_initialHash = _getHash();
			_supportsPushState = ('pushState' in history);


			if(_initialHash.indexOf('gid=') > -1) {
				_initialHash = _initialHash.split('&gid=')[0];
				_initialHash = _initialHash.split('?gid=')[0];
			}
			

			_listen('afterChange', self.updateURL);
			_listen('unbindEvents', function() {
				framework.unbind(window, 'hashchange', self.onHashChange);
			});


			var returnToOriginal = function() {
				_hashReseted = true;
				if(!_closedFromURL) {

					if(_urlChangedOnce) {
						history.back();
					} else {

						if(_initialHash) {
							_windowLoc.hash = _initialHash;
						} else {
							if (_supportsPushState) {

								// remove hash from url without refreshing it or scrolling to top
								history.pushState('', document.title,  _windowLoc.pathname + _windowLoc.search );
							} else {
								_windowLoc.hash = '';
							}
						}
					}
					
				}

				_cleanHistoryTimeouts();
			};


			_listen('unbindEvents', function() {
				if(_closedByScroll) {
					// if PhotoSwipe is closed by scroll, we go "back" before the closing animation starts
					// this is done to keep the scroll position
					returnToOriginal();
				}
			});
			_listen('destroy', function() {
				if(!_hashReseted) {
					returnToOriginal();
				}
			});
			_listen('firstUpdate', function() {
				_currentItemIndex = _parseItemIndexFromURL().pid;
			});

			

			
			var index = _initialHash.indexOf('pid=');
			if(index > -1) {
				_initialHash = _initialHash.substring(0, index);
				if(_initialHash.slice(-1) === '&') {
					_initialHash = _initialHash.slice(0, -1);
				}
			}
			

			setTimeout(function() {
				if(_isOpen) { // hasn't destroyed yet
					framework.bind(window, 'hashchange', self.onHashChange);
				}
			}, 40);
			
		},
		onHashChange: function() {

			if(_getHash() === _initialHash) {

				_closedFromURL = true;
				self.close();
				return;
			}
			if(!_hashChangedByScript) {

				_hashChangedByHistory = true;
				self.goTo( _parseItemIndexFromURL().pid );
				_hashChangedByHistory = false;
			}
			
		},
		updateURL: function() {

			// Delay the update of URL, to avoid lag during transition, 
			// and to not to trigger actions like "refresh page sound" or "blinking favicon" to often
			
			_cleanHistoryTimeouts();
			

			if(_hashChangedByHistory) {
				return;
			}

			if(!_historyChanged) {
				_updateHash(); // first time
			} else {
				_historyUpdateTimeout = setTimeout(_updateHash, 800);
			}
		}
	
	}
});


/*>>history*/
	framework.extend(self, publicMethods); };
	return PhotoSwipe;
});

/***/ }),
/* 84 */
/***/ (function(module, exports, __webpack_require__) {

var __WEBPACK_AMD_DEFINE_FACTORY__, __WEBPACK_AMD_DEFINE_RESULT__;/*! PhotoSwipe Default UI - 4.1.3-rc.1 - 2017-09-23
* http://photoswipe.com
* Copyright (c) 2017 Dmitry Semenov; */
/**
*
* UI on top of main sliding area (caption, arrows, close button, etc.).
* Built just using public methods/properties of PhotoSwipe.
* 
*/
(function (root, factory) { 
	if (true) {
		!(__WEBPACK_AMD_DEFINE_FACTORY__ = (factory),
				__WEBPACK_AMD_DEFINE_RESULT__ = (typeof __WEBPACK_AMD_DEFINE_FACTORY__ === 'function' ?
				(__WEBPACK_AMD_DEFINE_FACTORY__.call(exports, __webpack_require__, exports, module)) :
				__WEBPACK_AMD_DEFINE_FACTORY__),
				__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));
	} else if (typeof exports === 'object') {
		module.exports = factory();
	} else {
		root.PhotoSwipeUI_Default = factory();
	}
})(this, function () {

	'use strict';



var PhotoSwipeUI_Default =
 function(pswp, framework) {

	var ui = this;
	var _overlayUIUpdated = false,
		_controlsVisible = true,
		_fullscrenAPI,
		_controls,
		_captionContainer,
		_fakeCaptionContainer,
		_indexIndicator,
		_shareButton,
		_shareModal,
		_shareModalHidden = true,
		_initalCloseOnScrollValue,
		_isIdle,
		_listen,

		_loadingIndicator,
		_loadingIndicatorHidden,
		_loadingIndicatorTimeout,

		_galleryHasOneSlide,

		_options,
		_defaultUIOptions = {
			barsSize: {top:44, bottom:'auto'},
			closeElClasses: ['item', 'caption', 'zoom-wrap', 'ui', 'top-bar'], 
			timeToIdle: 4000, 
			timeToIdleOutside: 1000,
			loadingIndicatorDelay: 1000, // 2s
			
			addCaptionHTMLFn: function(item, captionEl /*, isFake */) {
				if(!item.title) {
					captionEl.children[0].innerHTML = '';
					return false;
				}
				captionEl.children[0].innerHTML = item.title;
				return true;
			},

			closeEl:true,
			captionEl: true,
			fullscreenEl: true,
			zoomEl: true,
			shareEl: true,
			counterEl: true,
			arrowEl: true,
			preloaderEl: true,

			tapToClose: false,
			tapToToggleControls: true,

			clickToCloseNonZoomable: true,

			shareButtons: [
				{id:'facebook', label:'Share on Facebook', url:'https://www.facebook.com/sharer/sharer.php?u={{url}}'},
				{id:'twitter', label:'Tweet', url:'https://twitter.com/intent/tweet?text={{text}}&url={{url}}'},
				{id:'pinterest', label:'Pin it', url:'http://www.pinterest.com/pin/create/button/'+
													'?url={{url}}&media={{image_url}}&description={{text}}'},
				{id:'download', label:'Download image', url:'{{raw_image_url}}', download:true}
			],
			getImageURLForShare: function( /* shareButtonData */ ) {
				return pswp.currItem.src || '';
			},
			getPageURLForShare: function( /* shareButtonData */ ) {
				return window.location.href;
			},
			getTextForShare: function( /* shareButtonData */ ) {
				return pswp.currItem.title || '';
			},
				
			indexIndicatorSep: ' / ',
			fitControlsWidth: 1200

		},
		_blockControlsTap,
		_blockControlsTapTimeout;



	var _onControlsTap = function(e) {
			if(_blockControlsTap) {
				return true;
			}


			e = e || window.event;

			if(_options.timeToIdle && _options.mouseUsed && !_isIdle) {
				// reset idle timer
				_onIdleMouseMove();
			}


			var target = e.target || e.srcElement,
				uiElement,
				clickedClass = target.getAttribute('class') || '',
				found;

			for(var i = 0; i < _uiElements.length; i++) {
				uiElement = _uiElements[i];
				if(uiElement.onTap && clickedClass.indexOf('pswp__' + uiElement.name ) > -1 ) {
					uiElement.onTap();
					found = true;

				}
			}

			if(found) {
				if(e.stopPropagation) {
					e.stopPropagation();
				}
				_blockControlsTap = true;

				// Some versions of Android don't prevent ghost click event 
				// when preventDefault() was called on touchstart and/or touchend.
				// 
				// This happens on v4.3, 4.2, 4.1, 
				// older versions strangely work correctly, 
				// but just in case we add delay on all of them)	
				var tapDelay = framework.features.isOldAndroid ? 600 : 30;
				_blockControlsTapTimeout = setTimeout(function() {
					_blockControlsTap = false;
				}, tapDelay);
			}

		},
		_fitControlsInViewport = function() {
			return !pswp.likelyTouchDevice || _options.mouseUsed || screen.width > _options.fitControlsWidth;
		},
		_togglePswpClass = function(el, cName, add) {
			framework[ (add ? 'add' : 'remove') + 'Class' ](el, 'pswp__' + cName);
		},

		// add class when there is just one item in the gallery
		// (by default it hides left/right arrows and 1ofX counter)
		_countNumItems = function() {
			var hasOneSlide = (_options.getNumItemsFn() === 1);

			if(hasOneSlide !== _galleryHasOneSlide) {
				_togglePswpClass(_controls, 'ui--one-slide', hasOneSlide);
				_galleryHasOneSlide = hasOneSlide;
			}
		},
		_toggleShareModalClass = function() {
			_togglePswpClass(_shareModal, 'share-modal--hidden', _shareModalHidden);
		},
		_toggleShareModal = function() {

			_shareModalHidden = !_shareModalHidden;
			
			
			if(!_shareModalHidden) {
				_toggleShareModalClass();
				setTimeout(function() {
					if(!_shareModalHidden) {
						framework.addClass(_shareModal, 'pswp__share-modal--fade-in');
					}
				}, 30);
			} else {
				framework.removeClass(_shareModal, 'pswp__share-modal--fade-in');
				setTimeout(function() {
					if(_shareModalHidden) {
						_toggleShareModalClass();
					}
				}, 300);
			}
			
			if(!_shareModalHidden) {
				_updateShareURLs();
			}
			return false;
		},

		_openWindowPopup = function(e) {
			e = e || window.event;
			var target = e.target || e.srcElement;

			pswp.shout('shareLinkClick', e, target);

			if(!target.href) {
				return false;
			}

			if( target.hasAttribute('download') ) {
				return true;
			}

			window.open(target.href, 'pswp_share', 'scrollbars=yes,resizable=yes,toolbar=no,'+
										'location=yes,width=550,height=420,top=100,left=' + 
										(window.screen ? Math.round(screen.width / 2 - 275) : 100)  );

			if(!_shareModalHidden) {
				_toggleShareModal();
			}
			
			return false;
		},
		_updateShareURLs = function() {
			var shareButtonOut = '',
				shareButtonData,
				shareURL,
				image_url,
				page_url,
				share_text;

			for(var i = 0; i < _options.shareButtons.length; i++) {
				shareButtonData = _options.shareButtons[i];

				image_url = _options.getImageURLForShare(shareButtonData);
				page_url = _options.getPageURLForShare(shareButtonData);
				share_text = _options.getTextForShare(shareButtonData);

				shareURL = shareButtonData.url.replace('{{url}}', encodeURIComponent(page_url) )
									.replace('{{image_url}}', encodeURIComponent(image_url) )
									.replace('{{raw_image_url}}', image_url )
									.replace('{{text}}', encodeURIComponent(share_text) );

				shareButtonOut += '<a href="' + shareURL + '" target="_blank" '+
									'class="pswp__share--' + shareButtonData.id + '"' +
									(shareButtonData.download ? 'download' : '') + '>' + 
									shareButtonData.label + '</a>';

				if(_options.parseShareButtonOut) {
					shareButtonOut = _options.parseShareButtonOut(shareButtonData, shareButtonOut);
				}
			}
			_shareModal.children[0].innerHTML = shareButtonOut;
			_shareModal.children[0].onclick = _openWindowPopup;

		},
		_hasCloseClass = function(target) {
			for(var  i = 0; i < _options.closeElClasses.length; i++) {
				if( framework.hasClass(target, 'pswp__' + _options.closeElClasses[i]) ) {
					return true;
				}
			}
		},
		_idleInterval,
		_idleTimer,
		_idleIncrement = 0,
		_onIdleMouseMove = function() {
			clearTimeout(_idleTimer);
			_idleIncrement = 0;
			if(_isIdle) {
				ui.setIdle(false);
			}
		},
		_onMouseLeaveWindow = function(e) {
			e = e ? e : window.event;
			var from = e.relatedTarget || e.toElement;
			if (!from || from.nodeName === 'HTML') {
				clearTimeout(_idleTimer);
				_idleTimer = setTimeout(function() {
					ui.setIdle(true);
				}, _options.timeToIdleOutside);
			}
		},
		_setupFullscreenAPI = function() {
			if(_options.fullscreenEl && !framework.features.isOldAndroid) {
				if(!_fullscrenAPI) {
					_fullscrenAPI = ui.getFullscreenAPI();
				}
				if(_fullscrenAPI) {
					framework.bind(document, _fullscrenAPI.eventK, ui.updateFullscreen);
					ui.updateFullscreen();
					framework.addClass(pswp.template, 'pswp--supports-fs');
				} else {
					framework.removeClass(pswp.template, 'pswp--supports-fs');
				}
			}
		},
		_setupLoadingIndicator = function() {
			// Setup loading indicator
			if(_options.preloaderEl) {
			
				_toggleLoadingIndicator(true);

				_listen('beforeChange', function() {

					clearTimeout(_loadingIndicatorTimeout);

					// display loading indicator with delay
					_loadingIndicatorTimeout = setTimeout(function() {

						if(pswp.currItem && pswp.currItem.loading) {

							if( !pswp.allowProgressiveImg() || (pswp.currItem.img && !pswp.currItem.img.naturalWidth)  ) {
								// show preloader if progressive loading is not enabled, 
								// or image width is not defined yet (because of slow connection)
								_toggleLoadingIndicator(false); 
								// items-controller.js function allowProgressiveImg
							}
							
						} else {
							_toggleLoadingIndicator(true); // hide preloader
						}

					}, _options.loadingIndicatorDelay);
					
				});
				_listen('imageLoadComplete', function(index, item) {
					if(pswp.currItem === item) {
						_toggleLoadingIndicator(true);
					}
				});

			}
		},
		_toggleLoadingIndicator = function(hide) {
			if( _loadingIndicatorHidden !== hide ) {
				_togglePswpClass(_loadingIndicator, 'preloader--active', !hide);
				_loadingIndicatorHidden = hide;
			}
		},
		_applyNavBarGaps = function(item) {
			var gap = item.vGap;

			if( _fitControlsInViewport() ) {
				
				var bars = _options.barsSize; 
				if(_options.captionEl && bars.bottom === 'auto') {
					if(!_fakeCaptionContainer) {
						_fakeCaptionContainer = framework.createEl('pswp__caption pswp__caption--fake');
						_fakeCaptionContainer.appendChild( framework.createEl('pswp__caption__center') );
						_controls.insertBefore(_fakeCaptionContainer, _captionContainer);
						framework.addClass(_controls, 'pswp__ui--fit');
					}
					if( _options.addCaptionHTMLFn(item, _fakeCaptionContainer, true) ) {

						var captionSize = _fakeCaptionContainer.clientHeight;
						gap.bottom = parseInt(captionSize,10) || 44;
					} else {
						gap.bottom = bars.top; // if no caption, set size of bottom gap to size of top
					}
				} else {
					gap.bottom = bars.bottom === 'auto' ? 0 : bars.bottom;
				}
				
				// height of top bar is static, no need to calculate it
				gap.top = bars.top;
			} else {
				gap.top = gap.bottom = 0;
			}
		},
		_setupIdle = function() {
			// Hide controls when mouse is used
			if(_options.timeToIdle) {
				_listen('mouseUsed', function() {
					
					framework.bind(document, 'mousemove', _onIdleMouseMove);
					framework.bind(document, 'mouseout', _onMouseLeaveWindow);

					_idleInterval = setInterval(function() {
						_idleIncrement++;
						if(_idleIncrement === 2) {
							ui.setIdle(true);
						}
					}, _options.timeToIdle / 2);
				});
			}
		},
		_setupHidingControlsDuringGestures = function() {

			// Hide controls on vertical drag
			_listen('onVerticalDrag', function(now) {
				if(_controlsVisible && now < 0.95) {
					ui.hideControls();
				} else if(!_controlsVisible && now >= 0.95) {
					ui.showControls();
				}
			});

			// Hide controls when pinching to close
			var pinchControlsHidden;
			_listen('onPinchClose' , function(now) {
				if(_controlsVisible && now < 0.9) {
					ui.hideControls();
					pinchControlsHidden = true;
				} else if(pinchControlsHidden && !_controlsVisible && now > 0.9) {
					ui.showControls();
				}
			});

			_listen('zoomGestureEnded', function() {
				pinchControlsHidden = false;
				if(pinchControlsHidden && !_controlsVisible) {
					ui.showControls();
				}
			});

		};



	var _uiElements = [
		{ 
			name: 'caption', 
			option: 'captionEl',
			onInit: function(el) {  
				_captionContainer = el; 
			} 
		},
		{ 
			name: 'share-modal', 
			option: 'shareEl',
			onInit: function(el) {  
				_shareModal = el;
			},
			onTap: function() {
				_toggleShareModal();
			} 
		},
		{ 
			name: 'button--share', 
			option: 'shareEl',
			onInit: function(el) { 
				_shareButton = el;
			},
			onTap: function() {
				_toggleShareModal();
			} 
		},
		{ 
			name: 'button--zoom', 
			option: 'zoomEl',
			onTap: pswp.toggleDesktopZoom
		},
		{ 
			name: 'counter', 
			option: 'counterEl',
			onInit: function(el) {  
				_indexIndicator = el;
			} 
		},
		{ 
			name: 'button--close', 
			option: 'closeEl',
			onTap: pswp.close
		},
		{ 
			name: 'button--arrow--left', 
			option: 'arrowEl',
			onTap: pswp.prev
		},
		{ 
			name: 'button--arrow--right', 
			option: 'arrowEl',
			onTap: pswp.next
		},
		{ 
			name: 'button--fs', 
			option: 'fullscreenEl',
			onTap: function() {  
				if(_fullscrenAPI.isFullscreen()) {
					_fullscrenAPI.exit();
				} else {
					_fullscrenAPI.enter();
				}
			} 
		},
		{ 
			name: 'preloader', 
			option: 'preloaderEl',
			onInit: function(el) {  
				_loadingIndicator = el;
			} 
		}

	];

	var _setupUIElements = function() {
		var item,
			classAttr,
			uiElement;

		var loopThroughChildElements = function(sChildren) {
			if(!sChildren) {
				return;
			}

			var l = sChildren.length;
			for(var i = 0; i < l; i++) {
				item = sChildren[i];
				classAttr = item.className;

				for(var a = 0; a < _uiElements.length; a++) {
					uiElement = _uiElements[a];

					if(classAttr.indexOf('pswp__' + uiElement.name) > -1  ) {

						if( _options[uiElement.option] ) { // if element is not disabled from options
							
							framework.removeClass(item, 'pswp__element--disabled');
							if(uiElement.onInit) {
								uiElement.onInit(item);
							}
							
							//item.style.display = 'block';
						} else {
							framework.addClass(item, 'pswp__element--disabled');
							//item.style.display = 'none';
						}
					}
				}
			}
		};
		loopThroughChildElements(_controls.children);

		var topBar =  framework.getChildByClass(_controls, 'pswp__top-bar');
		if(topBar) {
			loopThroughChildElements( topBar.children );
		}
	};


	

	ui.init = function() {

		// extend options
		framework.extend(pswp.options, _defaultUIOptions, true);

		// create local link for fast access
		_options = pswp.options;

		// find pswp__ui element
		_controls = framework.getChildByClass(pswp.scrollWrap, 'pswp__ui');

		// create local link
		_listen = pswp.listen;


		_setupHidingControlsDuringGestures();

		// update controls when slides change
		_listen('beforeChange', ui.update);

		// toggle zoom on double-tap
		_listen('doubleTap', function(point) {
			var initialZoomLevel = pswp.currItem.initialZoomLevel;
			if(pswp.getZoomLevel() !== initialZoomLevel) {
				pswp.zoomTo(initialZoomLevel, point, 333);
			} else {
				pswp.zoomTo(_options.getDoubleTapZoom(false, pswp.currItem), point, 333);
			}
		});

		// Allow text selection in caption
		_listen('preventDragEvent', function(e, isDown, preventObj) {
			var t = e.target || e.srcElement;
			if(
				t && 
				t.getAttribute('class') && e.type.indexOf('mouse') > -1 && 
				( t.getAttribute('class').indexOf('__caption') > 0 || (/(SMALL|STRONG|EM)/i).test(t.tagName) ) 
			) {
				preventObj.prevent = false;
			}
		});

		// bind events for UI
		_listen('bindEvents', function() {
			framework.bind(_controls, 'pswpTap click', _onControlsTap);
			framework.bind(pswp.scrollWrap, 'pswpTap', ui.onGlobalTap);

			if(!pswp.likelyTouchDevice) {
				framework.bind(pswp.scrollWrap, 'mouseover', ui.onMouseOver);
			}
		});

		// unbind events for UI
		_listen('unbindEvents', function() {
			if(!_shareModalHidden) {
				_toggleShareModal();
			}

			if(_idleInterval) {
				clearInterval(_idleInterval);
			}
			framework.unbind(document, 'mouseout', _onMouseLeaveWindow);
			framework.unbind(document, 'mousemove', _onIdleMouseMove);
			framework.unbind(_controls, 'pswpTap click', _onControlsTap);
			framework.unbind(pswp.scrollWrap, 'pswpTap', ui.onGlobalTap);
			framework.unbind(pswp.scrollWrap, 'mouseover', ui.onMouseOver);

			if(_fullscrenAPI) {
				framework.unbind(document, _fullscrenAPI.eventK, ui.updateFullscreen);
				if(_fullscrenAPI.isFullscreen()) {
					_options.hideAnimationDuration = 0;
					_fullscrenAPI.exit();
				}
				_fullscrenAPI = null;
			}
		});


		// clean up things when gallery is destroyed
		_listen('destroy', function() {
			if(_options.captionEl) {
				if(_fakeCaptionContainer) {
					_controls.removeChild(_fakeCaptionContainer);
				}
				framework.removeClass(_captionContainer, 'pswp__caption--empty');
			}

			if(_shareModal) {
				_shareModal.children[0].onclick = null;
			}
			framework.removeClass(_controls, 'pswp__ui--over-close');
			framework.addClass( _controls, 'pswp__ui--hidden');
			ui.setIdle(false);
		});
		

		if(!_options.showAnimationDuration) {
			framework.removeClass( _controls, 'pswp__ui--hidden');
		}
		_listen('initialZoomIn', function() {
			if(_options.showAnimationDuration) {
				framework.removeClass( _controls, 'pswp__ui--hidden');
			}
		});
		_listen('initialZoomOut', function() {
			framework.addClass( _controls, 'pswp__ui--hidden');
		});

		_listen('parseVerticalMargin', _applyNavBarGaps);
		
		_setupUIElements();

		if(_options.shareEl && _shareButton && _shareModal) {
			_shareModalHidden = true;
		}

		_countNumItems();

		_setupIdle();

		_setupFullscreenAPI();

		_setupLoadingIndicator();
	};

	ui.setIdle = function(isIdle) {
		_isIdle = isIdle;
		_togglePswpClass(_controls, 'ui--idle', isIdle);
	};

	ui.update = function() {
		// Don't update UI if it's hidden
		if(_controlsVisible && pswp.currItem) {
			
			ui.updateIndexIndicator();

			if(_options.captionEl) {
				_options.addCaptionHTMLFn(pswp.currItem, _captionContainer);

				_togglePswpClass(_captionContainer, 'caption--empty', !pswp.currItem.title);
			}

			_overlayUIUpdated = true;

		} else {
			_overlayUIUpdated = false;
		}

		if(!_shareModalHidden) {
			_toggleShareModal();
		}

		_countNumItems();
	};

	ui.updateFullscreen = function(e) {

		if(e) {
			// some browsers change window scroll position during the fullscreen
			// so PhotoSwipe updates it just in case
			setTimeout(function() {
				pswp.setScrollOffset( 0, framework.getScrollY() );
			}, 50);
		}
		
		// toogle pswp--fs class on root element
		framework[ (_fullscrenAPI.isFullscreen() ? 'add' : 'remove') + 'Class' ](pswp.template, 'pswp--fs');
	};

	ui.updateIndexIndicator = function() {
		if(_options.counterEl) {
			_indexIndicator.innerHTML = (pswp.getCurrentIndex()+1) + 
										_options.indexIndicatorSep + 
										_options.getNumItemsFn();
		}
	};
	
	ui.onGlobalTap = function(e) {
		e = e || window.event;
		var target = e.target || e.srcElement;

		if(_blockControlsTap) {
			return;
		}

		if(e.detail && e.detail.pointerType === 'mouse') {

			// close gallery if clicked outside of the image
			if(_hasCloseClass(target)) {
				pswp.close();
				return;
			}

			if(framework.hasClass(target, 'pswp__img')) {
				if(pswp.getZoomLevel() === 1 && pswp.getZoomLevel() <= pswp.currItem.fitRatio) {
					if(_options.clickToCloseNonZoomable) {
						pswp.close();
					}
				} else {
					pswp.toggleDesktopZoom(e.detail.releasePoint);
				}
			}
			
		} else {

			// tap anywhere (except buttons) to toggle visibility of controls
			if(_options.tapToToggleControls) {
				if(_controlsVisible) {
					ui.hideControls();
				} else {
					ui.showControls();
				}
			}

			// tap to close gallery
			if(_options.tapToClose && (framework.hasClass(target, 'pswp__img') || _hasCloseClass(target)) ) {
				pswp.close();
				return;
			}
			
		}
	};
	ui.onMouseOver = function(e) {
		e = e || window.event;
		var target = e.target || e.srcElement;

		// add class when mouse is over an element that should close the gallery
		_togglePswpClass(_controls, 'ui--over-close', _hasCloseClass(target));
	};

	ui.hideControls = function() {
		framework.addClass(_controls,'pswp__ui--hidden');
		_controlsVisible = false;
	};

	ui.showControls = function() {
		_controlsVisible = true;
		if(!_overlayUIUpdated) {
			ui.update();
		}
		framework.removeClass(_controls,'pswp__ui--hidden');
	};

	ui.supportsFullscreen = function() {
		var d = document;
		return !!(d.exitFullscreen || d.mozCancelFullScreen || d.webkitExitFullscreen || d.msExitFullscreen);
	};

	ui.getFullscreenAPI = function() {
		var dE = document.documentElement,
			api,
			tF = 'fullscreenchange';

		if (dE.requestFullscreen) {
			api = {
				enterK: 'requestFullscreen',
				exitK: 'exitFullscreen',
				elementK: 'fullscreenElement',
				eventK: tF
			};

		} else if(dE.mozRequestFullScreen ) {
			api = {
				enterK: 'mozRequestFullScreen',
				exitK: 'mozCancelFullScreen',
				elementK: 'mozFullScreenElement',
				eventK: 'moz' + tF
			};

			

		} else if(dE.webkitRequestFullscreen) {
			api = {
				enterK: 'webkitRequestFullscreen',
				exitK: 'webkitExitFullscreen',
				elementK: 'webkitFullscreenElement',
				eventK: 'webkit' + tF
			};

		} else if(dE.msRequestFullscreen) {
			api = {
				enterK: 'msRequestFullscreen',
				exitK: 'msExitFullscreen',
				elementK: 'msFullscreenElement',
				eventK: 'MSFullscreenChange'
			};
		}

		if(api) {
			api.enter = function() { 
				// disable close-on-scroll in fullscreen
				_initalCloseOnScrollValue = _options.closeOnScroll; 
				_options.closeOnScroll = false; 

				if(this.enterK === 'webkitRequestFullscreen') {
					pswp.template[this.enterK]( Element.ALLOW_KEYBOARD_INPUT );
				} else {
					return pswp.template[this.enterK](); 
				}
			};
			api.exit = function() { 
				_options.closeOnScroll = _initalCloseOnScrollValue;

				return document[this.exitK](); 

			};
			api.isFullscreen = function() { return document[this.elementK]; };
		}

		return api;
	};



};
return PhotoSwipeUI_Default;


});


/***/ }),
/* 85 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_babel_runtime_helpers_typeof__ = __webpack_require__(20);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_babel_runtime_helpers_typeof___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0_babel_runtime_helpers_typeof__);

var passiveSupported = __webpack_require__(102);
var isBrowser = (typeof window === 'undefined' ? 'undefined' : __WEBPACK_IMPORTED_MODULE_0_babel_runtime_helpers_typeof___default()(window)) === 'object';

if (isBrowser) {
  window.__$vuxPopups = window.__$vuxPopups || {};
}

var popupDialog = function popupDialog(option) {
  var _this = this;

  if (!isBrowser) {
    return;
  }
  this.uuid = Math.random().toString(36).substring(3, 8);
  this.params = {};
  if (Object.prototype.toString.call(option) === '[object Object]') {
    this.params = {
      hideOnBlur: option.hideOnBlur,
      onOpen: option.onOpen || function () {},
      onClose: option.onClose || function () {},
      showMask: option.showMask
    };
  }
  if (!!document.querySelectorAll('.vux-popup-mask').length <= 0) {
    this.divMask = document.createElement('a');
    this.divMask.className = 'vux-popup-mask';
    this.divMask.dataset.uuid = '';
    this.divMask.href = 'javascript:void(0)';
    document.body.appendChild(this.divMask);
  }
  var div = void 0;
  if (!option.container) {
    div = document.createElement('div');
  } else {
    div = option.container;
  }

  div.className += ' vux-popup-dialog vux-popup-dialog-' + this.uuid;
  if (!this.params.hideOnBlur) {
    div.className += ' vux-popup-mask-disabled';
  }

  this.div = div;

  if (!option.container) {
    document.body.appendChild(div);
  }
  this.container = document.querySelector('.vux-popup-dialog-' + this.uuid);
  this.mask = document.querySelector('.vux-popup-mask');
  this.mask.dataset.uuid += ',' + this.uuid;
  this._bindEvents();
  option = null;
  this.containerHandler = function () {
    _this.mask && !/show/.test(_this.mask.className) && setTimeout(function () {
      !/show/.test(_this.mask.className) && (_this.mask.style['zIndex'] = -1);
    }, 200);
  };

  this.container && this.container.addEventListener('webkitTransitionEnd', this.containerHandler);
  this.container && this.container.addEventListener('transitionend', this.containerHandler);
};

popupDialog.prototype.onClickMask = function () {
  this.params.hideOnBlur && this.params.onClose();
};

popupDialog.prototype._bindEvents = function () {
  if (this.params.hideOnBlur) {
    this.mask.addEventListener('click', this.onClickMask.bind(this), false);
    this.mask.addEventListener('touchmove', function (e) {
      return e.preventDefault();
    }, passiveSupported ? { passive: false } : false);
  }
};

popupDialog.prototype.show = function () {
  if (this.params.showMask) {
    this.mask.classList.add('vux-popup-show');
    this.mask.style['zIndex'] = 500;
  }
  this.container.classList.add('vux-popup-show');
  this.params.onOpen && this.params.onOpen(this);
  if (isBrowser) {
    window.__$vuxPopups[this.uuid] = 1;
  }
};

popupDialog.prototype.hide = function () {
  var _this2 = this;

  var shouldCallback = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : true;

  this.container.classList.remove('vux-popup-show');
  if (!document.querySelector('.vux-popup-dialog.vux-popup-show')) {
    this.mask.classList.remove('vux-popup-show');
    setTimeout(function () {
      _this2.mask && !/show/.test(_this2.mask.className) && (_this2.mask.style['zIndex'] = -1);
    }, 400);
  }
  shouldCallback === false && this.params.onClose && this.params.hideOnBlur && this.params.onClose(this);
  this.isShow = false;
  if (isBrowser) {
    delete window.__$vuxPopups[this.uuid];
  }
};

popupDialog.prototype.destroy = function () {
  this.mask.dataset.uuid = this.mask.dataset.uuid.replace(new RegExp(',' + this.uuid, 'g'), '');
  if (!this.mask.dataset.uuid) {
    this.mask.removeEventListener('click', this.onClickMask.bind(this), false);
    this.mask && this.mask.parentNode && this.mask.parentNode.removeChild(this.mask);
  } else {
    this.hide();
  }
  this.container.removeEventListener('webkitTransitionEnd', this.containerHandler);
  this.container.removeEventListener('transitionend', this.containerHandler);
  if (isBrowser) {
    delete window.__$vuxPopups[this.uuid];
  }
};

/* harmony default export */ __webpack_exports__["a"] = (popupDialog);

/***/ }),
/* 86 */,
/* 87 */
/***/ (function(module, exports, __webpack_require__) {

__webpack_require__(88);
var $Object = __webpack_require__(67).Object;
module.exports = function defineProperty(it, key, desc) {
  return $Object.defineProperty(it, key, desc);
};


/***/ }),
/* 88 */
/***/ (function(module, exports, __webpack_require__) {

var $export = __webpack_require__(86);
// 19.1.2.4 / 15.2.3.6 Object.defineProperty(O, P, Attributes)
$export($export.S + $export.F * !__webpack_require__(139), 'Object', { defineProperty: __webpack_require__(138).f });


/***/ }),
/* 89 */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),
/* 90 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__mixins_multi_items__ = __webpack_require__(5);




/* unused harmony default export */ var _unused_webpack_default_export = ({
  name: 'tab',
  mixins: [__WEBPACK_IMPORTED_MODULE_0__mixins_multi_items__["b" /* parentMixin */]],
  mounted: function mounted() {
    var _this = this;

    this.$nextTick(function () {
      setTimeout(function () {
        _this.hasReady = true;
      }, 0);
    });
  },

  props: {
    lineWidth: {
      type: Number,
      default: 3
    },
    activeColor: String,
    barActiveColor: String,
    defaultColor: String,
    disabledColor: String,
    animate: {
      type: Boolean,
      default: true
    },
    customBarWidth: [Function, String],
    preventDefault: Boolean,
    scrollThreshold: {
      type: Number,
      default: 4
    },
    barPosition: {
      type: String,
      default: 'bottom',
      validator: function validator(val) {
        return ['bottom', 'top'].indexOf(val) !== -1;
      }
    }
  },
  computed: {
    barLeft: function barLeft() {
      if (this.hasReady) {
        var count = this.scrollable ? window.innerWidth / this.$children[this.currentIndex || 0].$el.getBoundingClientRect().width : this.number;
        return this.currentIndex * (100 / count) + '%';
      }
    },
    barRight: function barRight() {
      if (this.hasReady) {
        var count = this.scrollable ? window.innerWidth / this.$children[this.currentIndex || 0].$el.getBoundingClientRect().width : this.number;
        return (count - this.currentIndex - 1) * (100 / count) + '%';
      }
    },
    innerBarStyle: function innerBarStyle() {
      return {
        width: typeof this.customBarWidth === 'function' ? this.customBarWidth(this.currentIndex) : this.customBarWidth,
        backgroundColor: this.barActiveColor || this.activeColor
      };
    },
    barStyle: function barStyle() {
      var commonStyle = {
        left: this.barLeft,
        right: this.barRight,
        display: 'block',
        height: this.lineWidth + 'px',
        transition: !this.hasReady ? 'none' : null
      };
      if (!this.customBarWidth) {
        commonStyle.backgroundColor = this.barActiveColor || this.activeColor;
      } else {
        commonStyle.backgroundColor = 'transparent';
      }
      return commonStyle;
    },
    barClass: function barClass() {
      return {
        'vux-tab-ink-bar-transition-forward': this.direction === 'forward',
        'vux-tab-ink-bar-transition-backward': this.direction === 'backward'
      };
    },
    scrollable: function scrollable() {
      return this.number > this.scrollThreshold;
    }
  },
  watch: {
    currentIndex: function currentIndex(newIndex, oldIndex) {
      this.direction = newIndex > oldIndex ? 'forward' : 'backward';
      this.$emit('on-index-change', newIndex, oldIndex);
      this.hasReady && this.scrollToActiveTab();
    }
  },
  data: function data() {
    return {
      direction: 'forward',
      right: '100%',
      hasReady: false
    };
  },

  methods: {
    scrollToActiveTab: function scrollToActiveTab() {
      var _this2 = this;

      if (!this.scrollable || !this.$children || !this.$children.length) {
        return;
      }
      var currentIndexTab = this.$children[this.currentIndex].$el;
      var count = 0;

      var step = function step() {
        var scrollDuration = 15;
        var nav = _this2.$refs.nav;
        nav.scrollLeft += (currentIndexTab.offsetLeft - (nav.offsetWidth - currentIndexTab.offsetWidth) / 2 - nav.scrollLeft) / scrollDuration;
        if (++count < scrollDuration) {
          window.requestAnimationFrame(step);
        }
      };
      window.requestAnimationFrame(step);
    }
  }
});

/***/ }),
/* 91 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__mixins_multi_items__ = __webpack_require__(5);




/* harmony default export */ __webpack_exports__["a"] = ({
  name: 'tab',
  mixins: [__WEBPACK_IMPORTED_MODULE_0__mixins_multi_items__["b" /* parentMixin */]],
  mounted: function mounted() {
    var _this = this;

    this.$nextTick(function () {
      setTimeout(function () {
        _this.hasReady = true;
      }, 0);
    });
  },

  props: {
    lineWidth: {
      type: Number,
      default: 3
    },
    activeColor: String,
    barActiveColor: String,
    defaultColor: String,
    disabledColor: String,
    animate: {
      type: Boolean,
      default: true
    },
    customBarWidth: [Function, String],
    preventDefault: Boolean,
    scrollThreshold: {
      type: Number,
      default: 4
    },
    barPosition: {
      type: String,
      default: 'bottom',
      validator: function validator(val) {
        return ['bottom', 'top'].indexOf(val) !== -1;
      }
    }
  },
  computed: {
    barLeft: function barLeft() {
      if (this.hasReady) {
        var count = this.scrollable ? window.innerWidth / this.$children[this.currentIndex || 0].$el.getBoundingClientRect().width : this.number;
        return this.currentIndex * (100 / count) + '%';
      }
    },
    barRight: function barRight() {
      if (this.hasReady) {
        var count = this.scrollable ? window.innerWidth / this.$children[this.currentIndex || 0].$el.getBoundingClientRect().width : this.number;
        return (count - this.currentIndex - 1) * (100 / count) + '%';
      }
    },
    innerBarStyle: function innerBarStyle() {
      return {
        width: typeof this.customBarWidth === 'function' ? this.customBarWidth(this.currentIndex) : this.customBarWidth,
        backgroundColor: this.barActiveColor || this.activeColor
      };
    },
    barStyle: function barStyle() {
      var commonStyle = {
        left: this.barLeft,
        right: this.barRight,
        display: 'block',
        height: this.lineWidth + 'px',
        transition: !this.hasReady ? 'none' : null
      };
      if (!this.customBarWidth) {
        commonStyle.backgroundColor = this.barActiveColor || this.activeColor;
      } else {
        commonStyle.backgroundColor = 'transparent';
      }
      return commonStyle;
    },
    barClass: function barClass() {
      return {
        'vux-tab-ink-bar-transition-forward': this.direction === 'forward',
        'vux-tab-ink-bar-transition-backward': this.direction === 'backward'
      };
    },
    scrollable: function scrollable() {
      return this.number > this.scrollThreshold;
    }
  },
  watch: {
    currentIndex: function currentIndex(newIndex, oldIndex) {
      this.direction = newIndex > oldIndex ? 'forward' : 'backward';
      this.$emit('on-index-change', newIndex, oldIndex);
      this.hasReady && this.scrollToActiveTab();
    }
  },
  data: function data() {
    return {
      direction: 'forward',
      right: '100%',
      hasReady: false
    };
  },

  methods: {
    scrollToActiveTab: function scrollToActiveTab() {
      var _this2 = this;

      if (!this.scrollable || !this.$children || !this.$children.length) {
        return;
      }
      var currentIndexTab = this.$children[this.currentIndex].$el;
      var count = 0;

      var step = function step() {
        var scrollDuration = 15;
        var nav = _this2.$refs.nav;
        nav.scrollLeft += (currentIndexTab.offsetLeft - (nav.offsetWidth - currentIndexTab.offsetWidth) / 2 - nav.scrollLeft) / scrollDuration;
        if (++count < scrollDuration) {
          window.requestAnimationFrame(step);
        }
      };
      window.requestAnimationFrame(step);
    }
  }
});

/***/ }),
/* 92 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{staticClass:"vux-tab-wrap",class:_vm.barPosition === 'top' ? 'vux-tab-bar-top' : ''},[_c('div',{staticClass:"vux-tab-container"},[_c('div',{ref:"nav",staticClass:"vux-tab",class:[{'vux-tab-no-animate': !_vm.animate},{ scrollable: _vm.scrollable }]},[_vm._t("default"),_vm._v(" "),(_vm.animate)?_c('div',{staticClass:"vux-tab-ink-bar",class:_vm.barClass,style:(_vm.barStyle)},[(_vm.customBarWidth)?_c('span',{staticClass:"vux-tab-bar-inner",style:(_vm.innerBarStyle)}):_vm._e()]):_vm._e()],2)])])}
var staticRenderFns = []
var esExports = { render: render, staticRenderFns: staticRenderFns }
/* harmony default export */ __webpack_exports__["a"] = (esExports);

/***/ }),
/* 93 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__mixins_multi_items__ = __webpack_require__(5);




/* unused harmony default export */ var _unused_webpack_default_export = ({
  name: 'tab-item',
  mixins: [__WEBPACK_IMPORTED_MODULE_0__mixins_multi_items__["a" /* childMixin */]],
  props: {
    activeClass: String,
    disabled: Boolean,
    badgeBackground: {
      type: String,
      default: '#f74c31'
    },
    badgeColor: {
      type: String,
      default: '#fff'
    },
    badgeLabel: String
  },
  computed: {
    style: function style() {
      return {
        borderWidth: this.$parent.lineWidth + 'px',
        borderColor: this.$parent.activeColor,
        color: this.currentSelected ? this.$parent.activeColor : this.disabled ? this.$parent.disabledColor : this.$parent.defaultColor,
        border: this.$parent.animate ? 'none' : 'auto'
      };
    }
  }
});

/***/ }),
/* 94 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__mixins_multi_items__ = __webpack_require__(5);




/* harmony default export */ __webpack_exports__["a"] = ({
  name: 'tab-item',
  mixins: [__WEBPACK_IMPORTED_MODULE_0__mixins_multi_items__["a" /* childMixin */]],
  props: {
    activeClass: String,
    disabled: Boolean,
    badgeBackground: {
      type: String,
      default: '#f74c31'
    },
    badgeColor: {
      type: String,
      default: '#fff'
    },
    badgeLabel: String
  },
  computed: {
    style: function style() {
      return {
        borderWidth: this.$parent.lineWidth + 'px',
        borderColor: this.$parent.activeColor,
        color: this.currentSelected ? this.$parent.activeColor : this.disabled ? this.$parent.disabledColor : this.$parent.defaultColor,
        border: this.$parent.animate ? 'none' : 'auto'
      };
    }
  }
});

/***/ }),
/* 95 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{staticClass:"vux-tab-item",class:[_vm.currentSelected ? _vm.activeClass : '', {
    'vux-tab-selected': _vm.currentSelected,
    'vux-tab-disabled': _vm.disabled 
  }],style:(_vm.style),on:{"click":_vm.onItemClick}},[_vm._t("default"),_vm._v(" "),(typeof _vm.badgeLabel !== 'undefined' && _vm.badgeLabel !== '')?_c('span',{staticClass:"vux-tab-item-badge",style:({
      background: _vm.badgeBackground,
      color: _vm.badgeColor
    })},[_vm._v("\n  "+_vm._s(_vm.badgeLabel))]):_vm._e()],2)}
var staticRenderFns = []
var esExports = { render: render, staticRenderFns: staticRenderFns }
/* harmony default export */ __webpack_exports__["a"] = (esExports);

/***/ }),
/* 96 */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),
/* 97 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__sticky__ = __webpack_require__(38);




/* unused harmony default export */ var _unused_webpack_default_export = ({
  name: 'sticky',
  data: function data() {
    return {
      initTimes: 0
    };
  },
  created: function created() {
    this.$vux && this.$vux.bus && this.$vux.bus.$on('vux:after-view-enter', this.bindSticky);
  },
  activated: function activated() {
    if (this.initTimes > 0) {
      this.bindSticky();
    }
    this.initTimes++;
  },
  mounted: function mounted() {
    var _this = this;

    this.$nextTick(function () {
      _this.bindSticky();
    });
  },
  beforeDestroy: function beforeDestroy() {
    this.$vux && this.$vux.bus && this.$vux.bus.$off('vux:after-view-enter', this.bindSticky);
  },

  methods: {
    bindSticky: function bindSticky() {
      var _this2 = this;

      if (this.disabled) {
        return;
      }
      this.$nextTick(function () {
        Object(__WEBPACK_IMPORTED_MODULE_0__sticky__["a" /* default */])(_this2.$el, {
          scrollBox: _this2.scrollBox,
          offset: _this2.offset,
          checkStickySupport: typeof _this2.checkStickySupport === 'undefined' ? true : _this2.checkStickySupport
        });
      });
    }
  },
  props: ['scrollBox', 'offset', 'checkStickySupport', 'disabled']
});

/***/ }),
/* 98 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__sticky__ = __webpack_require__(38);




/* harmony default export */ __webpack_exports__["a"] = ({
  name: 'sticky',
  data: function data() {
    return {
      initTimes: 0
    };
  },
  created: function created() {
    this.$vux && this.$vux.bus && this.$vux.bus.$on('vux:after-view-enter', this.bindSticky);
  },
  activated: function activated() {
    if (this.initTimes > 0) {
      this.bindSticky();
    }
    this.initTimes++;
  },
  mounted: function mounted() {
    var _this = this;

    this.$nextTick(function () {
      _this.bindSticky();
    });
  },
  beforeDestroy: function beforeDestroy() {
    this.$vux && this.$vux.bus && this.$vux.bus.$off('vux:after-view-enter', this.bindSticky);
  },

  methods: {
    bindSticky: function bindSticky() {
      var _this2 = this;

      if (this.disabled) {
        return;
      }
      this.$nextTick(function () {
        Object(__WEBPACK_IMPORTED_MODULE_0__sticky__["a" /* default */])(_this2.$el, {
          scrollBox: _this2.scrollBox,
          offset: _this2.offset,
          checkStickySupport: typeof _this2.checkStickySupport === 'undefined' ? true : _this2.checkStickySupport
        });
      });
    }
  },
  props: ['scrollBox', 'offset', 'checkStickySupport', 'disabled']
});

/***/ }),
/* 99 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{staticClass:"vux-sticky-box"},[_vm._t("default")],2)}
var staticRenderFns = []
var esExports = { render: render, staticRenderFns: staticRenderFns }
/* harmony default export */ __webpack_exports__["a"] = (esExports);

/***/ }),
/* 100 */,
/* 101 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_vux_loader_src_script_loader_js_vux_loader_src_script_loader_js_vue_loader_lib_selector_type_script_index_0_index_vue__ = __webpack_require__(164);
/* unused harmony namespace reexport */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__babel_loader_vux_loader_src_script_loader_js_vue_loader_lib_selector_type_script_index_0_index_vue__ = __webpack_require__(165);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__vue_loader_lib_template_compiler_index_id_data_v_b3594a1a_hasScoped_false_buble_transforms_vux_loader_src_before_template_compiler_loader_js_vux_loader_src_template_loader_js_vue_loader_lib_selector_type_template_index_0_index_vue__ = __webpack_require__(166);
function injectStyle (ssrContext) {
  __webpack_require__(163)
}
var normalizeComponent = __webpack_require__(0)
/* script */


/* template */

/* template functional */
var __vue_template_functional__ = false
/* styles */
var __vue_styles__ = injectStyle
/* scopeId */
var __vue_scopeId__ = null
/* moduleIdentifier (server only) */
var __vue_module_identifier__ = null
var Component = normalizeComponent(
  __WEBPACK_IMPORTED_MODULE_1__babel_loader_vux_loader_src_script_loader_js_vue_loader_lib_selector_type_script_index_0_index_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_2__vue_loader_lib_template_compiler_index_id_data_v_b3594a1a_hasScoped_false_buble_transforms_vux_loader_src_before_template_compiler_loader_js_vux_loader_src_template_loader_js_vue_loader_lib_selector_type_template_index_0_index_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)

/* harmony default export */ __webpack_exports__["a"] = (Component.exports);


/***/ }),
/* 102 */
/***/ (function(module, exports) {

var passiveSupported = false;

try {
  var options = Object.defineProperty({}, 'passive', {
    get: function get() {
      passiveSupported = true;
    }
  });
  window.addEventListener('test', null, options);
} catch (err) {}

module.exports = passiveSupported;

/***/ }),
/* 103 */,
/* 104 */,
/* 105 */,
/* 106 */,
/* 107 */,
/* 108 */,
/* 109 */,
/* 110 */,
/* 111 */,
/* 112 */,
/* 113 */,
/* 114 */,
/* 115 */,
/* 116 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_vux_loader_src_script_loader_js_vux_loader_src_script_loader_js_vue_loader_lib_selector_type_script_index_0_index_vue__ = __webpack_require__(259);
/* unused harmony namespace reexport */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__babel_loader_vux_loader_src_script_loader_js_vue_loader_lib_selector_type_script_index_0_index_vue__ = __webpack_require__(260);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__vue_loader_lib_template_compiler_index_id_data_v_46168f67_hasScoped_false_buble_transforms_vux_loader_src_before_template_compiler_loader_js_vux_loader_src_template_loader_js_vue_loader_lib_selector_type_template_index_0_index_vue__ = __webpack_require__(261);
function injectStyle (ssrContext) {
  __webpack_require__(258)
}
var normalizeComponent = __webpack_require__(0)
/* script */


/* template */

/* template functional */
var __vue_template_functional__ = false
/* styles */
var __vue_styles__ = injectStyle
/* scopeId */
var __vue_scopeId__ = null
/* moduleIdentifier (server only) */
var __vue_module_identifier__ = null
var Component = normalizeComponent(
  __WEBPACK_IMPORTED_MODULE_1__babel_loader_vux_loader_src_script_loader_js_vue_loader_lib_selector_type_script_index_0_index_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_2__vue_loader_lib_template_compiler_index_id_data_v_46168f67_hasScoped_false_buble_transforms_vux_loader_src_before_template_compiler_loader_js_vux_loader_src_template_loader_js_vue_loader_lib_selector_type_template_index_0_index_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)

/* harmony default export */ __webpack_exports__["a"] = (Component.exports);


/***/ }),
/* 117 */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),
/* 118 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_json_stringify__ = __webpack_require__(13);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_json_stringify___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_json_stringify__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__swiper_js__ = __webpack_require__(61);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__libs_router__ = __webpack_require__(10);






/* unused harmony default export */ var _unused_webpack_default_export = ({
  name: 'swiper',
  created: function created() {
    this.index = this.value || 0;
    if (this.index) {
      this.current = this.index;
    }
  },
  mounted: function mounted() {
    var _this2 = this;

    this.hasTwoLoopItem();
    this.$nextTick(function () {
      if (!(_this2.list && _this2.list.length === 0)) {
        _this2.render(_this2.index);
      }
      _this2.xheight = _this2.getHeight();
      _this2.$emit('on-get-height', _this2.xheight);
    });
  },

  methods: {
    hasTwoLoopItem: function hasTwoLoopItem() {
      if (this.list.length === 2 && this.loop) {
        this.listTwoLoopItem = this.list;
      } else {
        this.listTwoLoopItem = [];
      }
    },
    clickListItem: function clickListItem(item) {
      Object(__WEBPACK_IMPORTED_MODULE_2__libs_router__["b" /* go */])(item.url, this.$router);
      this.$emit('on-click-list-item', JSON.parse(__WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_json_stringify___default()(item)));
    },
    buildBackgroundUrl: function buildBackgroundUrl(item) {
      return item.fallbackImg ? 'url(' + item.img + '), url(' + item.fallbackImg + ')' : 'url(' + item.img + ')';
    },
    render: function render() {
      var _this3 = this;

      var index = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 0;

      this.swiper && this.swiper.destroy();
      this.swiper = new __WEBPACK_IMPORTED_MODULE_1__swiper_js__["a" /* default */]({
        container: this.$el,
        direction: this.direction,
        auto: this.auto,
        loop: this.loop,
        interval: this.interval,
        threshold: this.threshold,
        duration: this.duration,
        height: this.height || this._height,
        minMovingDistance: this.minMovingDistance,
        imgList: this.imgList
      }).on('swiped', function (prev, index) {
        _this3.current = index % _this3.length;
        _this3.index = index % _this3.length;
      });
      if (index > 0) {
        this.swiper.go(index);
      }
    },
    rerender: function rerender() {
      var _this4 = this;

      if (!this.$el || this.hasRender) {
        return;
      }
      this.hasRender = true;
      this.hasTwoLoopItem();
      this.$nextTick(function () {
        _this4.index = _this4.value || 0;
        _this4.current = _this4.value || 0;
        _this4.length = _this4.list.length || _this4.$children.length;
        _this4.destroy();
        _this4.render(_this4.value);
      });
    },
    destroy: function destroy() {
      this.hasRender = false;
      this.swiper && this.swiper.destroy();
    },
    getHeight: function getHeight() {
      var hasHeight = parseInt(this.height, 10);
      if (hasHeight) return this.height;
      if (!hasHeight) {
        if (this.aspectRatio) {
          return this.$el.offsetWidth * this.aspectRatio + 'px';
        }
        return '180px';
      }
    }
  },
  props: {
    list: {
      type: Array,
      default: function _default() {
        return [];
      }
    },
    direction: {
      type: String,
      default: 'horizontal'
    },
    showDots: {
      type: Boolean,
      default: true
    },
    showDescMask: {
      type: Boolean,
      default: true
    },
    dotsPosition: {
      type: String,
      default: 'right'
    },
    dotsClass: String,
    auto: Boolean,
    loop: Boolean,
    interval: {
      type: Number,
      default: 3000
    },
    threshold: {
      type: Number,
      default: 50
    },
    duration: {
      type: Number,
      default: 300
    },
    height: {
      type: String,
      default: 'auto'
    },
    aspectRatio: Number,
    minMovingDistance: {
      type: Number,
      default: 0
    },
    value: {
      type: Number,
      default: 0
    }
  },
  data: function data() {
    return {
      hasRender: false,
      current: this.index || 0,
      xheight: 'auto',
      length: this.list.length,
      index: 0,
      listTwoLoopItem: [] };
  },

  watch: {
    auto: function auto(val) {
      if (!val) {
        this.swiper && this.swiper.stop();
      } else {
        this.swiper && this.swiper._auto();
      }
    },
    list: function list(val, oldVal) {
      if (__WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_json_stringify___default()(val) !== __WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_json_stringify___default()(oldVal)) {
        this.rerender();
      }
    },
    current: function current(currentIndex) {
      this.index = currentIndex;
      this.$emit('on-index-change', currentIndex);
    },
    index: function index(val) {
      var _this = this;
      if (val !== this.current) {
        this.$nextTick(function () {
          _this.swiper && _this.swiper.go(val);
        });
      }
      this.$emit('input', val);
    },
    value: function value(val) {
      this.index = val;
    }
  },
  beforeDestroy: function beforeDestroy() {
    this.destroy();
  }
});

/***/ }),
/* 119 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_json_stringify__ = __webpack_require__(13);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_json_stringify___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_json_stringify__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__swiper_js__ = __webpack_require__(61);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__libs_router__ = __webpack_require__(10);






/* harmony default export */ __webpack_exports__["a"] = ({
  name: 'swiper',
  created: function created() {
    this.index = this.value || 0;
    if (this.index) {
      this.current = this.index;
    }
  },
  mounted: function mounted() {
    var _this2 = this;

    this.hasTwoLoopItem();
    this.$nextTick(function () {
      if (!(_this2.list && _this2.list.length === 0)) {
        _this2.render(_this2.index);
      }
      _this2.xheight = _this2.getHeight();
      _this2.$emit('on-get-height', _this2.xheight);
    });
  },

  methods: {
    hasTwoLoopItem: function hasTwoLoopItem() {
      if (this.list.length === 2 && this.loop) {
        this.listTwoLoopItem = this.list;
      } else {
        this.listTwoLoopItem = [];
      }
    },
    clickListItem: function clickListItem(item) {
      Object(__WEBPACK_IMPORTED_MODULE_2__libs_router__["b" /* go */])(item.url, this.$router);
      this.$emit('on-click-list-item', JSON.parse(__WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_json_stringify___default()(item)));
    },
    buildBackgroundUrl: function buildBackgroundUrl(item) {
      return item.fallbackImg ? 'url(' + item.img + '), url(' + item.fallbackImg + ')' : 'url(' + item.img + ')';
    },
    render: function render() {
      var _this3 = this;

      var index = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 0;

      this.swiper && this.swiper.destroy();
      this.swiper = new __WEBPACK_IMPORTED_MODULE_1__swiper_js__["a" /* default */]({
        container: this.$el,
        direction: this.direction,
        auto: this.auto,
        loop: this.loop,
        interval: this.interval,
        threshold: this.threshold,
        duration: this.duration,
        height: this.height || this._height,
        minMovingDistance: this.minMovingDistance,
        imgList: this.imgList
      }).on('swiped', function (prev, index) {
        _this3.current = index % _this3.length;
        _this3.index = index % _this3.length;
      });
      if (index > 0) {
        this.swiper.go(index);
      }
    },
    rerender: function rerender() {
      var _this4 = this;

      if (!this.$el || this.hasRender) {
        return;
      }
      this.hasRender = true;
      this.hasTwoLoopItem();
      this.$nextTick(function () {
        _this4.index = _this4.value || 0;
        _this4.current = _this4.value || 0;
        _this4.length = _this4.list.length || _this4.$children.length;
        _this4.destroy();
        _this4.render(_this4.value);
      });
    },
    destroy: function destroy() {
      this.hasRender = false;
      this.swiper && this.swiper.destroy();
    },
    getHeight: function getHeight() {
      var hasHeight = parseInt(this.height, 10);
      if (hasHeight) return this.height;
      if (!hasHeight) {
        if (this.aspectRatio) {
          return this.$el.offsetWidth * this.aspectRatio + 'px';
        }
        return '180px';
      }
    }
  },
  props: {
    list: {
      type: Array,
      default: function _default() {
        return [];
      }
    },
    direction: {
      type: String,
      default: 'horizontal'
    },
    showDots: {
      type: Boolean,
      default: true
    },
    showDescMask: {
      type: Boolean,
      default: true
    },
    dotsPosition: {
      type: String,
      default: 'right'
    },
    dotsClass: String,
    auto: Boolean,
    loop: Boolean,
    interval: {
      type: Number,
      default: 3000
    },
    threshold: {
      type: Number,
      default: 50
    },
    duration: {
      type: Number,
      default: 300
    },
    height: {
      type: String,
      default: 'auto'
    },
    aspectRatio: Number,
    minMovingDistance: {
      type: Number,
      default: 0
    },
    value: {
      type: Number,
      default: 0
    }
  },
  data: function data() {
    return {
      hasRender: false,
      current: this.index || 0,
      xheight: 'auto',
      length: this.list.length,
      index: 0,
      listTwoLoopItem: [] };
  },

  watch: {
    auto: function auto(val) {
      if (!val) {
        this.swiper && this.swiper.stop();
      } else {
        this.swiper && this.swiper._auto();
      }
    },
    list: function list(val, oldVal) {
      if (__WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_json_stringify___default()(val) !== __WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_json_stringify___default()(oldVal)) {
        this.rerender();
      }
    },
    current: function current(currentIndex) {
      this.index = currentIndex;
      this.$emit('on-index-change', currentIndex);
    },
    index: function index(val) {
      var _this = this;
      if (val !== this.current) {
        this.$nextTick(function () {
          _this.swiper && _this.swiper.go(val);
        });
      }
      this.$emit('input', val);
    },
    value: function value(val) {
      this.index = val;
    }
  },
  beforeDestroy: function beforeDestroy() {
    this.destroy();
  }
});

/***/ }),
/* 120 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{staticClass:"vux-slider"},[_c('div',{staticClass:"vux-swiper",style:({height: _vm.xheight})},[_vm._t("default"),_vm._v(" "),_vm._l((_vm.list),function(item,index){return _c('div',{staticClass:"vux-swiper-item",attrs:{"data-index":index},on:{"click":function($event){_vm.clickListItem(item)}}},[_c('a',{attrs:{"href":"javascript:"}},[_c('div',{staticClass:"vux-img",style:({backgroundImage: _vm.buildBackgroundUrl(item)})}),_vm._v(" "),(_vm.showDescMask)?_c('p',{staticClass:"vux-swiper-desc"},[_vm._v(_vm._s(item.title))]):_vm._e()])])}),_vm._v(" "),_vm._l((_vm.listTwoLoopItem),function(item,index){return (_vm.listTwoLoopItem.length > 0)?_c('div',{staticClass:"vux-swiper-item vux-swiper-item-clone",attrs:{"data-index":index},on:{"click":function($event){_vm.clickListItem(item)}}},[_c('a',{attrs:{"href":"javascript:"}},[_c('div',{staticClass:"vux-img",style:({backgroundImage: _vm.buildBackgroundUrl(item)})}),_vm._v(" "),(_vm.showDescMask)?_c('p',{staticClass:"vux-swiper-desc"},[_vm._v(_vm._s(item.title))]):_vm._e()])]):_vm._e()})],2),_vm._v(" "),_c('div',{directives:[{name:"show",rawName:"v-show",value:(_vm.showDots),expression:"showDots"}],class:[_vm.dotsClass, 'vux-indicator', ("vux-indicator-" + _vm.dotsPosition)]},_vm._l((_vm.length),function(key){return _c('a',{attrs:{"href":"javascript:"}},[_c('i',{staticClass:"vux-icon-dot",class:{'active': key - 1 === _vm.current}})])}))])}
var staticRenderFns = []
var esExports = { render: render, staticRenderFns: staticRenderFns }
/* harmony default export */ __webpack_exports__["a"] = (esExports);

/***/ }),
/* 121 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony default export */ __webpack_exports__["a"] = ({
    'REQUEST_ERROR': {
        image: 'https://s10.mogucdn.com/p2/161213/upload_3bd517df2kgkclkhgl71bg4b37dcc_514x260.png',
        title: '网络加载失败',
        button: '点击刷新',
        tip: ''
    },
    'NOT_FOUND': {
        image: 'https://s8.mogucdn.com/pic/150112/17y7h4_ieydcyjsha2dgndcmuytambqgiyde_410x354.png',
        title: '很抱歉，找不到你要访问的页面',
        button: '返回',
        tip: ''
    },
    'DATA': {
        image: 'https://s10.mogucdn.com/p2/161213/upload_27e7gegi3f9acl5e05f3951if5855_514x260.png',
        title: '没有相关数据哦',
        button: '',
        tip: ''
    },
    'FOLLOW': {
        image: 'https://s10.mogucdn.com/p2/161213/upload_61ech6ihe399d85abhjhcigd38444_514x260.png',
        title: '关注有趣的人',
        button: '',
        tip: '不再错过他们每一条动态'
    },
    'FEED': {
        image: 'https://s10.mogucdn.com/p2/161213/upload_2ie7eb3ae0khl20g0g1glgb852e5i_514x260.png',
        title: '还没有任何反馈哦',
        button: '',
        tip: ''
    },
    'SHOP': {
        image: 'https://s10.mogucdn.com/p2/161213/upload_8acll7f4b4j2ljf8i164d5h7bl78g_514x260.png',
        title: '稍后再来试试吧~',
        button: '',
        tip: ''
    },
    'WEIBO': {
        image: 'https://s10.mogucdn.com/p2/161213/upload_3gh3bj6dk8k46783c78e414aeh7g0_514x260.png',
        title: '',
        button: '',
        tip: ''
    },
    'SEARCH': {
        image: 'https://s10.mogucdn.com/p2/161213/upload_74hhee883cbf190e3di6cljk23679_514x260.png',
        title: '抱歉！没找到相关商品~',
        button: '',
        tip: ''
    },
    'TAG': {
        image: 'https://s10.mogucdn.com/p2/161213/upload_76h1c5hjc8heecjehlfgekjdl2ki0_514x260.png',
        title: '',
        button: '',
        tip: ''
    },
    'MESSAGE': {
        image: 'https://s10.mogucdn.com/p2/161213/upload_21f4ij449lb4h67k0afjic82d0f31_514x260.png',
        title: '消息通知空空如也',
        button: '',
        tip: ''
    },
    'LIVE': {
        image: 'https://s10.mogucdn.com/p2/161213/upload_682e8fg28d8lk27b9l95jecghd4jl_514x260.png',
        title: '',
        button: '',
        tip: ''
    },
    'ORDER': {
        image: 'https://s10.mogucdn.com/p2/161213/upload_7b01ig2ih5egggj85l1gd8d38790f_514x260.png',
        title: "还没有相关订单哦",
        button: '',
        tip: ''
    },
    'CART': {
        image: 'https://s10.mogucdn.com/p2/161213/upload_1h082815bh50k99dajicd53ll1dhl_514x260.png',
        title: '购物车还是空的哦~',
        button: '去逛逛',
        tip: ''
    },
    'FOOTPRINT': {
        image: 'https://s10.mogucdn.com/p2/161213/upload_3c4d952gd6k9809gj4g8eg974lk14_514x260.png',
        title: '你还没有足迹~',
        button: '',
        tip: ''
    },
    'COUPON': {
        image: 'https://s10.mogucdn.com/mlcdn/c45406/170607_52khi3193g9ba5e023l7a6ecee326_514x258.png',
        title: '你还没有可用的店铺优惠券哦',
        button: '',
        tip: ''
    }
});

/***/ }),
/* 122 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_vux_loader_src_script_loader_js_vux_loader_src_script_loader_js_vue_loader_lib_selector_type_script_index_0_marquee_vue__ = __webpack_require__(227);
/* unused harmony namespace reexport */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__babel_loader_vux_loader_src_script_loader_js_vue_loader_lib_selector_type_script_index_0_marquee_vue__ = __webpack_require__(228);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__vue_loader_lib_template_compiler_index_id_data_v_27e3b286_hasScoped_false_buble_transforms_vux_loader_src_before_template_compiler_loader_js_vux_loader_src_template_loader_js_vue_loader_lib_selector_type_template_index_0_marquee_vue__ = __webpack_require__(229);
function injectStyle (ssrContext) {
  __webpack_require__(226)
}
var normalizeComponent = __webpack_require__(0)
/* script */


/* template */

/* template functional */
var __vue_template_functional__ = false
/* styles */
var __vue_styles__ = injectStyle
/* scopeId */
var __vue_scopeId__ = null
/* moduleIdentifier (server only) */
var __vue_module_identifier__ = null
var Component = normalizeComponent(
  __WEBPACK_IMPORTED_MODULE_1__babel_loader_vux_loader_src_script_loader_js_vue_loader_lib_selector_type_script_index_0_marquee_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_2__vue_loader_lib_template_compiler_index_id_data_v_27e3b286_hasScoped_false_buble_transforms_vux_loader_src_before_template_compiler_loader_js_vux_loader_src_template_loader_js_vue_loader_lib_selector_type_template_index_0_marquee_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)

/* harmony default export */ __webpack_exports__["a"] = (Component.exports);


/***/ }),
/* 123 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_vux_loader_src_script_loader_js_vux_loader_src_script_loader_js_vue_loader_lib_selector_type_script_index_0_marquee_item_vue__ = __webpack_require__(230);
/* unused harmony namespace reexport */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__babel_loader_vux_loader_src_script_loader_js_vue_loader_lib_selector_type_script_index_0_marquee_item_vue__ = __webpack_require__(231);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__vue_loader_lib_template_compiler_index_id_data_v_814132a2_hasScoped_false_buble_transforms_vux_loader_src_before_template_compiler_loader_js_vux_loader_src_template_loader_js_vue_loader_lib_selector_type_template_index_0_marquee_item_vue__ = __webpack_require__(232);
var normalizeComponent = __webpack_require__(0)
/* script */


/* template */

/* template functional */
var __vue_template_functional__ = false
/* styles */
var __vue_styles__ = null
/* scopeId */
var __vue_scopeId__ = null
/* moduleIdentifier (server only) */
var __vue_module_identifier__ = null
var Component = normalizeComponent(
  __WEBPACK_IMPORTED_MODULE_1__babel_loader_vux_loader_src_script_loader_js_vue_loader_lib_selector_type_script_index_0_marquee_item_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_2__vue_loader_lib_template_compiler_index_id_data_v_814132a2_hasScoped_false_buble_transforms_vux_loader_src_before_template_compiler_loader_js_vux_loader_src_template_loader_js_vue_loader_lib_selector_type_template_index_0_marquee_item_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)

/* harmony default export */ __webpack_exports__["a"] = (Component.exports);


/***/ }),
/* 124 */
/***/ (function(module, exports) {

module.exports = "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAYAAABzenr0AAAAeklEQVRYR+2XQQrAIAwEjW8s+cI+yfiE4iNbBC9CKb10BVlPuWWcbEAtLT62uH8SAMVARDQzO/q4AUw9BbCfgVrrNVa7AfBeUzMgAJqBUornnM8xbwfQei2APQ087TE1AwKgGvia4t8yIAAZkIHlBthfNcqr+O1SArgBPa9sMJ+mR6UAAAAASUVORK5CYII="

/***/ }),
/* 125 */
/***/ (function(module, exports) {

module.exports = "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAYAAABzenr0AAAACXBIWXMAAAsTAAALEwEAmpwYAAAKTWlDQ1BQaG90b3Nob3AgSUNDIHByb2ZpbGUAAHjanVN3WJP3Fj7f92UPVkLY8LGXbIEAIiOsCMgQWaIQkgBhhBASQMWFiApWFBURnEhVxILVCkidiOKgKLhnQYqIWotVXDjuH9yntX167+3t+9f7vOec5/zOec8PgBESJpHmomoAOVKFPDrYH49PSMTJvYACFUjgBCAQ5svCZwXFAADwA3l4fnSwP/wBr28AAgBw1S4kEsfh/4O6UCZXACCRAOAiEucLAZBSAMguVMgUAMgYALBTs2QKAJQAAGx5fEIiAKoNAOz0ST4FANipk9wXANiiHKkIAI0BAJkoRyQCQLsAYFWBUiwCwMIAoKxAIi4EwK4BgFm2MkcCgL0FAHaOWJAPQGAAgJlCLMwAIDgCAEMeE80DIEwDoDDSv+CpX3CFuEgBAMDLlc2XS9IzFLiV0Bp38vDg4iHiwmyxQmEXKRBmCeQinJebIxNI5wNMzgwAABr50cH+OD+Q5+bk4eZm52zv9MWi/mvwbyI+IfHf/ryMAgQAEE7P79pf5eXWA3DHAbB1v2upWwDaVgBo3/ldM9sJoFoK0Hr5i3k4/EAenqFQyDwdHAoLC+0lYqG9MOOLPv8z4W/gi372/EAe/tt68ABxmkCZrcCjg/1xYW52rlKO58sEQjFu9+cj/seFf/2OKdHiNLFcLBWK8ViJuFAiTcd5uVKRRCHJleIS6X8y8R+W/QmTdw0ArIZPwE62B7XLbMB+7gECiw5Y0nYAQH7zLYwaC5EAEGc0Mnn3AACTv/mPQCsBAM2XpOMAALzoGFyolBdMxggAAESggSqwQQcMwRSswA6cwR28wBcCYQZEQAwkwDwQQgbkgBwKoRiWQRlUwDrYBLWwAxqgEZrhELTBMTgN5+ASXIHrcBcGYBiewhi8hgkEQcgIE2EhOogRYo7YIs4IF5mOBCJhSDSSgKQg6YgUUSLFyHKkAqlCapFdSCPyLXIUOY1cQPqQ28ggMor8irxHMZSBslED1AJ1QLmoHxqKxqBz0XQ0D12AlqJr0Rq0Hj2AtqKn0UvodXQAfYqOY4DRMQ5mjNlhXIyHRWCJWBomxxZj5Vg1Vo81Yx1YN3YVG8CeYe8IJAKLgBPsCF6EEMJsgpCQR1hMWEOoJewjtBK6CFcJg4Qxwicik6hPtCV6EvnEeGI6sZBYRqwm7iEeIZ4lXicOE1+TSCQOyZLkTgohJZAySQtJa0jbSC2kU6Q+0hBpnEwm65Btyd7kCLKArCCXkbeQD5BPkvvJw+S3FDrFiOJMCaIkUqSUEko1ZT/lBKWfMkKZoKpRzame1AiqiDqfWkltoHZQL1OHqRM0dZolzZsWQ8ukLaPV0JppZ2n3aC/pdLoJ3YMeRZfQl9Jr6Afp5+mD9HcMDYYNg8dIYigZaxl7GacYtxkvmUymBdOXmchUMNcyG5lnmA+Yb1VYKvYqfBWRyhKVOpVWlX6V56pUVXNVP9V5qgtUq1UPq15WfaZGVbNQ46kJ1Bar1akdVbupNq7OUndSj1DPUV+jvl/9gvpjDbKGhUaghkijVGO3xhmNIRbGMmXxWELWclYD6yxrmE1iW7L57Ex2Bfsbdi97TFNDc6pmrGaRZp3mcc0BDsax4PA52ZxKziHODc57LQMtPy2x1mqtZq1+rTfaetq+2mLtcu0W7eva73VwnUCdLJ31Om0693UJuja6UbqFutt1z+o+02PreekJ9cr1Dund0Uf1bfSj9Rfq79bv0R83MDQINpAZbDE4Y/DMkGPoa5hpuNHwhOGoEctoupHEaKPRSaMnuCbuh2fjNXgXPmasbxxirDTeZdxrPGFiaTLbpMSkxeS+Kc2Ua5pmutG003TMzMgs3KzYrMnsjjnVnGueYb7ZvNv8jYWlRZzFSos2i8eW2pZ8ywWWTZb3rJhWPlZ5VvVW16xJ1lzrLOtt1ldsUBtXmwybOpvLtqitm63Edptt3xTiFI8p0in1U27aMez87ArsmuwG7Tn2YfYl9m32zx3MHBId1jt0O3xydHXMdmxwvOuk4TTDqcSpw+lXZxtnoXOd8zUXpkuQyxKXdpcXU22niqdun3rLleUa7rrStdP1o5u7m9yt2W3U3cw9xX2r+00umxvJXcM970H08PdY4nHM452nm6fC85DnL152Xlle+70eT7OcJp7WMG3I28Rb4L3Le2A6Pj1l+s7pAz7GPgKfep+Hvqa+It89viN+1n6Zfgf8nvs7+sv9j/i/4XnyFvFOBWABwQHlAb2BGoGzA2sDHwSZBKUHNQWNBbsGLww+FUIMCQ1ZH3KTb8AX8hv5YzPcZyya0RXKCJ0VWhv6MMwmTB7WEY6GzwjfEH5vpvlM6cy2CIjgR2yIuB9pGZkX+X0UKSoyqi7qUbRTdHF09yzWrORZ+2e9jvGPqYy5O9tqtnJ2Z6xqbFJsY+ybuIC4qriBeIf4RfGXEnQTJAntieTE2MQ9ieNzAudsmjOc5JpUlnRjruXcorkX5unOy553PFk1WZB8OIWYEpeyP+WDIEJQLxhP5aduTR0T8oSbhU9FvqKNolGxt7hKPJLmnVaV9jjdO31D+miGT0Z1xjMJT1IreZEZkrkj801WRNberM/ZcdktOZSclJyjUg1plrQr1zC3KLdPZisrkw3keeZtyhuTh8r35CP5c/PbFWyFTNGjtFKuUA4WTC+oK3hbGFt4uEi9SFrUM99m/ur5IwuCFny9kLBQuLCz2Lh4WfHgIr9FuxYji1MXdy4xXVK6ZHhp8NJ9y2jLspb9UOJYUlXyannc8o5Sg9KlpUMrglc0lamUycturvRauWMVYZVkVe9ql9VbVn8qF5VfrHCsqK74sEa45uJXTl/VfPV5bdra3kq3yu3rSOuk626s91m/r0q9akHV0IbwDa0b8Y3lG19tSt50oXpq9Y7NtM3KzQM1YTXtW8y2rNvyoTaj9nqdf13LVv2tq7e+2Sba1r/dd3vzDoMdFTve75TsvLUreFdrvUV99W7S7oLdjxpiG7q/5n7duEd3T8Wej3ulewf2Re/ranRvbNyvv7+yCW1SNo0eSDpw5ZuAb9qb7Zp3tXBaKg7CQeXBJ9+mfHvjUOihzsPcw83fmX+39QjrSHkr0jq/dawto22gPaG97+iMo50dXh1Hvrf/fu8x42N1xzWPV56gnSg98fnkgpPjp2Snnp1OPz3Umdx590z8mWtdUV29Z0PPnj8XdO5Mt1/3yfPe549d8Lxw9CL3Ytslt0utPa49R35w/eFIr1tv62X3y+1XPK509E3rO9Hv03/6asDVc9f41y5dn3m978bsG7duJt0cuCW69fh29u0XdwruTNxdeo94r/y+2v3qB/oP6n+0/rFlwG3g+GDAYM/DWQ/vDgmHnv6U/9OH4dJHzEfVI0YjjY+dHx8bDRq98mTOk+GnsqcTz8p+Vv9563Or59/94vtLz1j82PAL+YvPv655qfNy76uprzrHI8cfvM55PfGm/K3O233vuO+638e9H5ko/ED+UPPR+mPHp9BP9z7nfP78L/eE8/sl0p8zAAAAIGNIUk0AAHolAACAgwAA+f8AAIDpAAB1MAAA6mAAADqYAAAXb5JfxUYAAAB1SURBVHja7NexDYAwDARAf2aMMkJ+APagTTICipgxNIAYAAnkvDu7+L/WGGPYlwMBfgcA8HpJrbUDiGZmOWcI4BvQWrsCO8kkgF9AKSWFELZzTSS7AH4BzwCSEMA3wNZ4H7DsEEAAASYE6DWbDXAAAAD//wMAx/i70L9LINwAAAAASUVORK5CYII="

/***/ }),
/* 126 */
/***/ (function(module, exports) {

module.exports = "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAYAAABzenr0AAAACXBIWXMAAAsTAAALEwEAmpwYAAAKTWlDQ1BQaG90b3Nob3AgSUNDIHByb2ZpbGUAAHjanVN3WJP3Fj7f92UPVkLY8LGXbIEAIiOsCMgQWaIQkgBhhBASQMWFiApWFBURnEhVxILVCkidiOKgKLhnQYqIWotVXDjuH9yntX167+3t+9f7vOec5/zOec8PgBESJpHmomoAOVKFPDrYH49PSMTJvYACFUjgBCAQ5svCZwXFAADwA3l4fnSwP/wBr28AAgBw1S4kEsfh/4O6UCZXACCRAOAiEucLAZBSAMguVMgUAMgYALBTs2QKAJQAAGx5fEIiAKoNAOz0ST4FANipk9wXANiiHKkIAI0BAJkoRyQCQLsAYFWBUiwCwMIAoKxAIi4EwK4BgFm2MkcCgL0FAHaOWJAPQGAAgJlCLMwAIDgCAEMeE80DIEwDoDDSv+CpX3CFuEgBAMDLlc2XS9IzFLiV0Bp38vDg4iHiwmyxQmEXKRBmCeQinJebIxNI5wNMzgwAABr50cH+OD+Q5+bk4eZm52zv9MWi/mvwbyI+IfHf/ryMAgQAEE7P79pf5eXWA3DHAbB1v2upWwDaVgBo3/ldM9sJoFoK0Hr5i3k4/EAenqFQyDwdHAoLC+0lYqG9MOOLPv8z4W/gi372/EAe/tt68ABxmkCZrcCjg/1xYW52rlKO58sEQjFu9+cj/seFf/2OKdHiNLFcLBWK8ViJuFAiTcd5uVKRRCHJleIS6X8y8R+W/QmTdw0ArIZPwE62B7XLbMB+7gECiw5Y0nYAQH7zLYwaC5EAEGc0Mnn3AACTv/mPQCsBAM2XpOMAALzoGFyolBdMxggAAESggSqwQQcMwRSswA6cwR28wBcCYQZEQAwkwDwQQgbkgBwKoRiWQRlUwDrYBLWwAxqgEZrhELTBMTgN5+ASXIHrcBcGYBiewhi8hgkEQcgIE2EhOogRYo7YIs4IF5mOBCJhSDSSgKQg6YgUUSLFyHKkAqlCapFdSCPyLXIUOY1cQPqQ28ggMor8irxHMZSBslED1AJ1QLmoHxqKxqBz0XQ0D12AlqJr0Rq0Hj2AtqKn0UvodXQAfYqOY4DRMQ5mjNlhXIyHRWCJWBomxxZj5Vg1Vo81Yx1YN3YVG8CeYe8IJAKLgBPsCF6EEMJsgpCQR1hMWEOoJewjtBK6CFcJg4Qxwicik6hPtCV6EvnEeGI6sZBYRqwm7iEeIZ4lXicOE1+TSCQOyZLkTgohJZAySQtJa0jbSC2kU6Q+0hBpnEwm65Btyd7kCLKArCCXkbeQD5BPkvvJw+S3FDrFiOJMCaIkUqSUEko1ZT/lBKWfMkKZoKpRzame1AiqiDqfWkltoHZQL1OHqRM0dZolzZsWQ8ukLaPV0JppZ2n3aC/pdLoJ3YMeRZfQl9Jr6Afp5+mD9HcMDYYNg8dIYigZaxl7GacYtxkvmUymBdOXmchUMNcyG5lnmA+Yb1VYKvYqfBWRyhKVOpVWlX6V56pUVXNVP9V5qgtUq1UPq15WfaZGVbNQ46kJ1Bar1akdVbupNq7OUndSj1DPUV+jvl/9gvpjDbKGhUaghkijVGO3xhmNIRbGMmXxWELWclYD6yxrmE1iW7L57Ex2Bfsbdi97TFNDc6pmrGaRZp3mcc0BDsax4PA52ZxKziHODc57LQMtPy2x1mqtZq1+rTfaetq+2mLtcu0W7eva73VwnUCdLJ31Om0693UJuja6UbqFutt1z+o+02PreekJ9cr1Dund0Uf1bfSj9Rfq79bv0R83MDQINpAZbDE4Y/DMkGPoa5hpuNHwhOGoEctoupHEaKPRSaMnuCbuh2fjNXgXPmasbxxirDTeZdxrPGFiaTLbpMSkxeS+Kc2Ua5pmutG003TMzMgs3KzYrMnsjjnVnGueYb7ZvNv8jYWlRZzFSos2i8eW2pZ8ywWWTZb3rJhWPlZ5VvVW16xJ1lzrLOtt1ldsUBtXmwybOpvLtqitm63Edptt3xTiFI8p0in1U27aMez87ArsmuwG7Tn2YfYl9m32zx3MHBId1jt0O3xydHXMdmxwvOuk4TTDqcSpw+lXZxtnoXOd8zUXpkuQyxKXdpcXU22niqdun3rLleUa7rrStdP1o5u7m9yt2W3U3cw9xX2r+00umxvJXcM970H08PdY4nHM452nm6fC85DnL152Xlle+70eT7OcJp7WMG3I28Rb4L3Le2A6Pj1l+s7pAz7GPgKfep+Hvqa+It89viN+1n6Zfgf8nvs7+sv9j/i/4XnyFvFOBWABwQHlAb2BGoGzA2sDHwSZBKUHNQWNBbsGLww+FUIMCQ1ZH3KTb8AX8hv5YzPcZyya0RXKCJ0VWhv6MMwmTB7WEY6GzwjfEH5vpvlM6cy2CIjgR2yIuB9pGZkX+X0UKSoyqi7qUbRTdHF09yzWrORZ+2e9jvGPqYy5O9tqtnJ2Z6xqbFJsY+ybuIC4qriBeIf4RfGXEnQTJAntieTE2MQ9ieNzAudsmjOc5JpUlnRjruXcorkX5unOy553PFk1WZB8OIWYEpeyP+WDIEJQLxhP5aduTR0T8oSbhU9FvqKNolGxt7hKPJLmnVaV9jjdO31D+miGT0Z1xjMJT1IreZEZkrkj801WRNberM/ZcdktOZSclJyjUg1plrQr1zC3KLdPZisrkw3keeZtyhuTh8r35CP5c/PbFWyFTNGjtFKuUA4WTC+oK3hbGFt4uEi9SFrUM99m/ur5IwuCFny9kLBQuLCz2Lh4WfHgIr9FuxYji1MXdy4xXVK6ZHhp8NJ9y2jLspb9UOJYUlXyannc8o5Sg9KlpUMrglc0lamUycturvRauWMVYZVkVe9ql9VbVn8qF5VfrHCsqK74sEa45uJXTl/VfPV5bdra3kq3yu3rSOuk626s91m/r0q9akHV0IbwDa0b8Y3lG19tSt50oXpq9Y7NtM3KzQM1YTXtW8y2rNvyoTaj9nqdf13LVv2tq7e+2Sba1r/dd3vzDoMdFTve75TsvLUreFdrvUV99W7S7oLdjxpiG7q/5n7duEd3T8Wej3ulewf2Re/ranRvbNyvv7+yCW1SNo0eSDpw5ZuAb9qb7Zp3tXBaKg7CQeXBJ9+mfHvjUOihzsPcw83fmX+39QjrSHkr0jq/dawto22gPaG97+iMo50dXh1Hvrf/fu8x42N1xzWPV56gnSg98fnkgpPjp2Snnp1OPz3Umdx590z8mWtdUV29Z0PPnj8XdO5Mt1/3yfPe549d8Lxw9CL3Ytslt0utPa49R35w/eFIr1tv62X3y+1XPK509E3rO9Hv03/6asDVc9f41y5dn3m978bsG7duJt0cuCW69fh29u0XdwruTNxdeo94r/y+2v3qB/oP6n+0/rFlwG3g+GDAYM/DWQ/vDgmHnv6U/9OH4dJHzEfVI0YjjY+dHx8bDRq98mTOk+GnsqcTz8p+Vv9563Or59/94vtLz1j82PAL+YvPv655qfNy76uprzrHI8cfvM55PfGm/K3O233vuO+638e9H5ko/ED+UPPR+mPHp9BP9z7nfP78L/eE8/sl0p8zAAAAIGNIUk0AAHolAACAgwAA+f8AAIDpAAB1MAAA6mAAADqYAAAXb5JfxUYAAAB3SURBVHja7NexDYAwDARAf1YkygjxAMwBbZIRUMSMpgEaWiSQ8+4sWf/XGmYmXw4I+B0AwOsltdYOIIqI5JxBgG9Aa+0K7KqaCPALKKWkEMJ2rklVOwF+AbZM9xHmHQT4BsgaH2UEEEDAYAC+ZqMBDgAAAP//AwDjiLvQnxgLcQAAAABJRU5ErkJggg=="

/***/ }),
/* 127 */
/***/ (function(module, exports) {

module.exports = "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAYAAABzenr0AAAACXBIWXMAAAsTAAALEwEAmpwYAAAKTWlDQ1BQaG90b3Nob3AgSUNDIHByb2ZpbGUAAHjanVN3WJP3Fj7f92UPVkLY8LGXbIEAIiOsCMgQWaIQkgBhhBASQMWFiApWFBURnEhVxILVCkidiOKgKLhnQYqIWotVXDjuH9yntX167+3t+9f7vOec5/zOec8PgBESJpHmomoAOVKFPDrYH49PSMTJvYACFUjgBCAQ5svCZwXFAADwA3l4fnSwP/wBr28AAgBw1S4kEsfh/4O6UCZXACCRAOAiEucLAZBSAMguVMgUAMgYALBTs2QKAJQAAGx5fEIiAKoNAOz0ST4FANipk9wXANiiHKkIAI0BAJkoRyQCQLsAYFWBUiwCwMIAoKxAIi4EwK4BgFm2MkcCgL0FAHaOWJAPQGAAgJlCLMwAIDgCAEMeE80DIEwDoDDSv+CpX3CFuEgBAMDLlc2XS9IzFLiV0Bp38vDg4iHiwmyxQmEXKRBmCeQinJebIxNI5wNMzgwAABr50cH+OD+Q5+bk4eZm52zv9MWi/mvwbyI+IfHf/ryMAgQAEE7P79pf5eXWA3DHAbB1v2upWwDaVgBo3/ldM9sJoFoK0Hr5i3k4/EAenqFQyDwdHAoLC+0lYqG9MOOLPv8z4W/gi372/EAe/tt68ABxmkCZrcCjg/1xYW52rlKO58sEQjFu9+cj/seFf/2OKdHiNLFcLBWK8ViJuFAiTcd5uVKRRCHJleIS6X8y8R+W/QmTdw0ArIZPwE62B7XLbMB+7gECiw5Y0nYAQH7zLYwaC5EAEGc0Mnn3AACTv/mPQCsBAM2XpOMAALzoGFyolBdMxggAAESggSqwQQcMwRSswA6cwR28wBcCYQZEQAwkwDwQQgbkgBwKoRiWQRlUwDrYBLWwAxqgEZrhELTBMTgN5+ASXIHrcBcGYBiewhi8hgkEQcgIE2EhOogRYo7YIs4IF5mOBCJhSDSSgKQg6YgUUSLFyHKkAqlCapFdSCPyLXIUOY1cQPqQ28ggMor8irxHMZSBslED1AJ1QLmoHxqKxqBz0XQ0D12AlqJr0Rq0Hj2AtqKn0UvodXQAfYqOY4DRMQ5mjNlhXIyHRWCJWBomxxZj5Vg1Vo81Yx1YN3YVG8CeYe8IJAKLgBPsCF6EEMJsgpCQR1hMWEOoJewjtBK6CFcJg4Qxwicik6hPtCV6EvnEeGI6sZBYRqwm7iEeIZ4lXicOE1+TSCQOyZLkTgohJZAySQtJa0jbSC2kU6Q+0hBpnEwm65Btyd7kCLKArCCXkbeQD5BPkvvJw+S3FDrFiOJMCaIkUqSUEko1ZT/lBKWfMkKZoKpRzame1AiqiDqfWkltoHZQL1OHqRM0dZolzZsWQ8ukLaPV0JppZ2n3aC/pdLoJ3YMeRZfQl9Jr6Afp5+mD9HcMDYYNg8dIYigZaxl7GacYtxkvmUymBdOXmchUMNcyG5lnmA+Yb1VYKvYqfBWRyhKVOpVWlX6V56pUVXNVP9V5qgtUq1UPq15WfaZGVbNQ46kJ1Bar1akdVbupNq7OUndSj1DPUV+jvl/9gvpjDbKGhUaghkijVGO3xhmNIRbGMmXxWELWclYD6yxrmE1iW7L57Ex2Bfsbdi97TFNDc6pmrGaRZp3mcc0BDsax4PA52ZxKziHODc57LQMtPy2x1mqtZq1+rTfaetq+2mLtcu0W7eva73VwnUCdLJ31Om0693UJuja6UbqFutt1z+o+02PreekJ9cr1Dund0Uf1bfSj9Rfq79bv0R83MDQINpAZbDE4Y/DMkGPoa5hpuNHwhOGoEctoupHEaKPRSaMnuCbuh2fjNXgXPmasbxxirDTeZdxrPGFiaTLbpMSkxeS+Kc2Ua5pmutG003TMzMgs3KzYrMnsjjnVnGueYb7ZvNv8jYWlRZzFSos2i8eW2pZ8ywWWTZb3rJhWPlZ5VvVW16xJ1lzrLOtt1ldsUBtXmwybOpvLtqitm63Edptt3xTiFI8p0in1U27aMez87ArsmuwG7Tn2YfYl9m32zx3MHBId1jt0O3xydHXMdmxwvOuk4TTDqcSpw+lXZxtnoXOd8zUXpkuQyxKXdpcXU22niqdun3rLleUa7rrStdP1o5u7m9yt2W3U3cw9xX2r+00umxvJXcM970H08PdY4nHM452nm6fC85DnL152Xlle+70eT7OcJp7WMG3I28Rb4L3Le2A6Pj1l+s7pAz7GPgKfep+Hvqa+It89viN+1n6Zfgf8nvs7+sv9j/i/4XnyFvFOBWABwQHlAb2BGoGzA2sDHwSZBKUHNQWNBbsGLww+FUIMCQ1ZH3KTb8AX8hv5YzPcZyya0RXKCJ0VWhv6MMwmTB7WEY6GzwjfEH5vpvlM6cy2CIjgR2yIuB9pGZkX+X0UKSoyqi7qUbRTdHF09yzWrORZ+2e9jvGPqYy5O9tqtnJ2Z6xqbFJsY+ybuIC4qriBeIf4RfGXEnQTJAntieTE2MQ9ieNzAudsmjOc5JpUlnRjruXcorkX5unOy553PFk1WZB8OIWYEpeyP+WDIEJQLxhP5aduTR0T8oSbhU9FvqKNolGxt7hKPJLmnVaV9jjdO31D+miGT0Z1xjMJT1IreZEZkrkj801WRNberM/ZcdktOZSclJyjUg1plrQr1zC3KLdPZisrkw3keeZtyhuTh8r35CP5c/PbFWyFTNGjtFKuUA4WTC+oK3hbGFt4uEi9SFrUM99m/ur5IwuCFny9kLBQuLCz2Lh4WfHgIr9FuxYji1MXdy4xXVK6ZHhp8NJ9y2jLspb9UOJYUlXyannc8o5Sg9KlpUMrglc0lamUycturvRauWMVYZVkVe9ql9VbVn8qF5VfrHCsqK74sEa45uJXTl/VfPV5bdra3kq3yu3rSOuk626s91m/r0q9akHV0IbwDa0b8Y3lG19tSt50oXpq9Y7NtM3KzQM1YTXtW8y2rNvyoTaj9nqdf13LVv2tq7e+2Sba1r/dd3vzDoMdFTve75TsvLUreFdrvUV99W7S7oLdjxpiG7q/5n7duEd3T8Wej3ulewf2Re/ranRvbNyvv7+yCW1SNo0eSDpw5ZuAb9qb7Zp3tXBaKg7CQeXBJ9+mfHvjUOihzsPcw83fmX+39QjrSHkr0jq/dawto22gPaG97+iMo50dXh1Hvrf/fu8x42N1xzWPV56gnSg98fnkgpPjp2Snnp1OPz3Umdx590z8mWtdUV29Z0PPnj8XdO5Mt1/3yfPe549d8Lxw9CL3Ytslt0utPa49R35w/eFIr1tv62X3y+1XPK509E3rO9Hv03/6asDVc9f41y5dn3m978bsG7duJt0cuCW69fh29u0XdwruTNxdeo94r/y+2v3qB/oP6n+0/rFlwG3g+GDAYM/DWQ/vDgmHnv6U/9OH4dJHzEfVI0YjjY+dHx8bDRq98mTOk+GnsqcTz8p+Vv9563Or59/94vtLz1j82PAL+YvPv655qfNy76uprzrHI8cfvM55PfGm/K3O233vuO+638e9H5ko/ED+UPPR+mPHp9BP9z7nfP78L/eE8/sl0p8zAAAAIGNIUk0AAHolAACAgwAA+f8AAIDpAAB1MAAA6mAAADqYAAAXb5JfxUYAAAB0SURBVHja7NexDYAwDERRX89cLECUEeIBmAPqhBUsZjQViIoKCeSce/u/1nB3+XJAwO8AAF6P1FoNQBIRKaWAgNiA1tp50FQ1ExAX4Mt0LW7DmFXVCIgLuMcw7yAgNkDW9BgjgAACOgDwNesNcAAAAP//AwCb98vQvF4rtAAAAABJRU5ErkJggg=="

/***/ }),
/* 128 */
/***/ (function(module, exports) {

module.exports = "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAYAAABzenr0AAAACXBIWXMAAAsTAAALEwEAmpwYAAAKTWlDQ1BQaG90b3Nob3AgSUNDIHByb2ZpbGUAAHjanVN3WJP3Fj7f92UPVkLY8LGXbIEAIiOsCMgQWaIQkgBhhBASQMWFiApWFBURnEhVxILVCkidiOKgKLhnQYqIWotVXDjuH9yntX167+3t+9f7vOec5/zOec8PgBESJpHmomoAOVKFPDrYH49PSMTJvYACFUjgBCAQ5svCZwXFAADwA3l4fnSwP/wBr28AAgBw1S4kEsfh/4O6UCZXACCRAOAiEucLAZBSAMguVMgUAMgYALBTs2QKAJQAAGx5fEIiAKoNAOz0ST4FANipk9wXANiiHKkIAI0BAJkoRyQCQLsAYFWBUiwCwMIAoKxAIi4EwK4BgFm2MkcCgL0FAHaOWJAPQGAAgJlCLMwAIDgCAEMeE80DIEwDoDDSv+CpX3CFuEgBAMDLlc2XS9IzFLiV0Bp38vDg4iHiwmyxQmEXKRBmCeQinJebIxNI5wNMzgwAABr50cH+OD+Q5+bk4eZm52zv9MWi/mvwbyI+IfHf/ryMAgQAEE7P79pf5eXWA3DHAbB1v2upWwDaVgBo3/ldM9sJoFoK0Hr5i3k4/EAenqFQyDwdHAoLC+0lYqG9MOOLPv8z4W/gi372/EAe/tt68ABxmkCZrcCjg/1xYW52rlKO58sEQjFu9+cj/seFf/2OKdHiNLFcLBWK8ViJuFAiTcd5uVKRRCHJleIS6X8y8R+W/QmTdw0ArIZPwE62B7XLbMB+7gECiw5Y0nYAQH7zLYwaC5EAEGc0Mnn3AACTv/mPQCsBAM2XpOMAALzoGFyolBdMxggAAESggSqwQQcMwRSswA6cwR28wBcCYQZEQAwkwDwQQgbkgBwKoRiWQRlUwDrYBLWwAxqgEZrhELTBMTgN5+ASXIHrcBcGYBiewhi8hgkEQcgIE2EhOogRYo7YIs4IF5mOBCJhSDSSgKQg6YgUUSLFyHKkAqlCapFdSCPyLXIUOY1cQPqQ28ggMor8irxHMZSBslED1AJ1QLmoHxqKxqBz0XQ0D12AlqJr0Rq0Hj2AtqKn0UvodXQAfYqOY4DRMQ5mjNlhXIyHRWCJWBomxxZj5Vg1Vo81Yx1YN3YVG8CeYe8IJAKLgBPsCF6EEMJsgpCQR1hMWEOoJewjtBK6CFcJg4Qxwicik6hPtCV6EvnEeGI6sZBYRqwm7iEeIZ4lXicOE1+TSCQOyZLkTgohJZAySQtJa0jbSC2kU6Q+0hBpnEwm65Btyd7kCLKArCCXkbeQD5BPkvvJw+S3FDrFiOJMCaIkUqSUEko1ZT/lBKWfMkKZoKpRzame1AiqiDqfWkltoHZQL1OHqRM0dZolzZsWQ8ukLaPV0JppZ2n3aC/pdLoJ3YMeRZfQl9Jr6Afp5+mD9HcMDYYNg8dIYigZaxl7GacYtxkvmUymBdOXmchUMNcyG5lnmA+Yb1VYKvYqfBWRyhKVOpVWlX6V56pUVXNVP9V5qgtUq1UPq15WfaZGVbNQ46kJ1Bar1akdVbupNq7OUndSj1DPUV+jvl/9gvpjDbKGhUaghkijVGO3xhmNIRbGMmXxWELWclYD6yxrmE1iW7L57Ex2Bfsbdi97TFNDc6pmrGaRZp3mcc0BDsax4PA52ZxKziHODc57LQMtPy2x1mqtZq1+rTfaetq+2mLtcu0W7eva73VwnUCdLJ31Om0693UJuja6UbqFutt1z+o+02PreekJ9cr1Dund0Uf1bfSj9Rfq79bv0R83MDQINpAZbDE4Y/DMkGPoa5hpuNHwhOGoEctoupHEaKPRSaMnuCbuh2fjNXgXPmasbxxirDTeZdxrPGFiaTLbpMSkxeS+Kc2Ua5pmutG003TMzMgs3KzYrMnsjjnVnGueYb7ZvNv8jYWlRZzFSos2i8eW2pZ8ywWWTZb3rJhWPlZ5VvVW16xJ1lzrLOtt1ldsUBtXmwybOpvLtqitm63Edptt3xTiFI8p0in1U27aMez87ArsmuwG7Tn2YfYl9m32zx3MHBId1jt0O3xydHXMdmxwvOuk4TTDqcSpw+lXZxtnoXOd8zUXpkuQyxKXdpcXU22niqdun3rLleUa7rrStdP1o5u7m9yt2W3U3cw9xX2r+00umxvJXcM970H08PdY4nHM452nm6fC85DnL152Xlle+70eT7OcJp7WMG3I28Rb4L3Le2A6Pj1l+s7pAz7GPgKfep+Hvqa+It89viN+1n6Zfgf8nvs7+sv9j/i/4XnyFvFOBWABwQHlAb2BGoGzA2sDHwSZBKUHNQWNBbsGLww+FUIMCQ1ZH3KTb8AX8hv5YzPcZyya0RXKCJ0VWhv6MMwmTB7WEY6GzwjfEH5vpvlM6cy2CIjgR2yIuB9pGZkX+X0UKSoyqi7qUbRTdHF09yzWrORZ+2e9jvGPqYy5O9tqtnJ2Z6xqbFJsY+ybuIC4qriBeIf4RfGXEnQTJAntieTE2MQ9ieNzAudsmjOc5JpUlnRjruXcorkX5unOy553PFk1WZB8OIWYEpeyP+WDIEJQLxhP5aduTR0T8oSbhU9FvqKNolGxt7hKPJLmnVaV9jjdO31D+miGT0Z1xjMJT1IreZEZkrkj801WRNberM/ZcdktOZSclJyjUg1plrQr1zC3KLdPZisrkw3keeZtyhuTh8r35CP5c/PbFWyFTNGjtFKuUA4WTC+oK3hbGFt4uEi9SFrUM99m/ur5IwuCFny9kLBQuLCz2Lh4WfHgIr9FuxYji1MXdy4xXVK6ZHhp8NJ9y2jLspb9UOJYUlXyannc8o5Sg9KlpUMrglc0lamUycturvRauWMVYZVkVe9ql9VbVn8qF5VfrHCsqK74sEa45uJXTl/VfPV5bdra3kq3yu3rSOuk626s91m/r0q9akHV0IbwDa0b8Y3lG19tSt50oXpq9Y7NtM3KzQM1YTXtW8y2rNvyoTaj9nqdf13LVv2tq7e+2Sba1r/dd3vzDoMdFTve75TsvLUreFdrvUV99W7S7oLdjxpiG7q/5n7duEd3T8Wej3ulewf2Re/ranRvbNyvv7+yCW1SNo0eSDpw5ZuAb9qb7Zp3tXBaKg7CQeXBJ9+mfHvjUOihzsPcw83fmX+39QjrSHkr0jq/dawto22gPaG97+iMo50dXh1Hvrf/fu8x42N1xzWPV56gnSg98fnkgpPjp2Snnp1OPz3Umdx590z8mWtdUV29Z0PPnj8XdO5Mt1/3yfPe549d8Lxw9CL3Ytslt0utPa49R35w/eFIr1tv62X3y+1XPK509E3rO9Hv03/6asDVc9f41y5dn3m978bsG7duJt0cuCW69fh29u0XdwruTNxdeo94r/y+2v3qB/oP6n+0/rFlwG3g+GDAYM/DWQ/vDgmHnv6U/9OH4dJHzEfVI0YjjY+dHx8bDRq98mTOk+GnsqcTz8p+Vv9563Or59/94vtLz1j82PAL+YvPv655qfNy76uprzrHI8cfvM55PfGm/K3O233vuO+638e9H5ko/ED+UPPR+mPHp9BP9z7nfP78L/eE8/sl0p8zAAAAIGNIUk0AAHolAACAgwAA+f8AAIDpAAB1MAAA6mAAADqYAAAXb5JfxUYAAABzSURBVHjaYvz//z/DQALGUQcMOgcwMjJS3ZIZM2asZmRkDGFgYGBIS0tjHHXA8HaA5TI7uIHHow4xjjpg+DoA2aKEz9Fh6enpq0cdMHwdgM3QUQcMXwdYLbfHa9moA0YdMOqA4e+A0Z7RiHMAAAAA//8DAH/Cv9Dofr2QAAAAAElFTkSuQmCC"

/***/ }),
/* 129 */
/***/ (function(module, exports) {

module.exports = "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAYAAABzenr0AAAACXBIWXMAAAsTAAALEwEAmpwYAAAKTWlDQ1BQaG90b3Nob3AgSUNDIHByb2ZpbGUAAHjanVN3WJP3Fj7f92UPVkLY8LGXbIEAIiOsCMgQWaIQkgBhhBASQMWFiApWFBURnEhVxILVCkidiOKgKLhnQYqIWotVXDjuH9yntX167+3t+9f7vOec5/zOec8PgBESJpHmomoAOVKFPDrYH49PSMTJvYACFUjgBCAQ5svCZwXFAADwA3l4fnSwP/wBr28AAgBw1S4kEsfh/4O6UCZXACCRAOAiEucLAZBSAMguVMgUAMgYALBTs2QKAJQAAGx5fEIiAKoNAOz0ST4FANipk9wXANiiHKkIAI0BAJkoRyQCQLsAYFWBUiwCwMIAoKxAIi4EwK4BgFm2MkcCgL0FAHaOWJAPQGAAgJlCLMwAIDgCAEMeE80DIEwDoDDSv+CpX3CFuEgBAMDLlc2XS9IzFLiV0Bp38vDg4iHiwmyxQmEXKRBmCeQinJebIxNI5wNMzgwAABr50cH+OD+Q5+bk4eZm52zv9MWi/mvwbyI+IfHf/ryMAgQAEE7P79pf5eXWA3DHAbB1v2upWwDaVgBo3/ldM9sJoFoK0Hr5i3k4/EAenqFQyDwdHAoLC+0lYqG9MOOLPv8z4W/gi372/EAe/tt68ABxmkCZrcCjg/1xYW52rlKO58sEQjFu9+cj/seFf/2OKdHiNLFcLBWK8ViJuFAiTcd5uVKRRCHJleIS6X8y8R+W/QmTdw0ArIZPwE62B7XLbMB+7gECiw5Y0nYAQH7zLYwaC5EAEGc0Mnn3AACTv/mPQCsBAM2XpOMAALzoGFyolBdMxggAAESggSqwQQcMwRSswA6cwR28wBcCYQZEQAwkwDwQQgbkgBwKoRiWQRlUwDrYBLWwAxqgEZrhELTBMTgN5+ASXIHrcBcGYBiewhi8hgkEQcgIE2EhOogRYo7YIs4IF5mOBCJhSDSSgKQg6YgUUSLFyHKkAqlCapFdSCPyLXIUOY1cQPqQ28ggMor8irxHMZSBslED1AJ1QLmoHxqKxqBz0XQ0D12AlqJr0Rq0Hj2AtqKn0UvodXQAfYqOY4DRMQ5mjNlhXIyHRWCJWBomxxZj5Vg1Vo81Yx1YN3YVG8CeYe8IJAKLgBPsCF6EEMJsgpCQR1hMWEOoJewjtBK6CFcJg4Qxwicik6hPtCV6EvnEeGI6sZBYRqwm7iEeIZ4lXicOE1+TSCQOyZLkTgohJZAySQtJa0jbSC2kU6Q+0hBpnEwm65Btyd7kCLKArCCXkbeQD5BPkvvJw+S3FDrFiOJMCaIkUqSUEko1ZT/lBKWfMkKZoKpRzame1AiqiDqfWkltoHZQL1OHqRM0dZolzZsWQ8ukLaPV0JppZ2n3aC/pdLoJ3YMeRZfQl9Jr6Afp5+mD9HcMDYYNg8dIYigZaxl7GacYtxkvmUymBdOXmchUMNcyG5lnmA+Yb1VYKvYqfBWRyhKVOpVWlX6V56pUVXNVP9V5qgtUq1UPq15WfaZGVbNQ46kJ1Bar1akdVbupNq7OUndSj1DPUV+jvl/9gvpjDbKGhUaghkijVGO3xhmNIRbGMmXxWELWclYD6yxrmE1iW7L57Ex2Bfsbdi97TFNDc6pmrGaRZp3mcc0BDsax4PA52ZxKziHODc57LQMtPy2x1mqtZq1+rTfaetq+2mLtcu0W7eva73VwnUCdLJ31Om0693UJuja6UbqFutt1z+o+02PreekJ9cr1Dund0Uf1bfSj9Rfq79bv0R83MDQINpAZbDE4Y/DMkGPoa5hpuNHwhOGoEctoupHEaKPRSaMnuCbuh2fjNXgXPmasbxxirDTeZdxrPGFiaTLbpMSkxeS+Kc2Ua5pmutG003TMzMgs3KzYrMnsjjnVnGueYb7ZvNv8jYWlRZzFSos2i8eW2pZ8ywWWTZb3rJhWPlZ5VvVW16xJ1lzrLOtt1ldsUBtXmwybOpvLtqitm63Edptt3xTiFI8p0in1U27aMez87ArsmuwG7Tn2YfYl9m32zx3MHBId1jt0O3xydHXMdmxwvOuk4TTDqcSpw+lXZxtnoXOd8zUXpkuQyxKXdpcXU22niqdun3rLleUa7rrStdP1o5u7m9yt2W3U3cw9xX2r+00umxvJXcM970H08PdY4nHM452nm6fC85DnL152Xlle+70eT7OcJp7WMG3I28Rb4L3Le2A6Pj1l+s7pAz7GPgKfep+Hvqa+It89viN+1n6Zfgf8nvs7+sv9j/i/4XnyFvFOBWABwQHlAb2BGoGzA2sDHwSZBKUHNQWNBbsGLww+FUIMCQ1ZH3KTb8AX8hv5YzPcZyya0RXKCJ0VWhv6MMwmTB7WEY6GzwjfEH5vpvlM6cy2CIjgR2yIuB9pGZkX+X0UKSoyqi7qUbRTdHF09yzWrORZ+2e9jvGPqYy5O9tqtnJ2Z6xqbFJsY+ybuIC4qriBeIf4RfGXEnQTJAntieTE2MQ9ieNzAudsmjOc5JpUlnRjruXcorkX5unOy553PFk1WZB8OIWYEpeyP+WDIEJQLxhP5aduTR0T8oSbhU9FvqKNolGxt7hKPJLmnVaV9jjdO31D+miGT0Z1xjMJT1IreZEZkrkj801WRNberM/ZcdktOZSclJyjUg1plrQr1zC3KLdPZisrkw3keeZtyhuTh8r35CP5c/PbFWyFTNGjtFKuUA4WTC+oK3hbGFt4uEi9SFrUM99m/ur5IwuCFny9kLBQuLCz2Lh4WfHgIr9FuxYji1MXdy4xXVK6ZHhp8NJ9y2jLspb9UOJYUlXyannc8o5Sg9KlpUMrglc0lamUycturvRauWMVYZVkVe9ql9VbVn8qF5VfrHCsqK74sEa45uJXTl/VfPV5bdra3kq3yu3rSOuk626s91m/r0q9akHV0IbwDa0b8Y3lG19tSt50oXpq9Y7NtM3KzQM1YTXtW8y2rNvyoTaj9nqdf13LVv2tq7e+2Sba1r/dd3vzDoMdFTve75TsvLUreFdrvUV99W7S7oLdjxpiG7q/5n7duEd3T8Wej3ulewf2Re/ranRvbNyvv7+yCW1SNo0eSDpw5ZuAb9qb7Zp3tXBaKg7CQeXBJ9+mfHvjUOihzsPcw83fmX+39QjrSHkr0jq/dawto22gPaG97+iMo50dXh1Hvrf/fu8x42N1xzWPV56gnSg98fnkgpPjp2Snnp1OPz3Umdx590z8mWtdUV29Z0PPnj8XdO5Mt1/3yfPe549d8Lxw9CL3Ytslt0utPa49R35w/eFIr1tv62X3y+1XPK509E3rO9Hv03/6asDVc9f41y5dn3m978bsG7duJt0cuCW69fh29u0XdwruTNxdeo94r/y+2v3qB/oP6n+0/rFlwG3g+GDAYM/DWQ/vDgmHnv6U/9OH4dJHzEfVI0YjjY+dHx8bDRq98mTOk+GnsqcTz8p+Vv9563Or59/94vtLz1j82PAL+YvPv655qfNy76uprzrHI8cfvM55PfGm/K3O233vuO+638e9H5ko/ED+UPPR+mPHp9BP9z7nfP78L/eE8/sl0p8zAAAAIGNIUk0AAHolAACAgwAA+f8AAIDpAAB1MAAA6mAAADqYAAAXb5JfxUYAAABpSURBVHjaYvz//z/DQALGUQcMOgcwMjJS3RLLZXZwS45FHmQcdcDwdgCyZcejDjGOOmD4OgDZ0ITP0WHp6emrRx0wfB1AyNBRBwwvB1gtt8drwKgDRh0w6oDh74DRntGIcwAAAAD//wMA3JO/0JpuYWoAAAAASUVORK5CYII="

/***/ }),
/* 130 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_vux_loader_src_script_loader_js_vux_loader_src_script_loader_js_vue_loader_lib_selector_type_script_index_0_index_vue__ = __webpack_require__(322);
/* unused harmony namespace reexport */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__babel_loader_vux_loader_src_script_loader_js_vue_loader_lib_selector_type_script_index_0_index_vue__ = __webpack_require__(324);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__vue_loader_lib_template_compiler_index_id_data_v_b2aa79c6_hasScoped_false_buble_transforms_vux_loader_src_before_template_compiler_loader_js_vux_loader_src_template_loader_js_vue_loader_lib_selector_type_template_index_0_index_vue__ = __webpack_require__(325);
function injectStyle (ssrContext) {
  __webpack_require__(321)
}
var normalizeComponent = __webpack_require__(0)
/* script */


/* template */

/* template functional */
var __vue_template_functional__ = false
/* styles */
var __vue_styles__ = injectStyle
/* scopeId */
var __vue_scopeId__ = null
/* moduleIdentifier (server only) */
var __vue_module_identifier__ = null
var Component = normalizeComponent(
  __WEBPACK_IMPORTED_MODULE_1__babel_loader_vux_loader_src_script_loader_js_vue_loader_lib_selector_type_script_index_0_index_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_2__vue_loader_lib_template_compiler_index_id_data_v_b2aa79c6_hasScoped_false_buble_transforms_vux_loader_src_before_template_compiler_loader_js_vux_loader_src_template_loader_js_vue_loader_lib_selector_type_template_index_0_index_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)

/* harmony default export */ __webpack_exports__["a"] = (Component.exports);


/***/ }),
/* 131 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__requestAnimationFrame__ = __webpack_require__(323);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__requestAnimationFrame___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0__requestAnimationFrame__);




var TRANSLATE32 = 'translate(32,32)';
var STROKE_OPACITY = 'stroke-opacity';
var ROUND = 'round';
var INDEFINITE = 'indefinite';
var DURATION = '750ms';
var NONE = 'none';
var SHORTCUTS = {
  a: 'animate',
  an: 'attributeName',
  at: 'animateTransform',
  c: 'circle',
  da: 'stroke-dasharray',
  os: 'stroke-dashoffset',
  f: 'fill',
  lc: 'stroke-linecap',
  rc: 'repeatCount',
  sw: 'stroke-width',
  t: 'transform',
  v: 'values'
};

var SPIN_ANIMATION = {
  v: '0,32,32;360,32,32',
  an: 'transform',
  type: 'rotate',
  rc: INDEFINITE,
  dur: DURATION
};

function createSvgElement(tagName, data, parent, spinnerName, size) {
  var ele = document.createElement(SHORTCUTS[tagName] || tagName);
  var k, x, y;

  for (k in data) {
    if (Object.prototype.toString.call(data[k]) === '[object Array]') {
      for (x = 0; x < data[k].length; x++) {
        if (data[k][x].fn) {
          for (y = 0; y < data[k][x].t; y++) {
            createSvgElement(k, data[k][x].fn(y, spinnerName), ele, spinnerName);
          }
        } else {
          createSvgElement(k, data[k][x], ele, spinnerName);
        }
      }
    } else {
      setSvgAttribute(ele, k, data[k]);
    }
  }
  if (size && size !== '28px') {
    setSvgAttribute(ele, 'style', 'width: ' + size + '; height: ' + size);
  }
  parent.appendChild(ele);
}

function setSvgAttribute(ele, k, v) {
  ele.setAttribute(SHORTCUTS[k] || k, v);
}

function animationValues(strValues, i) {
  var values = strValues.split(';');
  var back = values.slice(i);
  var front = values.slice(0, values.length - back.length);
  values = back.concat(front).reverse();
  return values.join(';') + ';' + values[0];
}

var IOS_SPINNER = {
  sw: 4,
  lc: ROUND,
  line: [{
    fn: function fn(i, spinnerName) {
      return {
        y1: spinnerName === 'ios' ? 17 : 12,
        y2: spinnerName === 'ios' ? 29 : 20,
        t: TRANSLATE32 + ' rotate(' + (30 * i + (i < 6 ? 180 : -180)) + ')',
        a: [{
          fn: function fn() {
            return {
              an: STROKE_OPACITY,
              dur: DURATION,
              v: animationValues('0;.1;.15;.25;.35;.45;.55;.65;.7;.85;1', i),
              rc: INDEFINITE
            };
          },

          t: 1
        }]
      };
    },

    t: 12
  }]
};

var spinners = {

  android: {
    c: [{
      sw: 6,
      da: 128,
      os: 82,
      r: 26,
      cx: 32,
      cy: 32,
      f: NONE
    }]
  },

  ios: IOS_SPINNER,

  'ios-small': IOS_SPINNER,

  bubbles: {
    sw: 0,
    c: [{
      fn: function fn(i) {
        return {
          cx: 24 * Math.cos(2 * Math.PI * i / 8),
          cy: 24 * Math.sin(2 * Math.PI * i / 8),
          t: TRANSLATE32,
          a: [{
            fn: function fn() {
              return {
                an: 'r',
                dur: DURATION,
                v: animationValues('1;2;3;4;5;6;7;8', i),
                rc: INDEFINITE
              };
            },

            t: 1
          }]
        };
      },

      t: 8
    }]
  },

  circles: {

    c: [{
      fn: function fn(i) {
        return {
          r: 5,
          cx: 24 * Math.cos(2 * Math.PI * i / 8),
          cy: 24 * Math.sin(2 * Math.PI * i / 8),
          t: TRANSLATE32,
          sw: 0,
          a: [{
            fn: function fn() {
              return {
                an: 'fill-opacity',
                dur: DURATION,
                v: animationValues('.3;.3;.3;.4;.7;.85;.9;1', i),
                rc: INDEFINITE
              };
            },

            t: 1
          }]
        };
      },

      t: 8
    }]
  },

  crescent: {
    c: [{
      sw: 4,
      da: 128,
      os: 82,
      r: 26,
      cx: 32,
      cy: 32,
      f: NONE,
      at: [SPIN_ANIMATION]
    }]
  },

  dots: {

    c: [{
      fn: function fn(i) {
        return {
          cx: 16 + 16 * i,
          cy: 32,
          sw: 0,
          a: [{
            fn: function fn() {
              return {
                an: 'fill-opacity',
                dur: DURATION,
                v: animationValues('.5;.6;.8;1;.8;.6;.5', i),
                rc: INDEFINITE
              };
            },

            t: 1
          }, {
            fn: function fn() {
              return {
                an: 'r',
                dur: DURATION,
                v: animationValues('4;5;6;5;4;3;3', i),
                rc: INDEFINITE
              };
            },

            t: 1
          }]
        };
      },

      t: 3
    }]
  },

  lines: {
    sw: 7,
    lc: ROUND,
    line: [{
      fn: function fn(i) {
        return {
          x1: 10 + i * 14,
          x2: 10 + i * 14,
          a: [{
            fn: function fn() {
              return {
                an: 'y1',
                dur: DURATION,
                v: animationValues('16;18;28;18;16', i),
                rc: INDEFINITE
              };
            },

            t: 1
          }, {
            fn: function fn() {
              return {
                an: 'y2',
                dur: DURATION,
                v: animationValues('48;44;36;46;48', i),
                rc: INDEFINITE
              };
            },

            t: 1
          }, {
            fn: function fn() {
              return {
                an: STROKE_OPACITY,
                dur: DURATION,
                v: animationValues('1;.8;.5;.4;1', i),
                rc: INDEFINITE
              };
            },

            t: 1
          }]
        };
      },

      t: 4
    }]
  },

  ripple: {
    f: NONE,
    'fill-rule': 'evenodd',
    sw: 3,
    circle: [{
      fn: function fn(i) {
        return {
          cx: 32,
          cy: 32,
          a: [{
            fn: function fn() {
              return {
                an: 'r',
                begin: i * -1 + 's',
                dur: '2s',
                v: '0;24',
                keyTimes: '0;1',
                keySplines: '0.1,0.2,0.3,1',
                calcMode: 'spline',
                rc: INDEFINITE
              };
            },

            t: 1
          }, {
            fn: function fn() {
              return {
                an: STROKE_OPACITY,
                begin: i * -1 + 's',
                dur: '2s',
                v: '.2;1;.2;0',
                rc: INDEFINITE
              };
            },

            t: 1
          }]
        };
      },

      t: 2
    }]
  },

  spiral: {
    defs: [{
      linearGradient: [{
        id: 'sGD',
        gradientUnits: 'userSpaceOnUse',
        x1: 55,
        y1: 46,
        x2: 2,
        y2: 46,
        stop: [{
          offset: 0.1,
          class: 'stop1'
        }, {
          offset: 1,
          class: 'stop2'
        }]
      }]
    }],
    g: [{
      sw: 4,
      lc: ROUND,
      f: NONE,
      path: [{
        stroke: 'url(#sGD)',
        d: 'M4,32 c0,15,12,28,28,28c8,0,16-4,21-9'
      }, {
        d: 'M60,32 C60,16,47.464,4,32,4S4,16,4,32'
      }],
      at: [SPIN_ANIMATION]
    }]
  }

};

var animations = {
  android: function android(ele) {
    var self = this;

    this.stop = false;

    var rIndex = 0;
    var rotateCircle = 0;
    var startTime;
    var svgEle = ele.querySelector('g');
    var circleEle = ele.querySelector('circle');

    function run() {
      if (self.stop) return;

      var v = easeInOutCubic(Date.now() - startTime, 650);
      var scaleX = 1;
      var translateX = 0;
      var dasharray = 188 - 58 * v;
      var dashoffset = 182 - 182 * v;

      if (rIndex % 2) {
        scaleX = -1;
        translateX = -64;
        dasharray = 128 - -58 * v;
        dashoffset = 182 * v;
      }

      var rotateLine = [0, -101, -90, -11, -180, 79, -270, -191][rIndex];

      setSvgAttribute(circleEle, 'da', Math.max(Math.min(dasharray, 188), 128));
      setSvgAttribute(circleEle, 'os', Math.max(Math.min(dashoffset, 182), 0));
      setSvgAttribute(circleEle, 't', 'scale(' + scaleX + ',1) translate(' + translateX + ',0) rotate(' + rotateLine + ',32,32)');

      rotateCircle += 4.1;
      if (rotateCircle > 359) rotateCircle = 0;
      setSvgAttribute(svgEle, 't', 'rotate(' + rotateCircle + ',32,32)');

      if (v >= 1) {
        rIndex++;
        if (rIndex > 7) rIndex = 0;
        startTime = Date.now();
      }

      window.requestAnimationFrame(run);
    }

    return function () {
      startTime = Date.now();
      run();
      return self;
    };
  }
};

function easeInOutCubic(t, c) {
  t /= c / 2;
  if (t < 1) return 1 / 2 * t * t * t;
  t -= 2;
  return 1 / 2 * (t * t * t + 2);
}

/* harmony default export */ __webpack_exports__["a"] = (function (el, icon, size) {
  var spinnerName, anim;
  spinnerName = icon;
  var container = document.createElement('div');
  createSvgElement('svg', {
    viewBox: '0 0 64 64',
    g: [spinners[spinnerName]]
  }, container, spinnerName, size);

  el.innerHTML = container.innerHTML;
  start();
  function start() {
    if (animations[spinnerName]) {
      anim = animations[spinnerName](el)();
    }
  }
  return el;
});

/***/ }),
/* 132 */,
/* 133 */,
/* 134 */,
/* 135 */,
/* 136 */,
/* 137 */,
/* 138 */,
/* 139 */,
/* 140 */,
/* 141 */,
/* 142 */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),
/* 143 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";


/* unused harmony default export */ var _unused_webpack_default_export = ({
  name: 'grid',
  methods: {
    countColumn: function countColumn() {
      this.childrenSize = this.$children.length;
      this.$children.forEach(function (c, index) {
        return c.index = index;
      });
    }
  },
  props: {
    rows: {
      type: Number,
      validator: function validator() {
        if (false) {
          console.warn('[VUX warn] Grid rows 属性已经废弃，使用 cols 代替。单行列数为自动计算');
        }
        return true;
      }
    },
    cols: {
      type: Number
    },
    showLrBorders: {
      type: Boolean,
      default: true
    },
    showVerticalDividers: {
      type: Boolean,
      default: true
    }
  },
  computed: {
    column: function column() {
      return this.cols || this.childrenSize;
    }
  },
  data: function data() {
    return {
      childrenSize: 3
    };
  }
});

/***/ }),
/* 144 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";


/* harmony default export */ __webpack_exports__["a"] = ({
  name: 'grid',
  methods: {
    countColumn: function countColumn() {
      this.childrenSize = this.$children.length;
      this.$children.forEach(function (c, index) {
        return c.index = index;
      });
    }
  },
  props: {
    rows: {
      type: Number,
      validator: function validator() {
        if (false) {
          console.warn('[VUX warn] Grid rows 属性已经废弃，使用 cols 代替。单行列数为自动计算');
        }
        return true;
      }
    },
    cols: {
      type: Number
    },
    showLrBorders: {
      type: Boolean,
      default: true
    },
    showVerticalDividers: {
      type: Boolean,
      default: true
    }
  },
  computed: {
    column: function column() {
      return this.cols || this.childrenSize;
    }
  },
  data: function data() {
    return {
      childrenSize: 3
    };
  }
});

/***/ }),
/* 145 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{staticClass:"weui-grids",class:{
    'vux-grid-no-lr-borders': !_vm.showLrBorders
  }},[_vm._t("default")],2)}
var staticRenderFns = []
var esExports = { render: render, staticRenderFns: staticRenderFns }
/* harmony default export */ __webpack_exports__["a"] = (esExports);

/***/ }),
/* 146 */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),
/* 147 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__libs_router__ = __webpack_require__(10);




/* unused harmony default export */ var _unused_webpack_default_export = ({
  name: 'grid-item',
  props: ['icon', 'label', 'link'],
  created: function created() {
    this.$parent.countColumn();
  },
  mounted: function mounted() {
    this.$slots.icon && (this.hasIconSlot = true);
    this.$slots.label && (this.hasLabelSlot = true);
  },
  destroyed: function destroyed() {
    this.$parent.countColumn();
  },

  computed: {
    isLast: function isLast() {
      return !((this.index + 1) % this.$parent.column);
    },
    style: function style() {
      var column = this.$parent.column;
      if (!column || column === 3) {
        return;
      }
      var styles = {};
      styles.width = 100 / column + '%';
      return styles;
    }
  },
  methods: {
    onClick: function onClick() {
      this.$emit('on-item-click');
      Object(__WEBPACK_IMPORTED_MODULE_0__libs_router__["b" /* go */])(this.link, this.$router);
    }
  },
  data: function data() {
    return {
      index: 0,
      hasIconSlot: false,
      hasLabelSlot: false
    };
  }
});

/***/ }),
/* 148 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__libs_router__ = __webpack_require__(10);




/* harmony default export */ __webpack_exports__["a"] = ({
  name: 'grid-item',
  props: ['icon', 'label', 'link'],
  created: function created() {
    this.$parent.countColumn();
  },
  mounted: function mounted() {
    this.$slots.icon && (this.hasIconSlot = true);
    this.$slots.label && (this.hasLabelSlot = true);
  },
  destroyed: function destroyed() {
    this.$parent.countColumn();
  },

  computed: {
    isLast: function isLast() {
      return !((this.index + 1) % this.$parent.column);
    },
    style: function style() {
      var column = this.$parent.column;
      if (!column || column === 3) {
        return;
      }
      var styles = {};
      styles.width = 100 / column + '%';
      return styles;
    }
  },
  methods: {
    onClick: function onClick() {
      this.$emit('on-item-click');
      Object(__WEBPACK_IMPORTED_MODULE_0__libs_router__["b" /* go */])(this.link, this.$router);
    }
  },
  data: function data() {
    return {
      index: 0,
      hasIconSlot: false,
      hasLabelSlot: false
    };
  }
});

/***/ }),
/* 149 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('a',{staticClass:"weui-grid",class:{
    'vux-grid-item-no-border': (_vm.isLast && !_vm.$parent.showLrBorders) || (!_vm.isLast && !_vm.$parent.showVerticalDividers),
  },style:(_vm.style),attrs:{"href":"javascript:;"},on:{"click":_vm.onClick}},[(_vm.hasIconSlot || _vm.icon)?_c('div',{staticClass:"weui-grid__icon"},[_vm._t("icon",[_c('img',{attrs:{"src":_vm.icon,"alt":""}})])],2):_vm._e(),_vm._v(" "),(_vm.hasLabelSlot || _vm.label)?_c('p',{staticClass:"weui-grid__label"},[_vm._t("label",[_c('span',{domProps:{"innerHTML":_vm._s(_vm.label)}})])],2):_vm._e(),_vm._v(" "),_vm._t("default")],2)}
var staticRenderFns = []
var esExports = { render: render, staticRenderFns: staticRenderFns }
/* harmony default export */ __webpack_exports__["a"] = (esExports);

/***/ }),
/* 150 */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),
/* 151 */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),
/* 152 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_x_photoswipe_dist_photoswipe__ = __webpack_require__(83);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_x_photoswipe_dist_photoswipe___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0_x_photoswipe_dist_photoswipe__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_x_photoswipe_dist_photoswipe_ui_default__ = __webpack_require__(84);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_x_photoswipe_dist_photoswipe_ui_default___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_1_x_photoswipe_dist_photoswipe_ui_default__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2_object_assign__ = __webpack_require__(28);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2_object_assign___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_2_object_assign__);






/* unused harmony default export */ var _unused_webpack_default_export = ({
  name: 'previewer',
  computed: {
    imgs: function imgs() {
      return this.list.map(function (one) {
        if (!one.msrc) {
          one.msrc = one.src;
        }
        if (typeof one.w === 'undefined') {
          one.w = 0;
          one.h = 0;
        }
        return one;
      });
    }
  },
  watch: {
    imgs: function imgs(newVal, oldVal) {
      if (!this.photoswipe) {
        return;
      }
      if (newVal.length && newVal.length - oldVal.length === -1) {
        var index = this.photoswipe.getCurrentIndex();
        this.photoswipe.invalidateCurrItems();
        this.photoswipe.items.splice(index, 1);
        var goToIndex = index;
        if (goToIndex > this.photoswipe.items.length - 1) {
          goToIndex = 0;
        }
        this.photoswipe.goTo(goToIndex);
        this.photoswipe.updateSize(true);
        this.photoswipe.ui.update();
      } else if (!newVal.length) {
        this.close();
      }
    }
  },
  methods: {
    init: function init(index) {
      var self = this;
      var showItem = this.imgs[index];
      if (!showItem.w || !showItem.h || showItem.w < 5 || showItem.h < 5) {
        var img = new Image();
        img.onload = function () {
          showItem.w = this.width;
          showItem.h = this.height;
          self.doInit(index);
        };
        img.src = showItem.src;
      } else {
        this.doInit(index);
      }
    },
    doInit: function doInit(index) {
      var _this = this;

      var self = this;
      var options = __WEBPACK_IMPORTED_MODULE_2_object_assign___default()({
        history: false,
        shareEl: false,
        tapToClose: true,
        index: index
      }, this.options);
      this.photoswipe = new __WEBPACK_IMPORTED_MODULE_0_x_photoswipe_dist_photoswipe___default.a(this.$el, __WEBPACK_IMPORTED_MODULE_1_x_photoswipe_dist_photoswipe_ui_default___default.a, this.imgs, options);

      this.photoswipe.listen('gettingData', function (index, item) {
        if (!item.w || !item.h || item.w < 1 || item.h < 1) {
          var img = new Image();
          img.onload = function () {
            item.w = this.width;
            item.h = this.height;
            self.photoswipe.updateSize(true);
          };
          img.src = item.src;
        }
      });

      this.photoswipe.init();
      this.photoswipe.listen('close', function () {
        _this.$emit('on-close');
      });
      this.photoswipe.listen('afterChange', function (a, b) {
        _this.$emit('on-index-change', {
          currentIndex: _this.photoswipe.getCurrentIndex()
        });
      });
    },
    show: function show(index) {
      this.init(index);
    },
    getCurrentIndex: function getCurrentIndex() {
      return this.photoswipe.getCurrentIndex();
    },
    close: function close() {
      this.photoswipe.close();
    },
    goTo: function goTo(index) {
      this.photoswipe.goTo(index);
    },
    prev: function prev() {
      this.photoswipe.prev();
    },
    next: function next() {
      this.photoswipe.next();
    }
  },
  props: {
    list: {
      type: Array,
      required: true
    },
    index: {
      type: Number,
      default: 0
    },
    options: {
      type: Object,
      default: function _default() {
        return {};
      }
    }
  }
});

/***/ }),
/* 153 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_x_photoswipe_dist_photoswipe__ = __webpack_require__(83);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_x_photoswipe_dist_photoswipe___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0_x_photoswipe_dist_photoswipe__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_x_photoswipe_dist_photoswipe_ui_default__ = __webpack_require__(84);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_x_photoswipe_dist_photoswipe_ui_default___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_1_x_photoswipe_dist_photoswipe_ui_default__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2_object_assign__ = __webpack_require__(28);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2_object_assign___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_2_object_assign__);






/* harmony default export */ __webpack_exports__["a"] = ({
  name: 'previewer',
  computed: {
    imgs: function imgs() {
      return this.list.map(function (one) {
        if (!one.msrc) {
          one.msrc = one.src;
        }
        if (typeof one.w === 'undefined') {
          one.w = 0;
          one.h = 0;
        }
        return one;
      });
    }
  },
  watch: {
    imgs: function imgs(newVal, oldVal) {
      if (!this.photoswipe) {
        return;
      }
      if (newVal.length && newVal.length - oldVal.length === -1) {
        var index = this.photoswipe.getCurrentIndex();
        this.photoswipe.invalidateCurrItems();
        this.photoswipe.items.splice(index, 1);
        var goToIndex = index;
        if (goToIndex > this.photoswipe.items.length - 1) {
          goToIndex = 0;
        }
        this.photoswipe.goTo(goToIndex);
        this.photoswipe.updateSize(true);
        this.photoswipe.ui.update();
      } else if (!newVal.length) {
        this.close();
      }
    }
  },
  methods: {
    init: function init(index) {
      var self = this;
      var showItem = this.imgs[index];
      if (!showItem.w || !showItem.h || showItem.w < 5 || showItem.h < 5) {
        var img = new Image();
        img.onload = function () {
          showItem.w = this.width;
          showItem.h = this.height;
          self.doInit(index);
        };
        img.src = showItem.src;
      } else {
        this.doInit(index);
      }
    },
    doInit: function doInit(index) {
      var _this = this;

      var self = this;
      var options = __WEBPACK_IMPORTED_MODULE_2_object_assign___default()({
        history: false,
        shareEl: false,
        tapToClose: true,
        index: index
      }, this.options);
      this.photoswipe = new __WEBPACK_IMPORTED_MODULE_0_x_photoswipe_dist_photoswipe___default.a(this.$el, __WEBPACK_IMPORTED_MODULE_1_x_photoswipe_dist_photoswipe_ui_default___default.a, this.imgs, options);

      this.photoswipe.listen('gettingData', function (index, item) {
        if (!item.w || !item.h || item.w < 1 || item.h < 1) {
          var img = new Image();
          img.onload = function () {
            item.w = this.width;
            item.h = this.height;
            self.photoswipe.updateSize(true);
          };
          img.src = item.src;
        }
      });

      this.photoswipe.init();
      this.photoswipe.listen('close', function () {
        _this.$emit('on-close');
      });
      this.photoswipe.listen('afterChange', function (a, b) {
        _this.$emit('on-index-change', {
          currentIndex: _this.photoswipe.getCurrentIndex()
        });
      });
    },
    show: function show(index) {
      this.init(index);
    },
    getCurrentIndex: function getCurrentIndex() {
      return this.photoswipe.getCurrentIndex();
    },
    close: function close() {
      this.photoswipe.close();
    },
    goTo: function goTo(index) {
      this.photoswipe.goTo(index);
    },
    prev: function prev() {
      this.photoswipe.prev();
    },
    next: function next() {
      this.photoswipe.next();
    }
  },
  props: {
    list: {
      type: Array,
      required: true
    },
    index: {
      type: Number,
      default: 0
    },
    options: {
      type: Object,
      default: function _default() {
        return {};
      }
    }
  }
});

/***/ }),
/* 154 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{staticClass:"pswp vux-previewer",attrs:{"tabindex":"-1","role":"dialog","aria-hidden":"true"}},[_c('div',{staticClass:"pswp__bg"}),_vm._v(" "),_c('div',{staticClass:"pswp__scroll-wrap"},[_vm._m(0),_vm._v(" "),_c('div',{staticClass:"pswp__ui pswp__ui--hidden"},[_c('div',{staticClass:"pswp__top-bar"},[_c('div',{staticClass:"pswp__counter"}),_vm._v(" "),_vm._t("button-after"),_vm._v(" "),_c('button',{staticClass:"pswp__button pswp__button--close",attrs:{"title":"Close (Esc)"}}),_vm._v(" "),_c('button',{staticClass:"pswp__button pswp__button--share",attrs:{"title":"Share"}}),_vm._v(" "),_c('button',{staticClass:"pswp__button pswp__button--fs",attrs:{"title":"Toggle fullscreen"}}),_vm._v(" "),_c('button',{staticClass:"pswp__button pswp__button--zoom",attrs:{"title":"Zoom in/out"}}),_vm._v(" "),_vm._t("button-before"),_vm._v(" "),_vm._m(1)],2),_vm._v(" "),_vm._m(2),_vm._v(" "),_c('button',{staticClass:"pswp__button pswp__button--arrow--left",attrs:{"title":"Previous (arrow left)"}}),_vm._v(" "),_c('button',{staticClass:"pswp__button pswp__button--arrow--right",attrs:{"title":"Next (arrow right)"}}),_vm._v(" "),_vm._m(3)])])])}
var staticRenderFns = [function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{staticClass:"pswp__container"},[_c('div',{staticClass:"pswp__item"}),_vm._v(" "),_c('div',{staticClass:"pswp__item"}),_vm._v(" "),_c('div',{staticClass:"pswp__item"})])},function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{staticClass:"pswp__preloader"},[_c('div',{staticClass:"pswp__preloader__icn"},[_c('div',{staticClass:"pswp__preloader__cut"},[_c('div',{staticClass:"pswp__preloader__donut"})])])])},function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{staticClass:"pswp__share-modal pswp__share-modal--hidden pswp__single-tap"},[_c('div',{staticClass:"pswp__share-tooltip"})])},function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{staticClass:"pswp__caption"},[_c('div',{staticClass:"pswp__caption__center"})])}]
var esExports = { render: render, staticRenderFns: staticRenderFns }
/* harmony default export */ __webpack_exports__["a"] = (esExports);

/***/ }),
/* 155 */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),
/* 156 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_object_keys__ = __webpack_require__(18);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_object_keys___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_object_keys__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__popup__ = __webpack_require__(85);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__libs_dom__ = __webpack_require__(133);






/* unused harmony default export */ var _unused_webpack_default_export = ({
  name: 'popup',
  props: {
    value: Boolean,
    height: {
      type: String,
      default: 'auto'
    },
    width: {
      type: String,
      default: 'auto'
    },
    showMask: {
      type: Boolean,
      default: true
    },
    isTransparent: Boolean,
    hideOnBlur: {
      type: Boolean,
      default: true
    },
    position: {
      type: String,
      default: 'bottom'
    },
    maxHeight: String,
    popupStyle: Object,
    hideOnDeactivated: {
      type: Boolean,
      default: true
    },
    shouldRerenderOnShow: {
      type: Boolean,
      default: false
    },
    shouldScrollTopOnShow: {
      type: Boolean,
      default: false
    }
  },
  created: function created() {
    if (this.$vux && this.$vux.config && this.$vux.config.$layout === 'VIEW_BOX') {
      this.layout = 'VIEW_BOX';
    }
  },
  mounted: function mounted() {
    var _this2 = this;

    this.$overflowScrollingList = document.querySelectorAll('.vux-fix-safari-overflow-scrolling');
    this.$nextTick(function () {
      var _this = _this2;
      _this2.popup = new __WEBPACK_IMPORTED_MODULE_1__popup__["a" /* default */]({
        showMask: _this.showMask,
        container: _this.$el,
        hideOnBlur: _this.hideOnBlur,
        onOpen: function onOpen() {
          _this.fixSafariOverflowScrolling('auto');
          _this.show = true;
        },
        onClose: function onClose() {
          _this.show = false;
          if (window.__$vuxPopups && __WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_object_keys___default()(window.__$vuxPopups).length > 1) return;
          if (document.querySelector('.vux-popup-dialog.vux-popup-mask-disabled')) return;
          setTimeout(function () {
            _this.fixSafariOverflowScrolling('touch');
          }, 300);
        }
      });
      if (_this2.value) {
        _this2.popup.show();
      }
      _this2.initialShow = false;
    });
  },
  deactivated: function deactivated() {
    if (this.hideOnDeactivated) {
      this.show = false;
    }
    this.removeModalClassName();
  },

  methods: {
    fixSafariOverflowScrolling: function fixSafariOverflowScrolling(type) {
      if (!this.$overflowScrollingList.length) return;

      for (var i = 0; i < this.$overflowScrollingList.length; i++) {
        this.$overflowScrollingList[i].style.webkitOverflowScrolling = type;
      }
    },
    removeModalClassName: function removeModalClassName() {
      this.layout === 'VIEW_BOX' && __WEBPACK_IMPORTED_MODULE_2__libs_dom__["a" /* default */].removeClass(document.body, 'vux-modal-open');
    },
    doShow: function doShow() {
      this.popup && this.popup.show();
      this.$emit('on-show');
      this.fixSafariOverflowScrolling('auto');
      this.layout === 'VIEW_BOX' && __WEBPACK_IMPORTED_MODULE_2__libs_dom__["a" /* default */].addClass(document.body, 'vux-modal-open');
      if (!this.hasFirstShow) {
        this.$emit('on-first-show');
        this.hasFirstShow = true;
      }
    },
    scrollTop: function scrollTop() {
      var _this3 = this;

      this.$nextTick(function () {
        _this3.$el.scrollTop = 0;
        var box = _this3.$el.querySelectorAll('.vux-scrollable');
        if (box.length) {
          for (var i = 0; i < box.length; i++) {
            box[i].scrollTop = 0;
          }
        }
      });
    }
  },
  data: function data() {
    return {
      layout: '',
      initialShow: true,
      hasFirstShow: false,
      shouldRenderBody: true,
      show: this.value
    };
  },

  computed: {
    styles: function styles() {
      var styles = {};
      if (!this.position || this.position === 'bottom' || this.position === 'top') {
        styles.height = this.height;
      } else {
        styles.width = this.width;
      }

      if (this.maxHeight) {
        styles['max-height'] = this.maxHeight;
      }

      this.isTransparent && (styles['background'] = 'transparent');
      if (this.popupStyle) {
        for (var i in this.popupStyle) {
          styles[i] = this.popupStyle[i];
        }
      }
      return styles;
    }
  },
  watch: {
    value: function value(val) {
      this.show = val;
    },
    show: function show(val) {
      var _this4 = this;

      this.$emit('input', val);
      if (val) {
        if (this.shouldRerenderOnShow) {
          this.shouldRenderBody = false;
          this.$nextTick(function () {
            _this4.scrollTop();
            _this4.shouldRenderBody = true;
            _this4.doShow();
          });
        } else {
          if (this.shouldScrollTopOnShow) {
            this.scrollTop();
          }
          this.doShow();
        }
      } else {
        this.$emit('on-hide');
        this.show = false;
        this.popup.hide(false);
        setTimeout(function () {
          if (!document.querySelector('.vux-popup-dialog.vux-popup-show')) {
            _this4.fixSafariOverflowScrolling('touch');
          }
          _this4.removeModalClassName();
        }, 200);
      }
    }
  },
  beforeDestroy: function beforeDestroy() {
    this.popup && this.popup.destroy();
    this.fixSafariOverflowScrolling('touch');
    this.removeModalClassName();
  }
});

/***/ }),
/* 157 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_object_keys__ = __webpack_require__(18);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_object_keys___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_object_keys__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__popup__ = __webpack_require__(85);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__libs_dom__ = __webpack_require__(133);






/* harmony default export */ __webpack_exports__["a"] = ({
  name: 'popup',
  props: {
    value: Boolean,
    height: {
      type: String,
      default: 'auto'
    },
    width: {
      type: String,
      default: 'auto'
    },
    showMask: {
      type: Boolean,
      default: true
    },
    isTransparent: Boolean,
    hideOnBlur: {
      type: Boolean,
      default: true
    },
    position: {
      type: String,
      default: 'bottom'
    },
    maxHeight: String,
    popupStyle: Object,
    hideOnDeactivated: {
      type: Boolean,
      default: true
    },
    shouldRerenderOnShow: {
      type: Boolean,
      default: false
    },
    shouldScrollTopOnShow: {
      type: Boolean,
      default: false
    }
  },
  created: function created() {
    if (this.$vux && this.$vux.config && this.$vux.config.$layout === 'VIEW_BOX') {
      this.layout = 'VIEW_BOX';
    }
  },
  mounted: function mounted() {
    var _this2 = this;

    this.$overflowScrollingList = document.querySelectorAll('.vux-fix-safari-overflow-scrolling');
    this.$nextTick(function () {
      var _this = _this2;
      _this2.popup = new __WEBPACK_IMPORTED_MODULE_1__popup__["a" /* default */]({
        showMask: _this.showMask,
        container: _this.$el,
        hideOnBlur: _this.hideOnBlur,
        onOpen: function onOpen() {
          _this.fixSafariOverflowScrolling('auto');
          _this.show = true;
        },
        onClose: function onClose() {
          _this.show = false;
          if (window.__$vuxPopups && __WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_object_keys___default()(window.__$vuxPopups).length > 1) return;
          if (document.querySelector('.vux-popup-dialog.vux-popup-mask-disabled')) return;
          setTimeout(function () {
            _this.fixSafariOverflowScrolling('touch');
          }, 300);
        }
      });
      if (_this2.value) {
        _this2.popup.show();
      }
      _this2.initialShow = false;
    });
  },
  deactivated: function deactivated() {
    if (this.hideOnDeactivated) {
      this.show = false;
    }
    this.removeModalClassName();
  },

  methods: {
    fixSafariOverflowScrolling: function fixSafariOverflowScrolling(type) {
      if (!this.$overflowScrollingList.length) return;

      for (var i = 0; i < this.$overflowScrollingList.length; i++) {
        this.$overflowScrollingList[i].style.webkitOverflowScrolling = type;
      }
    },
    removeModalClassName: function removeModalClassName() {
      this.layout === 'VIEW_BOX' && __WEBPACK_IMPORTED_MODULE_2__libs_dom__["a" /* default */].removeClass(document.body, 'vux-modal-open');
    },
    doShow: function doShow() {
      this.popup && this.popup.show();
      this.$emit('on-show');
      this.fixSafariOverflowScrolling('auto');
      this.layout === 'VIEW_BOX' && __WEBPACK_IMPORTED_MODULE_2__libs_dom__["a" /* default */].addClass(document.body, 'vux-modal-open');
      if (!this.hasFirstShow) {
        this.$emit('on-first-show');
        this.hasFirstShow = true;
      }
    },
    scrollTop: function scrollTop() {
      var _this3 = this;

      this.$nextTick(function () {
        _this3.$el.scrollTop = 0;
        var box = _this3.$el.querySelectorAll('.vux-scrollable');
        if (box.length) {
          for (var i = 0; i < box.length; i++) {
            box[i].scrollTop = 0;
          }
        }
      });
    }
  },
  data: function data() {
    return {
      layout: '',
      initialShow: true,
      hasFirstShow: false,
      shouldRenderBody: true,
      show: this.value
    };
  },

  computed: {
    styles: function styles() {
      var styles = {};
      if (!this.position || this.position === 'bottom' || this.position === 'top') {
        styles.height = this.height;
      } else {
        styles.width = this.width;
      }

      if (this.maxHeight) {
        styles['max-height'] = this.maxHeight;
      }

      this.isTransparent && (styles['background'] = 'transparent');
      if (this.popupStyle) {
        for (var i in this.popupStyle) {
          styles[i] = this.popupStyle[i];
        }
      }
      return styles;
    }
  },
  watch: {
    value: function value(val) {
      this.show = val;
    },
    show: function show(val) {
      var _this4 = this;

      this.$emit('input', val);
      if (val) {
        if (this.shouldRerenderOnShow) {
          this.shouldRenderBody = false;
          this.$nextTick(function () {
            _this4.scrollTop();
            _this4.shouldRenderBody = true;
            _this4.doShow();
          });
        } else {
          if (this.shouldScrollTopOnShow) {
            this.scrollTop();
          }
          this.doShow();
        }
      } else {
        this.$emit('on-hide');
        this.show = false;
        this.popup.hide(false);
        setTimeout(function () {
          if (!document.querySelector('.vux-popup-dialog.vux-popup-show')) {
            _this4.fixSafariOverflowScrolling('touch');
          }
          _this4.removeModalClassName();
        }, 200);
      }
    }
  },
  beforeDestroy: function beforeDestroy() {
    this.popup && this.popup.destroy();
    this.fixSafariOverflowScrolling('touch');
    this.removeModalClassName();
  }
});

/***/ }),
/* 158 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('transition',{attrs:{"name":("vux-popup-animate-" + _vm.position)}},[_c('div',{directives:[{name:"show",rawName:"v-show",value:(_vm.show && !_vm.initialShow),expression:"show && !initialShow"}],staticClass:"vux-popup-dialog",class:[("vux-popup-" + _vm.position), _vm.show ? 'vux-popup-show' : ''],style:(_vm.styles)},[(_vm.shouldRenderBody)?_vm._t("default"):_vm._e()],2)])}
var staticRenderFns = []
var esExports = { render: render, staticRenderFns: staticRenderFns }
/* harmony default export */ __webpack_exports__["a"] = (esExports);

/***/ }),
/* 159 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";


/* unused harmony default export */ var _unused_webpack_default_export = ({
  name: 'swiper-item',
  mounted: function mounted() {
    var _this = this;

    this.$nextTick(function () {
      _this.$parent.rerender();
    });
  },
  beforeDestroy: function beforeDestroy() {
    var $parent = this.$parent;
    this.$nextTick(function () {
      $parent.rerender();
    });
  }
});

/***/ }),
/* 160 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";


/* harmony default export */ __webpack_exports__["a"] = ({
  name: 'swiper-item',
  mounted: function mounted() {
    var _this = this;

    this.$nextTick(function () {
      _this.$parent.rerender();
    });
  },
  beforeDestroy: function beforeDestroy() {
    var $parent = this.$parent;
    this.$nextTick(function () {
      $parent.rerender();
    });
  }
});

/***/ }),
/* 161 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{staticClass:"vux-swiper-item"},[_vm._t("default")],2)}
var staticRenderFns = []
var esExports = { render: render, staticRenderFns: staticRenderFns }
/* harmony default export */ __webpack_exports__["a"] = (esExports);

/***/ }),
/* 162 */,
/* 163 */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),
/* 164 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_babel_runtime_helpers_typeof__ = __webpack_require__(20);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_babel_runtime_helpers_typeof___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0_babel_runtime_helpers_typeof__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_array_find__ = __webpack_require__(57);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_array_find___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_1_array_find__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__mixins_uuid__ = __webpack_require__(50);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_3__libs_clean_style__ = __webpack_require__(33);







var findByKey = function findByKey(key, options) {
  var _rs = __WEBPACK_IMPORTED_MODULE_1_array_find___default()(options, function (item) {
    return item.key === key;
  });
  return _rs ? _rs.value : key;
};

/* unused harmony default export */ var _unused_webpack_default_export = ({
  name: 'selector',
  mixins: [__WEBPACK_IMPORTED_MODULE_2__mixins_uuid__["a" /* default */]],
  created: function created() {
    if (typeof this.value !== 'undefined') {
      this.currentValue = this.value;
    }
  },
  beforeMount: function beforeMount() {
    this.isIOS = /iPad|iPhone|iPod/.test(window.navigator.userAgent);
  },

  computed: {
    fixIos: function fixIos() {
      return !this.placeholder && this.isEmptyValue(this.value) && this.isIOS && this.title;
    },
    color: function color() {
      return this.showPlaceholder ? '#A9A9A9' : '';
    },
    processOptions: function processOptions() {
      var _this = this;

      if (!this.options.length) {
        return [];
      }

      var isObject = __WEBPACK_IMPORTED_MODULE_0_babel_runtime_helpers_typeof___default()(this.options[0]) === 'object';

      if (isObject && !this.valueMap) {
        return this.options;
      }

      if (isObject && this.valueMap) {
        return this.options.map(function (item) {
          return {
            key: item[_this.valueMap[0]],
            value: item[_this.valueMap[1]]
          };
        });
      }

      return this.options.map(function (item) {
        return {
          key: item,
          value: item
        };
      });
    },
    showPlaceholder: function showPlaceholder() {
      if (this.isEmptyValue(this.value) && this.placeholder) {
        return true;
      }
      return false;
    },
    labelClass: function labelClass() {
      return {
        'vux-cell-justify': this.$parent.labelAlign === 'justify' || this.$parent.$parent.labelAlign === 'justify'
      };
    }
  },
  methods: {
    isEmptyValue: function isEmptyValue(val) {
      return typeof val === 'undefined' || val === '' || val === null;
    },

    cleanStyle: __WEBPACK_IMPORTED_MODULE_3__libs_clean_style__["a" /* default */],
    getFullValue: function getFullValue() {
      var _this2 = this;

      if (!this.value) {
        return null;
      }
      if (!this.options.length) {
        return null;
      }
      if (__WEBPACK_IMPORTED_MODULE_0_babel_runtime_helpers_typeof___default()(this.options[0]) !== 'object') {
        return this.value;
      } else {
        if (!this.valueMap) {
          return this.options.filter(function (one) {
            return one.key === _this2.value;
          });
        } else {
          return this.options.filter(function (one) {
            return one[_this2.valueMap[0]] === _this2.value;
          });
        }
      }
    }
  },
  filters: {
    findByKey: findByKey
  },
  watch: {
    value: function value(val) {
      this.currentValue = val;
    },
    currentValue: function currentValue(val) {
      this.$emit('input', val);
      this.$emit('on-change', val);
    }
  },
  props: {
    title: String,
    direction: String,
    options: {
      type: Array,
      required: true
    },
    name: String,
    placeholder: String,
    readonly: Boolean,
    value: [String, Number, Object, Boolean],
    valueMap: {
      type: Array,
      validator: function validator(val) {
        if (!val.length || val.length !== 2) {
          if (false) {
            console.error('[VUX error] selector prop:valueMap\'s length should be 2');
          }
          return false;
        }
        return true;
      }
    }
  },
  data: function data() {
    return {
      currentValue: '',
      isIOS: false
    };
  }
});

/***/ }),
/* 165 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_babel_runtime_helpers_typeof__ = __webpack_require__(20);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_babel_runtime_helpers_typeof___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0_babel_runtime_helpers_typeof__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_array_find__ = __webpack_require__(57);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_array_find___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_1_array_find__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__mixins_uuid__ = __webpack_require__(50);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_3__libs_clean_style__ = __webpack_require__(33);







var findByKey = function findByKey(key, options) {
  var _rs = __WEBPACK_IMPORTED_MODULE_1_array_find___default()(options, function (item) {
    return item.key === key;
  });
  return _rs ? _rs.value : key;
};

/* harmony default export */ __webpack_exports__["a"] = ({
  name: 'selector',
  mixins: [__WEBPACK_IMPORTED_MODULE_2__mixins_uuid__["a" /* default */]],
  created: function created() {
    if (typeof this.value !== 'undefined') {
      this.currentValue = this.value;
    }
  },
  beforeMount: function beforeMount() {
    this.isIOS = /iPad|iPhone|iPod/.test(window.navigator.userAgent);
  },

  computed: {
    fixIos: function fixIos() {
      return !this.placeholder && this.isEmptyValue(this.value) && this.isIOS && this.title;
    },
    color: function color() {
      return this.showPlaceholder ? '#A9A9A9' : '';
    },
    processOptions: function processOptions() {
      var _this = this;

      if (!this.options.length) {
        return [];
      }

      var isObject = __WEBPACK_IMPORTED_MODULE_0_babel_runtime_helpers_typeof___default()(this.options[0]) === 'object';

      if (isObject && !this.valueMap) {
        return this.options;
      }

      if (isObject && this.valueMap) {
        return this.options.map(function (item) {
          return {
            key: item[_this.valueMap[0]],
            value: item[_this.valueMap[1]]
          };
        });
      }

      return this.options.map(function (item) {
        return {
          key: item,
          value: item
        };
      });
    },
    showPlaceholder: function showPlaceholder() {
      if (this.isEmptyValue(this.value) && this.placeholder) {
        return true;
      }
      return false;
    },
    labelClass: function labelClass() {
      return {
        'vux-cell-justify': this.$parent.labelAlign === 'justify' || this.$parent.$parent.labelAlign === 'justify'
      };
    }
  },
  methods: {
    isEmptyValue: function isEmptyValue(val) {
      return typeof val === 'undefined' || val === '' || val === null;
    },

    cleanStyle: __WEBPACK_IMPORTED_MODULE_3__libs_clean_style__["a" /* default */],
    getFullValue: function getFullValue() {
      var _this2 = this;

      if (!this.value) {
        return null;
      }
      if (!this.options.length) {
        return null;
      }
      if (__WEBPACK_IMPORTED_MODULE_0_babel_runtime_helpers_typeof___default()(this.options[0]) !== 'object') {
        return this.value;
      } else {
        if (!this.valueMap) {
          return this.options.filter(function (one) {
            return one.key === _this2.value;
          });
        } else {
          return this.options.filter(function (one) {
            return one[_this2.valueMap[0]] === _this2.value;
          });
        }
      }
    }
  },
  filters: {
    findByKey: findByKey
  },
  watch: {
    value: function value(val) {
      this.currentValue = val;
    },
    currentValue: function currentValue(val) {
      this.$emit('input', val);
      this.$emit('on-change', val);
    }
  },
  props: {
    title: String,
    direction: String,
    options: {
      type: Array,
      required: true
    },
    name: String,
    placeholder: String,
    readonly: Boolean,
    value: [String, Number, Object, Boolean],
    valueMap: {
      type: Array,
      validator: function validator(val) {
        if (!val.length || val.length !== 2) {
          if (false) {
            console.error('[VUX error] selector prop:valueMap\'s length should be 2');
          }
          return false;
        }
        return true;
      }
    }
  },
  data: function data() {
    return {
      currentValue: '',
      isIOS: false
    };
  }
});

/***/ }),
/* 166 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{staticClass:"vux-selector weui-cell",class:{
    'weui-cell_select': !_vm.readonly,
    'weui-cell_select-after': _vm.title
  }},[(_vm.title)?_c('div',{staticClass:"weui-cell__hd"},[_c('label',{staticClass:"weui-label",class:_vm.labelClass,style:(_vm.cleanStyle({
        width: _vm.$parent.labelWidth,
        textAlign: _vm.$parent.labelAlign,
        marginRight: _vm.$parent.labelMarginRight
      })),attrs:{"for":("vux-selector-" + _vm.uuid)},domProps:{"innerHTML":_vm._s(_vm.title)}})]):_vm._e(),_vm._v(" "),(!_vm.readonly)?_c('div',{staticClass:"weui-cell__bd"},[_c('select',{directives:[{name:"model",rawName:"v-model",value:(_vm.currentValue),expression:"currentValue"}],staticClass:"weui-select",style:(_vm.cleanStyle({
        direction: _vm.direction,
        color: _vm.color
      })),attrs:{"id":("vux-selector-" + _vm.uuid),"name":_vm.name},on:{"change":function($event){var $$selectedVal = Array.prototype.filter.call($event.target.options,function(o){return o.selected}).map(function(o){var val = "_value" in o ? o._value : o.value;return val}); _vm.currentValue=$event.target.multiple ? $$selectedVal : $$selectedVal[0]}}},[(_vm.showPlaceholder)?_c('option',{domProps:{"value":_vm.value === null ? 'null' : '',"selected":_vm.isEmptyValue(_vm.value) && !!_vm.placeholder}},[_vm._v(_vm._s(_vm.placeholder))]):_vm._e(),_vm._v(" "),(_vm.fixIos)?_c('option',{attrs:{"disabled":""}}):_vm._e(),_vm._v(" "),_vm._l((_vm.processOptions),function(one){return _c('option',{domProps:{"value":one.key}},[_vm._v(_vm._s(one.value))])})],2)]):_c('div',{staticClass:"weui-cell__ft vux-selector-readonly"},[_vm._v("\n    "+_vm._s(_vm._f("findByKey")(_vm.value,_vm.processOptions))+"\n  ")])])}
var staticRenderFns = []
var esExports = { render: render, staticRenderFns: staticRenderFns }
/* harmony default export */ __webpack_exports__["a"] = (esExports);

/***/ }),
/* 167 */
/***/ (function(module, exports, __webpack_require__) {

__webpack_require__(168);
module.exports = __webpack_require__(67).Number.isInteger;


/***/ }),
/* 168 */
/***/ (function(module, exports, __webpack_require__) {

// 20.1.2.3 Number.isInteger(number)
var $export = __webpack_require__(86);

$export($export.S, 'Number', { isInteger: __webpack_require__(169) });


/***/ }),
/* 169 */
/***/ (function(module, exports, __webpack_require__) {

// 20.1.2.3 Number.isInteger(number)
var isObject = __webpack_require__(329);
var floor = Math.floor;
module.exports = function isInteger(it) {
  return !isObject(it) && isFinite(it) && floor(it) === it;
};


/***/ }),
/* 170 */,
/* 171 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vux_loader_src_script_loader_js_node_modules_vux_loader_src_script_loader_js_node_modules_vue_loader_lib_selector_type_script_index_0_index_vue__ = __webpack_require__(173);
/* unused harmony namespace reexport */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__babel_loader_node_modules_vux_loader_src_script_loader_js_node_modules_vue_loader_lib_selector_type_script_index_0_index_vue__ = __webpack_require__(174);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__node_modules_vue_loader_lib_template_compiler_index_id_data_v_53756644_hasScoped_false_buble_transforms_node_modules_vux_loader_src_before_template_compiler_loader_js_node_modules_vux_loader_src_template_loader_js_node_modules_vue_loader_lib_selector_type_template_index_0_index_vue__ = __webpack_require__(175);
function injectStyle (ssrContext) {
  __webpack_require__(172)
}
var normalizeComponent = __webpack_require__(0)
/* script */


/* template */

/* template functional */
var __vue_template_functional__ = false
/* styles */
var __vue_styles__ = injectStyle
/* scopeId */
var __vue_scopeId__ = null
/* moduleIdentifier (server only) */
var __vue_module_identifier__ = null
var Component = normalizeComponent(
  __WEBPACK_IMPORTED_MODULE_1__babel_loader_node_modules_vux_loader_src_script_loader_js_node_modules_vue_loader_lib_selector_type_script_index_0_index_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_2__node_modules_vue_loader_lib_template_compiler_index_id_data_v_53756644_hasScoped_false_buble_transforms_node_modules_vux_loader_src_before_template_compiler_loader_js_node_modules_vux_loader_src_template_loader_js_node_modules_vue_loader_lib_selector_type_template_index_0_index_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)

/* harmony default export */ __webpack_exports__["a"] = (Component.exports);


/***/ }),
/* 172 */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),
/* 173 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__config__ = __webpack_require__(121);



/* unused harmony default export */ var _unused_webpack_default_export = ({
    name: 'ym-abnor',
    props: {
        type: {
            type: String,
            value: ''
        },
        show: {
            type: Boolean,
            default: false
        }
    },
    data: function data() {
        return {
            image: '',
            title: '',
            button: '',
            tip: ''
        };
    },
    mounted: function mounted() {
        var _this = this;

        var that = this;
        var type = that.type;
        this.$nextTick(function () {
            if (type && __WEBPACK_IMPORTED_MODULE_0__config__["a" /* default */][type]) {
                _this.image = _this.image || __WEBPACK_IMPORTED_MODULE_0__config__["a" /* default */][type].image;
                _this.title = _this.title || __WEBPACK_IMPORTED_MODULE_0__config__["a" /* default */][type].title;
                _this.button = _this.button || __WEBPACK_IMPORTED_MODULE_0__config__["a" /* default */][type].button;
                _this.tip = _this.tip || __WEBPACK_IMPORTED_MODULE_0__config__["a" /* default */][type].tip;
            }
        });
    }
});

/***/ }),
/* 174 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__config__ = __webpack_require__(121);



/* harmony default export */ __webpack_exports__["a"] = ({
    name: 'ym-abnor',
    props: {
        type: {
            type: String,
            value: ''
        },
        show: {
            type: Boolean,
            default: false
        }
    },
    data: function data() {
        return {
            image: '',
            title: '',
            button: '',
            tip: ''
        };
    },
    mounted: function mounted() {
        var _this = this;

        var that = this;
        var type = that.type;
        this.$nextTick(function () {
            if (type && __WEBPACK_IMPORTED_MODULE_0__config__["a" /* default */][type]) {
                _this.image = _this.image || __WEBPACK_IMPORTED_MODULE_0__config__["a" /* default */][type].image;
                _this.title = _this.title || __WEBPACK_IMPORTED_MODULE_0__config__["a" /* default */][type].title;
                _this.button = _this.button || __WEBPACK_IMPORTED_MODULE_0__config__["a" /* default */][type].button;
                _this.tip = _this.tip || __WEBPACK_IMPORTED_MODULE_0__config__["a" /* default */][type].tip;
            }
        });
    }
});

/***/ }),
/* 175 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{staticClass:"ym-abnor"},[_c('div',{staticClass:"ym-abnor__box"},[(_vm.image)?_c('img',{staticClass:"ym-abnor__image",attrs:{"src":_vm.image}}):_vm._e(),_vm._v(" "),(_vm.title)?_c('div',{staticClass:"ym-abnor__text"},[_vm._v(_vm._s(_vm.title))]):_vm._e(),_vm._v(" "),(_vm.tip)?_c('div',{staticClass:"ym-abnor__tip"},[_vm._v(_vm._s(_vm.tip))]):_vm._e(),_vm._v(" "),(_vm.button)?_c('div',{staticClass:"ym-abnor__btn",attrs:{"bindtap":"emitAbnorTap"}},[_vm._v(_vm._s(_vm.button))]):_vm._e()])])}
var staticRenderFns = []
var esExports = { render: render, staticRenderFns: staticRenderFns }
/* harmony default export */ __webpack_exports__["a"] = (esExports);

/***/ }),
/* 176 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vux_loader_src_script_loader_js_node_modules_vux_loader_src_script_loader_js_node_modules_vue_loader_lib_selector_type_script_index_0_index_vue__ = __webpack_require__(178);
/* unused harmony reexport namespace */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__babel_loader_node_modules_vux_loader_src_script_loader_js_node_modules_vue_loader_lib_selector_type_script_index_0_index_vue__ = __webpack_require__(179);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__node_modules_vue_loader_lib_template_compiler_index_id_data_v_2603aca0_hasScoped_false_buble_transforms_node_modules_vux_loader_src_before_template_compiler_loader_js_node_modules_vux_loader_src_template_loader_js_node_modules_vue_loader_lib_selector_type_template_index_0_index_vue__ = __webpack_require__(180);
function injectStyle (ssrContext) {
  __webpack_require__(177)
}
var normalizeComponent = __webpack_require__(0)
/* script */


/* template */

/* template functional */
var __vue_template_functional__ = false
/* styles */
var __vue_styles__ = injectStyle
/* scopeId */
var __vue_scopeId__ = null
/* moduleIdentifier (server only) */
var __vue_module_identifier__ = null
var Component = normalizeComponent(
  __WEBPACK_IMPORTED_MODULE_1__babel_loader_node_modules_vux_loader_src_script_loader_js_node_modules_vue_loader_lib_selector_type_script_index_0_index_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_2__node_modules_vue_loader_lib_template_compiler_index_id_data_v_2603aca0_hasScoped_false_buble_transforms_node_modules_vux_loader_src_before_template_compiler_loader_js_node_modules_vux_loader_src_template_loader_js_node_modules_vue_loader_lib_selector_type_template_index_0_index_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)

/* unused harmony default export */ var _unused_webpack_default_export = (Component.exports);


/***/ }),
/* 177 */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),
/* 178 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";


/* unused harmony default export */ var _unused_webpack_default_export = ({
    props: {
        links: Array,
        text: String,
        logo: String,
        isFixed: Boolean
    }
});

/***/ }),
/* 179 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";


/* harmony default export */ __webpack_exports__["a"] = ({
    props: {
        links: Array,
        text: String,
        logo: String,
        isFixed: Boolean
    }
});

/***/ }),
/* 180 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{staticClass:"weui-footer",class:{'weui-footer_fixed-bottom':_vm.isFixed}},[(_vm.links)?_c('p',{staticClass:"weui-footer__links"},_vm._l((_vm.links),function(link){return _c('a',{staticClass:"weui-footer__link",attrs:{"href":link.url ? link.url: 'javascript:void(0);'}},[_vm._v("\n            "+_vm._s(link.title)+"\n        ")])})):_vm._e(),_vm._v(" "),(_vm.text)?_c('p',{staticClass:"weui-footer__text",domProps:{"innerHTML":_vm._s(_vm.text)}}):_vm._e(),_vm._v(" "),_vm._m(0)])}
var staticRenderFns = [function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('p',{staticClass:"weui-footer__logo"},[_c('a',{attrs:{"href":"javascript:void(0);"}},[_c('img',{attrs:{"src":"http://ybvv.img.admin9.com/images/122/2017/07/qe1DzE116e00p0m16bfBzCbwPmz0ZE.png"}})])])}]
var esExports = { render: render, staticRenderFns: staticRenderFns }
/* harmony default export */ __webpack_exports__["a"] = (esExports);

/***/ }),
/* 181 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vux_loader_src_script_loader_js_node_modules_vux_loader_src_script_loader_js_node_modules_vue_loader_lib_selector_type_script_index_0_index_vue__ = __webpack_require__(183);
/* unused harmony reexport namespace */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__babel_loader_node_modules_vux_loader_src_script_loader_js_node_modules_vue_loader_lib_selector_type_script_index_0_index_vue__ = __webpack_require__(184);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__node_modules_vue_loader_lib_template_compiler_index_id_data_v_acf7c53c_hasScoped_false_buble_transforms_node_modules_vux_loader_src_before_template_compiler_loader_js_node_modules_vux_loader_src_template_loader_js_node_modules_vue_loader_lib_selector_type_template_index_0_index_vue__ = __webpack_require__(185);
function injectStyle (ssrContext) {
  __webpack_require__(182)
}
var normalizeComponent = __webpack_require__(0)
/* script */


/* template */

/* template functional */
var __vue_template_functional__ = false
/* styles */
var __vue_styles__ = injectStyle
/* scopeId */
var __vue_scopeId__ = null
/* moduleIdentifier (server only) */
var __vue_module_identifier__ = null
var Component = normalizeComponent(
  __WEBPACK_IMPORTED_MODULE_1__babel_loader_node_modules_vux_loader_src_script_loader_js_node_modules_vue_loader_lib_selector_type_script_index_0_index_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_2__node_modules_vue_loader_lib_template_compiler_index_id_data_v_acf7c53c_hasScoped_false_buble_transforms_node_modules_vux_loader_src_before_template_compiler_loader_js_node_modules_vux_loader_src_template_loader_js_node_modules_vue_loader_lib_selector_type_template_index_0_index_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)

/* unused harmony default export */ var _unused_webpack_default_export = (Component.exports);


/***/ }),
/* 182 */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),
/* 183 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";


/* unused harmony default export */ var _unused_webpack_default_export = ({
    props: {
        title: String
    }
});

/***/ }),
/* 184 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";


/* harmony default export */ __webpack_exports__["a"] = ({
    props: {
        title: String
    }
});

/***/ }),
/* 185 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('article',{staticClass:"weui-article"},[_vm._t("title",[(_vm.title)?_c('h1',[_vm._v(_vm._s(_vm.title))]):_vm._e()]),_vm._v(" "),_c('section',[_vm._t("default")],2)],2)}
var staticRenderFns = []
var esExports = { render: render, staticRenderFns: staticRenderFns }
/* harmony default export */ __webpack_exports__["a"] = (esExports);

/***/ }),
/* 186 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vux_loader_src_script_loader_js_node_modules_vux_loader_src_script_loader_js_node_modules_vue_loader_lib_selector_type_script_index_0_index_vue__ = __webpack_require__(188);
/* unused harmony reexport namespace */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__babel_loader_node_modules_vux_loader_src_script_loader_js_node_modules_vue_loader_lib_selector_type_script_index_0_index_vue__ = __webpack_require__(189);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__node_modules_vue_loader_lib_template_compiler_index_id_data_v_1c1c9216_hasScoped_false_buble_transforms_node_modules_vux_loader_src_before_template_compiler_loader_js_node_modules_vux_loader_src_template_loader_js_node_modules_vue_loader_lib_selector_type_template_index_0_index_vue__ = __webpack_require__(190);
function injectStyle (ssrContext) {
  __webpack_require__(187)
}
var normalizeComponent = __webpack_require__(0)
/* script */


/* template */

/* template functional */
var __vue_template_functional__ = false
/* styles */
var __vue_styles__ = injectStyle
/* scopeId */
var __vue_scopeId__ = null
/* moduleIdentifier (server only) */
var __vue_module_identifier__ = null
var Component = normalizeComponent(
  __WEBPACK_IMPORTED_MODULE_1__babel_loader_node_modules_vux_loader_src_script_loader_js_node_modules_vue_loader_lib_selector_type_script_index_0_index_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_2__node_modules_vue_loader_lib_template_compiler_index_id_data_v_1c1c9216_hasScoped_false_buble_transforms_node_modules_vux_loader_src_before_template_compiler_loader_js_node_modules_vux_loader_src_template_loader_js_node_modules_vue_loader_lib_selector_type_template_index_0_index_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)

/* unused harmony default export */ var _unused_webpack_default_export = (Component.exports);


/***/ }),
/* 187 */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),
/* 188 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_vux_src_directives_transfer_dom_index_js__ = __webpack_require__(6);



/* unused harmony default export */ var _unused_webpack_default_export = ({
  directives: {
    TransferDom: __WEBPACK_IMPORTED_MODULE_0_vux_src_directives_transfer_dom_index_js__["a" /* default */]
  },
  props: {
    title: String,
    desc: String,
    labelWidth: String,
    items: {
      type: Array,
      default: function _default() {
        return [];
      }
    },
    count: {
      type: Number,
      default: 9
    },
    lineHide: Boolean
  },
  data: function data() {
    return {
      index: 0,
      localIds: [],
      serverIds: [],
      gallery: ''
    };
  },


  methods: {
    handlePicture: function handlePicture() {
      var self = this;
      var picNum = self.count - self.items.length;
      wx.chooseImage({
        count: picNum > 0 ? picNum : 0,
        sizeType: ['original', 'compressed'],
        sourceType: ['album', 'camera'],
        success: function success(res) {
          for (var n = 0; n < res.localIds.length; n++) {
            if (window.__wxjs_is_wkwebview) {
              wx.getLocalImgData({
                localId: res.localIds[n],
                success: function success(r) {
                  self.items.push({ url: r.localData });
                  self.localIds.push(r.localData);
                },
                fail: function fail() {
                  var localIds = self.localIds;
                  localIds.push(res.localIds[n]);
                  self.items.push({ url: res.localIds[n] });
                  self.localIds = localIds.slice(-self.count);
                }
              });
            } else {
              self.items.push({ url: res.localIds[n] });
              self.localIds.push(res.localIds[n]);
            }
          }
          var index = 0;
          self.uploadPhotos(res.localIds, index);
        }
      });
    },
    uploadPhotos: function uploadPhotos(localIds, index) {
      var self = this;
      self.$vux.loading.show({ text: '正在上传' });
      wx.uploadImage({
        localId: localIds[index],
        isShowProgressTips: 0,
        success: function success(res) {
          index++;
          if (index < localIds.length) {
            self.uploadPhotos(localIds, index);
          }
          var serverId = self.serverIds.concat([res.serverId]);
          self.serverIds = serverId.slice(-self.count);
        }
      });
    },
    onGalleryShow: function onGalleryShow(index) {
      this.index = index;
      this.gallery = this.items[index];
      this.$emit('on-show', index);
    },
    deletePhoto: function deletePhoto(index) {
      this.items.splice(index, 1);
      this.gallery = '';
      this.$emit('on-delete', index);
    }
  },
  computed: {
    labelStyles: function labelStyles() {
      return {
        width: this.labelWidthComputed || this.$parent.labelWidth || this.labelWidthComputed,
        textAlign: this.$parent.labelAlign,
        marginRight: this.$parent.labelMarginRight
      };
    }
  }
});

/***/ }),
/* 189 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_vux_src_directives_transfer_dom_index_js__ = __webpack_require__(6);



/* harmony default export */ __webpack_exports__["a"] = ({
  directives: {
    TransferDom: __WEBPACK_IMPORTED_MODULE_0_vux_src_directives_transfer_dom_index_js__["a" /* default */]
  },
  props: {
    title: String,
    desc: String,
    labelWidth: String,
    items: {
      type: Array,
      default: function _default() {
        return [];
      }
    },
    count: {
      type: Number,
      default: 9
    },
    lineHide: Boolean
  },
  data: function data() {
    return {
      index: 0,
      localIds: [],
      serverIds: [],
      gallery: ''
    };
  },


  methods: {
    handlePicture: function handlePicture() {
      var self = this;
      var picNum = self.count - self.items.length;
      wx.chooseImage({
        count: picNum > 0 ? picNum : 0,
        sizeType: ['original', 'compressed'],
        sourceType: ['album', 'camera'],
        success: function success(res) {
          for (var n = 0; n < res.localIds.length; n++) {
            if (window.__wxjs_is_wkwebview) {
              wx.getLocalImgData({
                localId: res.localIds[n],
                success: function success(r) {
                  self.items.push({ url: r.localData });
                  self.localIds.push(r.localData);
                },
                fail: function fail() {
                  var localIds = self.localIds;
                  localIds.push(res.localIds[n]);
                  self.items.push({ url: res.localIds[n] });
                  self.localIds = localIds.slice(-self.count);
                }
              });
            } else {
              self.items.push({ url: res.localIds[n] });
              self.localIds.push(res.localIds[n]);
            }
          }
          var index = 0;
          self.uploadPhotos(res.localIds, index);
        }
      });
    },
    uploadPhotos: function uploadPhotos(localIds, index) {
      var self = this;
      self.$vux.loading.show({ text: '正在上传' });
      wx.uploadImage({
        localId: localIds[index],
        isShowProgressTips: 0,
        success: function success(res) {
          index++;
          if (index < localIds.length) {
            self.uploadPhotos(localIds, index);
          }
          var serverId = self.serverIds.concat([res.serverId]);
          self.serverIds = serverId.slice(-self.count);
        }
      });
    },
    onGalleryShow: function onGalleryShow(index) {
      this.index = index;
      this.gallery = this.items[index];
      this.$emit('on-show', index);
    },
    deletePhoto: function deletePhoto(index) {
      this.items.splice(index, 1);
      this.gallery = '';
      this.$emit('on-delete', index);
    }
  },
  computed: {
    labelStyles: function labelStyles() {
      return {
        width: this.labelWidthComputed || this.$parent.labelWidth || this.labelWidthComputed,
        textAlign: this.$parent.labelAlign,
        marginRight: this.$parent.labelMarginRight
      };
    }
  }
});

/***/ }),
/* 190 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{staticClass:"weui-cell",class:{'line-hide':_vm.lineHide}},[_c('div',{staticClass:"weui-cell__bd"},[_c('div',{staticClass:"weui-uploader"},[(_vm.title || _vm.desc)?_c('div',{staticClass:"weui-uploader__hd"},[(_vm.title)?_c('p',{staticClass:"weui-uploader__title"},[_c('label',{staticClass:"weui-label",style:({width: _vm.labelWidth || _vm.$parent.labelWidth || _vm.labelWidthComputed, textAlign: _vm.$parent.labelAlign, marginRight: _vm.$parent.labelMarginRight}),domProps:{"innerHTML":_vm._s(_vm.title)}})]):_vm._e(),_vm._v(" "),(_vm.desc)?_c('div',{staticClass:"weui-uploader__info",domProps:{"innerHTML":_vm._s(_vm.desc)},on:{"click":function($event){_vm.$emit('on-desc')}}}):_vm._e()]):_vm._e(),_vm._v(" "),_c('div',{staticClass:"weui-uploader__bd"},[_c('ul',{staticClass:"weui-uploader__files",attrs:{"id":"uploaderFiles"}},_vm._l((_vm.items),function(photo,index){return _c('li',{staticClass:"weui-uploader__file",style:({backgroundImage: 'url('+photo.localId+')'}),on:{"click":function($event){_vm.onGalleryShow(index)}}})})),_vm._v(" "),_c('div',{directives:[{name:"show",rawName:"v-show",value:(_vm.items.length < _vm.count),expression:"items.length < count"}],staticClass:"weui-uploader__input-box"},[_c('div',{staticClass:"weui-uploader__input",on:{"click":_vm.handlePicture}})])])]),_vm._v(" "),_c('div',{directives:[{name:"transfer-dom",rawName:"v-transfer-dom"}]},[_c('div',{staticClass:"weui-gallery",class:{'show':_vm.gallery}},[_c('span',{staticClass:"weui-gallery__img",style:('backgroundImage: url('+_vm.gallery+')'),on:{"click":function($event){_vm.gallery=''}}}),_vm._v(" "),_c('div',{staticClass:"weui-gallery__opr",on:{"click":function($event){_vm.deletePhoto(_vm.index)}}},[_vm._m(0)])])])])])}
var staticRenderFns = [function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('a',{staticClass:"weui-gallery__del",attrs:{"href":"javascript:"}},[_c('i',{staticClass:"weui-icon-delete weui-icon_gallery-delete"})])}]
var esExports = { render: render, staticRenderFns: staticRenderFns }
/* harmony default export */ __webpack_exports__["a"] = (esExports);

/***/ }),
/* 191 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vux_loader_src_script_loader_js_node_modules_vux_loader_src_script_loader_js_node_modules_vue_loader_lib_selector_type_script_index_0_index_vue__ = __webpack_require__(192);
/* unused harmony reexport namespace */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__babel_loader_node_modules_vux_loader_src_script_loader_js_node_modules_vue_loader_lib_selector_type_script_index_0_index_vue__ = __webpack_require__(193);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__node_modules_vue_loader_lib_template_compiler_index_id_data_v_ccf291f4_hasScoped_false_buble_transforms_node_modules_vux_loader_src_before_template_compiler_loader_js_node_modules_vux_loader_src_template_loader_js_node_modules_vue_loader_lib_selector_type_template_index_0_index_vue__ = __webpack_require__(194);
var normalizeComponent = __webpack_require__(0)
/* script */


/* template */

/* template functional */
var __vue_template_functional__ = false
/* styles */
var __vue_styles__ = null
/* scopeId */
var __vue_scopeId__ = null
/* moduleIdentifier (server only) */
var __vue_module_identifier__ = null
var Component = normalizeComponent(
  __WEBPACK_IMPORTED_MODULE_1__babel_loader_node_modules_vux_loader_src_script_loader_js_node_modules_vue_loader_lib_selector_type_script_index_0_index_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_2__node_modules_vue_loader_lib_template_compiler_index_id_data_v_ccf291f4_hasScoped_false_buble_transforms_node_modules_vux_loader_src_before_template_compiler_loader_js_node_modules_vux_loader_src_template_loader_js_node_modules_vue_loader_lib_selector_type_template_index_0_index_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)

/* unused harmony default export */ var _unused_webpack_default_export = (Component.exports);


/***/ }),
/* 192 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_number_is_integer__ = __webpack_require__(27);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_number_is_integer___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_number_is_integer__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_vux_src_components_grid_grid_vue__ = __webpack_require__(1);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2_vux_src_components_grid_grid_item_vue__ = __webpack_require__(2);





/* unused harmony default export */ var _unused_webpack_default_export = ({
  props: {
    rows: {
      type: Number,
      default: 3
    },
    items: {
      type: Array,
      default: []
    }
  },
  components: {
    Grid: __WEBPACK_IMPORTED_MODULE_1_vux_src_components_grid_grid_vue__["a" /* default */], GridItem: __WEBPACK_IMPORTED_MODULE_2_vux_src_components_grid_grid_item_vue__["a" /* default */]
  },
  computed: {
    groupList: function groupList() {
      var count = this.items.length;
      var rowNum = count / this.rows;
      if (__WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_number_is_integer___default()(rowNum)) {
        return this.items;
      }
      rowNum = Math.ceil(count / this.rows) * this.rows;
      for (var i = 0; i < rowNum; i++) {
        if (!this.items[i]) {
          this.items.push({ id: 0, name: '' });
        }
      }
      return this.items;
    }
  }
});

/***/ }),
/* 193 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_number_is_integer__ = __webpack_require__(27);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_number_is_integer___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_number_is_integer__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_vux_src_components_grid_grid_vue__ = __webpack_require__(1);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2_vux_src_components_grid_grid_item_vue__ = __webpack_require__(2);





/* harmony default export */ __webpack_exports__["a"] = ({
  props: {
    rows: {
      type: Number,
      default: 3
    },
    items: {
      type: Array,
      default: []
    }
  },
  components: {
    Grid: __WEBPACK_IMPORTED_MODULE_1_vux_src_components_grid_grid_vue__["a" /* default */], GridItem: __WEBPACK_IMPORTED_MODULE_2_vux_src_components_grid_grid_item_vue__["a" /* default */]
  },
  computed: {
    groupList: function groupList() {
      var count = this.items.length;
      var rowNum = count / this.rows;
      if (__WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_number_is_integer___default()(rowNum)) {
        return this.items;
      }
      rowNum = Math.ceil(count / this.rows) * this.rows;
      for (var i = 0; i < rowNum; i++) {
        if (!this.items[i]) {
          this.items.push({ id: 0, name: '' });
        }
      }
      return this.items;
    }
  }
});

/***/ }),
/* 194 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('grid',{staticStyle:{"background":"#FFF"},attrs:{"cols":_vm.rows}},_vm._l((_vm.groupList),function(item,index){return _c('grid-item',{key:index,attrs:{"link":item.url,"icon":item.icon}},[_c('p',{attrs:{"slot":"icon"},slot:"icon"},[(item.icon)?_c('img',{class:{'img-circle':item.show_model==1},attrs:{"src":item.icon}}):_vm._e()]),_vm._v(" "),_c('span',{attrs:{"slot":"label"},slot:"label"},[_vm._v(_vm._s(item.name || ' '))])])}))}
var staticRenderFns = []
var esExports = { render: render, staticRenderFns: staticRenderFns }
/* harmony default export */ __webpack_exports__["a"] = (esExports);

/***/ }),
/* 195 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vux_loader_src_script_loader_js_node_modules_vux_loader_src_script_loader_js_node_modules_vue_loader_lib_selector_type_script_index_0_index_vue__ = __webpack_require__(197);
/* unused harmony namespace reexport */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__babel_loader_node_modules_vux_loader_src_script_loader_js_node_modules_vue_loader_lib_selector_type_script_index_0_index_vue__ = __webpack_require__(198);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__node_modules_vue_loader_lib_template_compiler_index_id_data_v_2bd98197_hasScoped_false_buble_transforms_node_modules_vux_loader_src_before_template_compiler_loader_js_node_modules_vux_loader_src_template_loader_js_node_modules_vue_loader_lib_selector_type_template_index_0_index_vue__ = __webpack_require__(199);
function injectStyle (ssrContext) {
  __webpack_require__(196)
}
var normalizeComponent = __webpack_require__(0)
/* script */


/* template */

/* template functional */
var __vue_template_functional__ = false
/* styles */
var __vue_styles__ = injectStyle
/* scopeId */
var __vue_scopeId__ = null
/* moduleIdentifier (server only) */
var __vue_module_identifier__ = null
var Component = normalizeComponent(
  __WEBPACK_IMPORTED_MODULE_1__babel_loader_node_modules_vux_loader_src_script_loader_js_node_modules_vue_loader_lib_selector_type_script_index_0_index_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_2__node_modules_vue_loader_lib_template_compiler_index_id_data_v_2bd98197_hasScoped_false_buble_transforms_node_modules_vux_loader_src_before_template_compiler_loader_js_node_modules_vux_loader_src_template_loader_js_node_modules_vue_loader_lib_selector_type_template_index_0_index_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)

/* harmony default export */ __webpack_exports__["a"] = (Component.exports);


/***/ }),
/* 196 */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),
/* 197 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";


/* unused harmony default export */ var _unused_webpack_default_export = ({
    props: {
        text: {
            type: String,
            default: ''
        }
    }
});

/***/ }),
/* 198 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";


/* harmony default export */ __webpack_exports__["a"] = ({
    props: {
        text: {
            type: String,
            default: ''
        }
    }
});

/***/ }),
/* 199 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('label',{staticClass:"weui-agree"},[_c('div',{staticClass:"weui-agree__text"},[_vm._t("default")],2)])}
var staticRenderFns = []
var esExports = { render: render, staticRenderFns: staticRenderFns }
/* harmony default export */ __webpack_exports__["a"] = (esExports);

/***/ }),
/* 200 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vux_loader_src_script_loader_js_node_modules_vux_loader_src_script_loader_js_node_modules_vue_loader_lib_selector_type_script_index_0_index_vue__ = __webpack_require__(202);
/* unused harmony reexport namespace */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__babel_loader_node_modules_vux_loader_src_script_loader_js_node_modules_vue_loader_lib_selector_type_script_index_0_index_vue__ = __webpack_require__(203);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__node_modules_vue_loader_lib_template_compiler_index_id_data_v_35340950_hasScoped_false_buble_transforms_node_modules_vux_loader_src_before_template_compiler_loader_js_node_modules_vux_loader_src_template_loader_js_node_modules_vue_loader_lib_selector_type_template_index_0_index_vue__ = __webpack_require__(204);
function injectStyle (ssrContext) {
  __webpack_require__(201)
}
var normalizeComponent = __webpack_require__(0)
/* script */


/* template */

/* template functional */
var __vue_template_functional__ = false
/* styles */
var __vue_styles__ = injectStyle
/* scopeId */
var __vue_scopeId__ = null
/* moduleIdentifier (server only) */
var __vue_module_identifier__ = null
var Component = normalizeComponent(
  __WEBPACK_IMPORTED_MODULE_1__babel_loader_node_modules_vux_loader_src_script_loader_js_node_modules_vue_loader_lib_selector_type_script_index_0_index_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_2__node_modules_vue_loader_lib_template_compiler_index_id_data_v_35340950_hasScoped_false_buble_transforms_node_modules_vux_loader_src_before_template_compiler_loader_js_node_modules_vux_loader_src_template_loader_js_node_modules_vue_loader_lib_selector_type_template_index_0_index_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)

/* unused harmony default export */ var _unused_webpack_default_export = (Component.exports);


/***/ }),
/* 201 */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),
/* 202 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_number_is_integer__ = __webpack_require__(27);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_number_is_integer___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_number_is_integer__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_vux_src_components_grid_grid_vue__ = __webpack_require__(1);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2_vux_src_components_grid_grid_item_vue__ = __webpack_require__(2);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_3_vux_src_components_badge_index_vue__ = __webpack_require__(17);






/* unused harmony default export */ var _unused_webpack_default_export = ({
  props: {
    rows: {
      type: Number,
      default: 5
    },
    items: {
      type: Array,
      default: []
    }
  },
  components: {
    Grid: __WEBPACK_IMPORTED_MODULE_1_vux_src_components_grid_grid_vue__["a" /* default */], GridItem: __WEBPACK_IMPORTED_MODULE_2_vux_src_components_grid_grid_item_vue__["a" /* default */], Badge: __WEBPACK_IMPORTED_MODULE_3_vux_src_components_badge_index_vue__["a" /* default */]
  },
  computed: {
    groupList: function groupList() {
      var count = this.items.length;
      var rowNum = count / this.rows;
      if (__WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_number_is_integer___default()(rowNum)) {
        return this.items;
      }
      rowNum = Math.ceil(count / this.rows) * this.rows;
      for (var i = 0; i < rowNum; i++) {
        if (!this.items[i]) {
          this.items.push({ id: 0, title: '' });
        }
      }
      return this.items;
    }
  }
});

/***/ }),
/* 203 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_number_is_integer__ = __webpack_require__(27);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_number_is_integer___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_number_is_integer__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_vux_src_components_grid_grid_vue__ = __webpack_require__(1);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2_vux_src_components_grid_grid_item_vue__ = __webpack_require__(2);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_3_vux_src_components_badge_index_vue__ = __webpack_require__(17);






/* harmony default export */ __webpack_exports__["a"] = ({
  props: {
    rows: {
      type: Number,
      default: 5
    },
    items: {
      type: Array,
      default: []
    }
  },
  components: {
    Grid: __WEBPACK_IMPORTED_MODULE_1_vux_src_components_grid_grid_vue__["a" /* default */], GridItem: __WEBPACK_IMPORTED_MODULE_2_vux_src_components_grid_grid_item_vue__["a" /* default */], Badge: __WEBPACK_IMPORTED_MODULE_3_vux_src_components_badge_index_vue__["a" /* default */]
  },
  computed: {
    groupList: function groupList() {
      var count = this.items.length;
      var rowNum = count / this.rows;
      if (__WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_number_is_integer___default()(rowNum)) {
        return this.items;
      }
      rowNum = Math.ceil(count / this.rows) * this.rows;
      for (var i = 0; i < rowNum; i++) {
        if (!this.items[i]) {
          this.items.push({ id: 0, title: '' });
        }
      }
      return this.items;
    }
  }
});

/***/ }),
/* 204 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('grid',{staticStyle:{"background":"#FFF"},attrs:{"cols":_vm.rows}},_vm._l((_vm.groupList),function(row,index){return _c('grid-item',{key:index,attrs:{"link":row.url,"icon":row.thumb}},[(row.thumb)?_c('img',{attrs:{"slot":"icon","src":row.thumb},slot:"icon"}):_vm._e(),_vm._v(" "),(row.icon)?_c('i',{class:row.icon,attrs:{"slot":"icon"},slot:"icon"}):_vm._e(),_vm._v(" "),_c('badge',{directives:[{name:"show",rawName:"v-show",value:(row.show==1),expression:"row.show==1"}],staticClass:"default-badge-postion",attrs:{"text":row.total}}),_vm._v(" "),_c('span',{staticStyle:{"font-size":"14px"},attrs:{"slot":"label"},slot:"label"},[_vm._v(_vm._s(row.title || ' '))])],1)}))}
var staticRenderFns = []
var esExports = { render: render, staticRenderFns: staticRenderFns }
/* harmony default export */ __webpack_exports__["a"] = (esExports);

/***/ }),
/* 205 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vux_loader_src_script_loader_js_node_modules_vux_loader_src_script_loader_js_node_modules_vue_loader_lib_selector_type_script_index_0_index_vue__ = __webpack_require__(207);
/* unused harmony reexport namespace */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__babel_loader_node_modules_vux_loader_src_script_loader_js_node_modules_vue_loader_lib_selector_type_script_index_0_index_vue__ = __webpack_require__(208);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__node_modules_vue_loader_lib_template_compiler_index_id_data_v_281fe1ee_hasScoped_false_buble_transforms_node_modules_vux_loader_src_before_template_compiler_loader_js_node_modules_vux_loader_src_template_loader_js_node_modules_vue_loader_lib_selector_type_template_index_0_index_vue__ = __webpack_require__(209);
function injectStyle (ssrContext) {
  __webpack_require__(206)
}
var normalizeComponent = __webpack_require__(0)
/* script */


/* template */

/* template functional */
var __vue_template_functional__ = false
/* styles */
var __vue_styles__ = injectStyle
/* scopeId */
var __vue_scopeId__ = null
/* moduleIdentifier (server only) */
var __vue_module_identifier__ = null
var Component = normalizeComponent(
  __WEBPACK_IMPORTED_MODULE_1__babel_loader_node_modules_vux_loader_src_script_loader_js_node_modules_vue_loader_lib_selector_type_script_index_0_index_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_2__node_modules_vue_loader_lib_template_compiler_index_id_data_v_281fe1ee_hasScoped_false_buble_transforms_node_modules_vux_loader_src_before_template_compiler_loader_js_node_modules_vux_loader_src_template_loader_js_node_modules_vue_loader_lib_selector_type_template_index_0_index_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)

/* unused harmony default export */ var _unused_webpack_default_export = (Component.exports);


/***/ }),
/* 206 */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),
/* 207 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";


/* unused harmony default export */ var _unused_webpack_default_export = ({
    props: {
        links: Array,
        text: String,
        isFixed: Boolean
    }
});

/***/ }),
/* 208 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";


/* harmony default export */ __webpack_exports__["a"] = ({
    props: {
        links: Array,
        text: String,
        isFixed: Boolean
    }
});

/***/ }),
/* 209 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{staticClass:"weui-footer",class:{'weui-footer_fixed-bottom':_vm.isFixed}},[(_vm.links)?_c('p',{staticClass:"weui-footer__links"},_vm._l((_vm.links),function(link){return _c('a',{staticClass:"weui-footer__link",attrs:{"href":link.url ? link.url: 'javascript:void(0);'}},[_vm._v("\n            "+_vm._s(link.title)+"\n        ")])})):_vm._e(),_vm._v(" "),(_vm.text)?_c('p',{staticClass:"weui-footer__text",domProps:{"innerHTML":_vm._s(_vm.text)}}):_vm._e()])}
var staticRenderFns = []
var esExports = { render: render, staticRenderFns: staticRenderFns }
/* harmony default export */ __webpack_exports__["a"] = (esExports);

/***/ }),
/* 210 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vux_loader_src_script_loader_js_node_modules_vux_loader_src_script_loader_js_node_modules_vue_loader_lib_selector_type_script_index_0_index_vue__ = __webpack_require__(211);
/* unused harmony reexport namespace */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__babel_loader_node_modules_vux_loader_src_script_loader_js_node_modules_vue_loader_lib_selector_type_script_index_0_index_vue__ = __webpack_require__(212);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__node_modules_vue_loader_lib_template_compiler_index_id_data_v_70e41715_hasScoped_false_buble_transforms_node_modules_vux_loader_src_before_template_compiler_loader_js_node_modules_vux_loader_src_template_loader_js_node_modules_vue_loader_lib_selector_type_template_index_0_index_vue__ = __webpack_require__(213);
var normalizeComponent = __webpack_require__(0)
/* script */


/* template */

/* template functional */
var __vue_template_functional__ = false
/* styles */
var __vue_styles__ = null
/* scopeId */
var __vue_scopeId__ = null
/* moduleIdentifier (server only) */
var __vue_module_identifier__ = null
var Component = normalizeComponent(
  __WEBPACK_IMPORTED_MODULE_1__babel_loader_node_modules_vux_loader_src_script_loader_js_node_modules_vue_loader_lib_selector_type_script_index_0_index_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_2__node_modules_vue_loader_lib_template_compiler_index_id_data_v_70e41715_hasScoped_false_buble_transforms_node_modules_vux_loader_src_before_template_compiler_loader_js_node_modules_vux_loader_src_template_loader_js_node_modules_vue_loader_lib_selector_type_template_index_0_index_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)

/* unused harmony default export */ var _unused_webpack_default_export = (Component.exports);


/***/ }),
/* 211 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_number_is_integer__ = __webpack_require__(27);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_number_is_integer___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_number_is_integer__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_vux_src_components_grid_grid_vue__ = __webpack_require__(1);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2_vux_src_components_grid_grid_item_vue__ = __webpack_require__(2);





/* unused harmony default export */ var _unused_webpack_default_export = ({
  props: {
    rows: {
      type: Number,
      default: 3
    },
    items: {
      type: Array,
      default: []
    }
  },
  components: {
    Grid: __WEBPACK_IMPORTED_MODULE_1_vux_src_components_grid_grid_vue__["a" /* default */], GridItem: __WEBPACK_IMPORTED_MODULE_2_vux_src_components_grid_grid_item_vue__["a" /* default */]
  },
  computed: {
    groupList: function groupList() {
      var count = this.items.length;
      var rowNum = count / this.rows;
      if (__WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_number_is_integer___default()(rowNum)) {
        return this.items;
      }
      rowNum = Math.ceil(count / this.rows) * this.rows;
      for (var i = 0; i < rowNum; i++) {
        if (!this.items[i]) {
          this.items.push({ id: 0, title: '' });
        }
      }
      return this.items;
    }
  }
});

/***/ }),
/* 212 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_number_is_integer__ = __webpack_require__(27);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_number_is_integer___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_number_is_integer__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_vux_src_components_grid_grid_vue__ = __webpack_require__(1);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2_vux_src_components_grid_grid_item_vue__ = __webpack_require__(2);





/* harmony default export */ __webpack_exports__["a"] = ({
  props: {
    rows: {
      type: Number,
      default: 3
    },
    items: {
      type: Array,
      default: []
    }
  },
  components: {
    Grid: __WEBPACK_IMPORTED_MODULE_1_vux_src_components_grid_grid_vue__["a" /* default */], GridItem: __WEBPACK_IMPORTED_MODULE_2_vux_src_components_grid_grid_item_vue__["a" /* default */]
  },
  computed: {
    groupList: function groupList() {
      var count = this.items.length;
      var rowNum = count / this.rows;
      if (__WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_number_is_integer___default()(rowNum)) {
        return this.items;
      }
      rowNum = Math.ceil(count / this.rows) * this.rows;
      for (var i = 0; i < rowNum; i++) {
        if (!this.items[i]) {
          this.items.push({ id: 0, title: '' });
        }
      }
      return this.items;
    }
  }
});

/***/ }),
/* 213 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('grid',{staticStyle:{"background":"#FFF"},attrs:{"cols":_vm.rows}},_vm._l((_vm.groupList),function(item,index){return _c('grid-item',{key:index,attrs:{"link":item.url,"icon":item.thumb}},[_c('p',{attrs:{"slot":"icon"},slot:"icon"},[(item.thumb)?_c('img',{attrs:{"src":item.thumb}}):_vm._e()]),_vm._v(" "),_c('span',{attrs:{"slot":"label"},slot:"label"},[_vm._v(_vm._s(item.title || ' '))])])}))}
var staticRenderFns = []
var esExports = { render: render, staticRenderFns: staticRenderFns }
/* harmony default export */ __webpack_exports__["a"] = (esExports);

/***/ }),
/* 214 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vux_loader_src_script_loader_js_node_modules_vux_loader_src_script_loader_js_node_modules_vue_loader_lib_selector_type_script_index_0_index_vue__ = __webpack_require__(215);
/* unused harmony reexport namespace */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__babel_loader_node_modules_vux_loader_src_script_loader_js_node_modules_vue_loader_lib_selector_type_script_index_0_index_vue__ = __webpack_require__(216);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__node_modules_vue_loader_lib_template_compiler_index_id_data_v_480ef1c4_hasScoped_false_buble_transforms_node_modules_vux_loader_src_before_template_compiler_loader_js_node_modules_vux_loader_src_template_loader_js_node_modules_vue_loader_lib_selector_type_template_index_0_index_vue__ = __webpack_require__(217);
var normalizeComponent = __webpack_require__(0)
/* script */


/* template */

/* template functional */
var __vue_template_functional__ = false
/* styles */
var __vue_styles__ = null
/* scopeId */
var __vue_scopeId__ = null
/* moduleIdentifier (server only) */
var __vue_module_identifier__ = null
var Component = normalizeComponent(
  __WEBPACK_IMPORTED_MODULE_1__babel_loader_node_modules_vux_loader_src_script_loader_js_node_modules_vue_loader_lib_selector_type_script_index_0_index_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_2__node_modules_vue_loader_lib_template_compiler_index_id_data_v_480ef1c4_hasScoped_false_buble_transforms_node_modules_vux_loader_src_before_template_compiler_loader_js_node_modules_vux_loader_src_template_loader_js_node_modules_vue_loader_lib_selector_type_template_index_0_index_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)

/* unused harmony default export */ var _unused_webpack_default_export = (Component.exports);


/***/ }),
/* 215 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_vux_src_components_swiper_swiper_vue__ = __webpack_require__(8);



/* unused harmony default export */ var _unused_webpack_default_export = ({
  props: {
    items: Array
  },
  components: {
    Swiper: __WEBPACK_IMPORTED_MODULE_0_vux_src_components_swiper_swiper_vue__["a" /* default */]
  }
});

/***/ }),
/* 216 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_vux_src_components_swiper_swiper_vue__ = __webpack_require__(8);



/* harmony default export */ __webpack_exports__["a"] = ({
  props: {
    items: Array
  },
  components: {
    Swiper: __WEBPACK_IMPORTED_MODULE_0_vux_src_components_swiper_swiper_vue__["a" /* default */]
  }
});

/***/ }),
/* 217 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('swiper',{attrs:{"list":_vm.items,"dots-position":"center","auto":"","aspect-ratio":180/320,"show-desc-mask":false}})}
var staticRenderFns = []
var esExports = { render: render, staticRenderFns: staticRenderFns }
/* harmony default export */ __webpack_exports__["a"] = (esExports);

/***/ }),
/* 218 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vux_loader_src_script_loader_js_node_modules_vux_loader_src_script_loader_js_node_modules_vue_loader_lib_selector_type_script_index_0_index_vue__ = __webpack_require__(220);
/* unused harmony reexport namespace */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__babel_loader_node_modules_vux_loader_src_script_loader_js_node_modules_vue_loader_lib_selector_type_script_index_0_index_vue__ = __webpack_require__(221);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__node_modules_vue_loader_lib_template_compiler_index_id_data_v_369e7d69_hasScoped_false_buble_transforms_node_modules_vux_loader_src_before_template_compiler_loader_js_node_modules_vux_loader_src_template_loader_js_node_modules_vue_loader_lib_selector_type_template_index_0_index_vue__ = __webpack_require__(222);
function injectStyle (ssrContext) {
  __webpack_require__(219)
}
var normalizeComponent = __webpack_require__(0)
/* script */


/* template */

/* template functional */
var __vue_template_functional__ = false
/* styles */
var __vue_styles__ = injectStyle
/* scopeId */
var __vue_scopeId__ = null
/* moduleIdentifier (server only) */
var __vue_module_identifier__ = null
var Component = normalizeComponent(
  __WEBPACK_IMPORTED_MODULE_1__babel_loader_node_modules_vux_loader_src_script_loader_js_node_modules_vue_loader_lib_selector_type_script_index_0_index_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_2__node_modules_vue_loader_lib_template_compiler_index_id_data_v_369e7d69_hasScoped_false_buble_transforms_node_modules_vux_loader_src_before_template_compiler_loader_js_node_modules_vux_loader_src_template_loader_js_node_modules_vue_loader_lib_selector_type_template_index_0_index_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)

/* unused harmony default export */ var _unused_webpack_default_export = (Component.exports);


/***/ }),
/* 219 */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),
/* 220 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_vux_src_components_grid_grid_vue__ = __webpack_require__(1);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_vux_src_components_grid_grid_item_vue__ = __webpack_require__(2);




/* unused harmony default export */ var _unused_webpack_default_export = ({
  props: {
    items: Array,
    default: []
  },
  components: {
    Grid: __WEBPACK_IMPORTED_MODULE_0_vux_src_components_grid_grid_vue__["a" /* default */],
    GridItem: __WEBPACK_IMPORTED_MODULE_1_vux_src_components_grid_grid_item_vue__["a" /* default */]
  }
});

/***/ }),
/* 221 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_vux_src_components_grid_grid_vue__ = __webpack_require__(1);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_vux_src_components_grid_grid_item_vue__ = __webpack_require__(2);




/* harmony default export */ __webpack_exports__["a"] = ({
  props: {
    items: Array,
    default: []
  },
  components: {
    Grid: __WEBPACK_IMPORTED_MODULE_0_vux_src_components_grid_grid_vue__["a" /* default */],
    GridItem: __WEBPACK_IMPORTED_MODULE_1_vux_src_components_grid_grid_item_vue__["a" /* default */]
  }
});

/***/ }),
/* 222 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',[_vm._m(0),_vm._v(" "),_c('grid',{staticStyle:{"background":"#FFF"},attrs:{"cols":1}},_vm._l((_vm.items),function(item,index){return _c('grid-item',{key:index,staticClass:"cube_grid_img",staticStyle:{"margin-top":"0.2em"},attrs:{"link":item.url,"data-ratio":"5:1"}},[_c('img',{attrs:{"src":item.img}})])}))],1)}
var staticRenderFns = [function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{staticClass:"left_title"},[_c('a',{attrs:{"href":""}},[_vm._v("热门广告")])])}]
var esExports = { render: render, staticRenderFns: staticRenderFns }
/* harmony default export */ __webpack_exports__["a"] = (esExports);

/***/ }),
/* 223 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vux_loader_src_script_loader_js_node_modules_vux_loader_src_script_loader_js_node_modules_vue_loader_lib_selector_type_script_index_0_index_vue__ = __webpack_require__(225);
/* unused harmony reexport namespace */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__babel_loader_node_modules_vux_loader_src_script_loader_js_node_modules_vue_loader_lib_selector_type_script_index_0_index_vue__ = __webpack_require__(233);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__node_modules_vue_loader_lib_template_compiler_index_id_data_v_692b0450_hasScoped_true_buble_transforms_node_modules_vux_loader_src_before_template_compiler_loader_js_node_modules_vux_loader_src_template_loader_js_node_modules_vue_loader_lib_selector_type_template_index_0_index_vue__ = __webpack_require__(234);
function injectStyle (ssrContext) {
  __webpack_require__(224)
}
var normalizeComponent = __webpack_require__(0)
/* script */


/* template */

/* template functional */
var __vue_template_functional__ = false
/* styles */
var __vue_styles__ = injectStyle
/* scopeId */
var __vue_scopeId__ = "data-v-692b0450"
/* moduleIdentifier (server only) */
var __vue_module_identifier__ = null
var Component = normalizeComponent(
  __WEBPACK_IMPORTED_MODULE_1__babel_loader_node_modules_vux_loader_src_script_loader_js_node_modules_vue_loader_lib_selector_type_script_index_0_index_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_2__node_modules_vue_loader_lib_template_compiler_index_id_data_v_692b0450_hasScoped_true_buble_transforms_node_modules_vux_loader_src_before_template_compiler_loader_js_node_modules_vux_loader_src_template_loader_js_node_modules_vue_loader_lib_selector_type_template_index_0_index_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)

/* unused harmony default export */ var _unused_webpack_default_export = (Component.exports);


/***/ }),
/* 224 */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),
/* 225 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_vux_src_components_group_index_vue__ = __webpack_require__(14);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_vux_src_components_marquee_marquee_vue__ = __webpack_require__(122);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2_vux_src_components_marquee_marquee_item_vue__ = __webpack_require__(123);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_3_vux_src_components_cell_index_vue__ = __webpack_require__(16);






/* unused harmony default export */ var _unused_webpack_default_export = ({
  props: {
    items: Array

  },
  components: {
    Group: __WEBPACK_IMPORTED_MODULE_0_vux_src_components_group_index_vue__["a" /* default */],
    Marquee: __WEBPACK_IMPORTED_MODULE_1_vux_src_components_marquee_marquee_vue__["a" /* default */],
    MarqueeItem: __WEBPACK_IMPORTED_MODULE_2_vux_src_components_marquee_marquee_item_vue__["a" /* default */],
    Cell: __WEBPACK_IMPORTED_MODULE_3_vux_src_components_cell_index_vue__["a" /* default */]
  }
});

/***/ }),
/* 226 */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),
/* 227 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";


/* unused harmony default export */ var _unused_webpack_default_export = ({
  name: 'marquee',
  props: {
    interval: {
      type: Number,
      default: 2000
    },
    duration: {
      type: Number,
      default: 300
    },
    direction: {
      type: String,
      default: 'up'
    },
    itemHeight: Number
  },
  beforeDestroy: function beforeDestroy() {
    this.destroy();
  },
  data: function data() {
    return {
      currenTranslateY: 0,
      height: '',
      length: 0,
      currentIndex: 0,
      noAnimate: false
    };
  },

  methods: {
    destroy: function destroy() {
      this.timer && clearInterval(this.timer);
    },
    init: function init() {
      this.destroy();

      if (this.cloneNode) {
        this.$refs.box.removeChild(this.cloneNode);
      }

      this.cloneNode = null;
      var firstItem = this.$refs.box.firstElementChild;
      if (!firstItem) {
        return false;
      }
      this.length = this.$refs.box.children.length;
      this.height = this.itemHeight || firstItem.offsetHeight;

      if (this.direction === 'up') {
        this.cloneNode = firstItem.cloneNode(true);
        this.$refs.box.appendChild(this.cloneNode);
      } else {
        this.cloneNode = this.$refs.box.lastElementChild.cloneNode(true);
        this.$refs.box.insertBefore(this.cloneNode, firstItem);
      }
      return true;
    },
    start: function start() {
      var _this = this;

      if (this.direction === 'down') this.go(false);
      this.timer = setInterval(function () {
        if (_this.direction === 'up') {
          _this.currentIndex += 1;
          _this.currenTranslateY = -_this.currentIndex * _this.height;
        } else {
          _this.currentIndex -= 1;
          _this.currenTranslateY = -(_this.currentIndex + 1) * _this.height;
        }
        if (_this.currentIndex === _this.length) {
          setTimeout(function () {
            _this.go(true);
          }, _this.duration);
        } else if (_this.currentIndex === -1) {
          setTimeout(function () {
            _this.go(false);
          }, _this.duration);
        } else {
          _this.noAnimate = false;
        }
      }, this.interval + this.duration);
    },
    go: function go(toFirst) {
      this.noAnimate = true;
      if (toFirst) {
        this.currentIndex = 0;
        this.currenTranslateY = 0;
      } else {
        this.currentIndex = this.length - 1;
        this.currenTranslateY = -(this.currentIndex + 1) * this.height;
      }
    }
  }
});

/***/ }),
/* 228 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";


/* harmony default export */ __webpack_exports__["a"] = ({
  name: 'marquee',
  props: {
    interval: {
      type: Number,
      default: 2000
    },
    duration: {
      type: Number,
      default: 300
    },
    direction: {
      type: String,
      default: 'up'
    },
    itemHeight: Number
  },
  beforeDestroy: function beforeDestroy() {
    this.destroy();
  },
  data: function data() {
    return {
      currenTranslateY: 0,
      height: '',
      length: 0,
      currentIndex: 0,
      noAnimate: false
    };
  },

  methods: {
    destroy: function destroy() {
      this.timer && clearInterval(this.timer);
    },
    init: function init() {
      this.destroy();

      if (this.cloneNode) {
        this.$refs.box.removeChild(this.cloneNode);
      }

      this.cloneNode = null;
      var firstItem = this.$refs.box.firstElementChild;
      if (!firstItem) {
        return false;
      }
      this.length = this.$refs.box.children.length;
      this.height = this.itemHeight || firstItem.offsetHeight;

      if (this.direction === 'up') {
        this.cloneNode = firstItem.cloneNode(true);
        this.$refs.box.appendChild(this.cloneNode);
      } else {
        this.cloneNode = this.$refs.box.lastElementChild.cloneNode(true);
        this.$refs.box.insertBefore(this.cloneNode, firstItem);
      }
      return true;
    },
    start: function start() {
      var _this = this;

      if (this.direction === 'down') this.go(false);
      this.timer = setInterval(function () {
        if (_this.direction === 'up') {
          _this.currentIndex += 1;
          _this.currenTranslateY = -_this.currentIndex * _this.height;
        } else {
          _this.currentIndex -= 1;
          _this.currenTranslateY = -(_this.currentIndex + 1) * _this.height;
        }
        if (_this.currentIndex === _this.length) {
          setTimeout(function () {
            _this.go(true);
          }, _this.duration);
        } else if (_this.currentIndex === -1) {
          setTimeout(function () {
            _this.go(false);
          }, _this.duration);
        } else {
          _this.noAnimate = false;
        }
      }, this.interval + this.duration);
    },
    go: function go(toFirst) {
      this.noAnimate = true;
      if (toFirst) {
        this.currentIndex = 0;
        this.currenTranslateY = 0;
      } else {
        this.currentIndex = this.length - 1;
        this.currenTranslateY = -(this.currentIndex + 1) * this.height;
      }
    }
  }
});

/***/ }),
/* 229 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{staticClass:"vux-marquee",style:({height: _vm.height + 'px'})},[_c('ul',{ref:"box",staticClass:"vux-marquee-box",style:({transform: ("translate3d(0," + _vm.currenTranslateY + "px,0)"), transition: ("transform " + (_vm.noAnimate ? 0 : _vm.duration) + "ms")})},[_vm._t("default")],2)])}
var staticRenderFns = []
var esExports = { render: render, staticRenderFns: staticRenderFns }
/* harmony default export */ __webpack_exports__["a"] = (esExports);

/***/ }),
/* 230 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";


/* unused harmony default export */ var _unused_webpack_default_export = ({
  name: 'marquee-item',
  mounted: function mounted() {
    var _this = this;

    this.$nextTick(function () {
      _this.$parent.destroy();
      _this.$parent.init();
      _this.$parent.start();
    });
  }
});

/***/ }),
/* 231 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";


/* harmony default export */ __webpack_exports__["a"] = ({
  name: 'marquee-item',
  mounted: function mounted() {
    var _this = this;

    this.$nextTick(function () {
      _this.$parent.destroy();
      _this.$parent.init();
      _this.$parent.start();
    });
  }
});

/***/ }),
/* 232 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('li',[_vm._t("default")],2)}
var staticRenderFns = []
var esExports = { render: render, staticRenderFns: staticRenderFns }
/* harmony default export */ __webpack_exports__["a"] = (esExports);

/***/ }),
/* 233 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_vux_src_components_group_index_vue__ = __webpack_require__(14);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_vux_src_components_marquee_marquee_vue__ = __webpack_require__(122);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2_vux_src_components_marquee_marquee_item_vue__ = __webpack_require__(123);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_3_vux_src_components_cell_index_vue__ = __webpack_require__(16);






/* harmony default export */ __webpack_exports__["a"] = ({
  props: {
    items: Array

  },
  components: {
    Group: __WEBPACK_IMPORTED_MODULE_0_vux_src_components_group_index_vue__["a" /* default */],
    Marquee: __WEBPACK_IMPORTED_MODULE_1_vux_src_components_marquee_marquee_vue__["a" /* default */],
    MarqueeItem: __WEBPACK_IMPORTED_MODULE_2_vux_src_components_marquee_marquee_item_vue__["a" /* default */],
    Cell: __WEBPACK_IMPORTED_MODULE_3_vux_src_components_cell_index_vue__["a" /* default */]
  }
});

/***/ }),
/* 234 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('group',{staticClass:"default-group",staticStyle:{"margin-top":"-15px"}},[_c('cell',[_c('i',{staticClass:"iconfamily icon-gonggao",attrs:{"slot":"icon"},slot:"icon"}),_vm._v(" "),_c('marquee',_vm._l((_vm.items),function(item,index){return _c('marquee-item',{key:index},[_c('a',{staticClass:"notice",staticStyle:{"color":"#999999"},attrs:{"href":item.url},domProps:{"innerHTML":_vm._s(item.title)}})])}))],1)],1)}
var staticRenderFns = []
var esExports = { render: render, staticRenderFns: staticRenderFns }
/* harmony default export */ __webpack_exports__["a"] = (esExports);

/***/ }),
/* 235 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vux_loader_src_script_loader_js_node_modules_vux_loader_src_script_loader_js_node_modules_vue_loader_lib_selector_type_script_index_0_index_vue__ = __webpack_require__(237);
/* unused harmony reexport namespace */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__babel_loader_node_modules_vux_loader_src_script_loader_js_node_modules_vue_loader_lib_selector_type_script_index_0_index_vue__ = __webpack_require__(238);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__node_modules_vue_loader_lib_template_compiler_index_id_data_v_88242fa2_hasScoped_false_buble_transforms_node_modules_vux_loader_src_before_template_compiler_loader_js_node_modules_vux_loader_src_template_loader_js_node_modules_vue_loader_lib_selector_type_template_index_0_index_vue__ = __webpack_require__(239);
function injectStyle (ssrContext) {
  __webpack_require__(236)
}
var normalizeComponent = __webpack_require__(0)
/* script */


/* template */

/* template functional */
var __vue_template_functional__ = false
/* styles */
var __vue_styles__ = injectStyle
/* scopeId */
var __vue_scopeId__ = null
/* moduleIdentifier (server only) */
var __vue_module_identifier__ = null
var Component = normalizeComponent(
  __WEBPACK_IMPORTED_MODULE_1__babel_loader_node_modules_vux_loader_src_script_loader_js_node_modules_vue_loader_lib_selector_type_script_index_0_index_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_2__node_modules_vue_loader_lib_template_compiler_index_id_data_v_88242fa2_hasScoped_false_buble_transforms_node_modules_vux_loader_src_before_template_compiler_loader_js_node_modules_vux_loader_src_template_loader_js_node_modules_vue_loader_lib_selector_type_template_index_0_index_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)

/* unused harmony default export */ var _unused_webpack_default_export = (Component.exports);


/***/ }),
/* 236 */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),
/* 237 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_vux_src_components_grid_grid_vue__ = __webpack_require__(1);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_vux_src_components_grid_grid_item_vue__ = __webpack_require__(2);




/* unused harmony default export */ var _unused_webpack_default_export = ({
  props: {
    items: {
      type: Array,
      default: []
    }
  },
  components: {
    Grid: __WEBPACK_IMPORTED_MODULE_0_vux_src_components_grid_grid_vue__["a" /* default */], GridItem: __WEBPACK_IMPORTED_MODULE_1_vux_src_components_grid_grid_item_vue__["a" /* default */]
  }
});

/***/ }),
/* 238 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_vux_src_components_grid_grid_vue__ = __webpack_require__(1);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_vux_src_components_grid_grid_item_vue__ = __webpack_require__(2);




/* harmony default export */ __webpack_exports__["a"] = ({
  props: {
    items: {
      type: Array,
      default: []
    }
  },
  components: {
    Grid: __WEBPACK_IMPORTED_MODULE_0_vux_src_components_grid_grid_vue__["a" /* default */], GridItem: __WEBPACK_IMPORTED_MODULE_1_vux_src_components_grid_grid_item_vue__["a" /* default */]
  }
});

/***/ }),
/* 239 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return (_vm.items.length ===1)?_c('grid',{staticClass:"cube_grids"},[_c('grid-item',{staticClass:"cube_grid width_whole",attrs:{"link":_vm.items[0].url}},[_c('div',{staticClass:"cube_grid_img",attrs:{"data-ratio":"2:1"}},[_c('img',{attrs:{"slot":"icon","src":_vm.items[0].img},slot:"icon"})])])],1):(_vm.items.length === 2)?_c('grid',{staticClass:"cube_grids"},_vm._l((_vm.items),function(item,index){return _c('grid-item',{key:index,staticClass:"cube_grid cube_grid width_half",attrs:{"link":item.url}},[_c('div',{staticClass:"cube_grid_img",attrs:{"data-ratio":"2:1"}},[_c('img',{attrs:{"slot":"icon","src":item.img},slot:"icon"})])])})):(_vm.items.length === 3)?_c('grid',{staticClass:"cube_grids"},[_c('grid-item',{staticClass:"cube_grid width_half cube_pading",attrs:{"link":_vm.items[0].url}},[_c('div',{staticClass:"cube_grid_img",attrs:{"data-ratio":"1:1"}},[_c('img',{attrs:{"slot":"icon","src":_vm.items[0].img},slot:"icon"})])]),_vm._v(" "),_c('grid-item',{staticClass:"cube_grid width_half",attrs:{"link":_vm.items[1].url}},[_c('div',{staticClass:"cube_pading_bottom"},[_c('div',{staticClass:"cube_grid_img",attrs:{"data-ratio":"2:1"}},[_c('img',{attrs:{"slot":"icon","src":_vm.items[1].img},slot:"icon"})])])]),_vm._v(" "),_c('grid-item',{staticClass:"cube_grid width_half",attrs:{"link":_vm.items[2].url}},[_c('div',{staticClass:"cube_pading_bottom"},[_c('div',{staticClass:"cube_grid_img",attrs:{"data-ratio":"2:1"}},[_c('img',{attrs:{"slot":"icon","src":_vm.items[2].img},slot:"icon"})])])])],1):(_vm.items.length === 4)?_c('grid',{staticClass:"cube_grids"},[_c('grid-item',{staticClass:"cube_grid width_half",attrs:{"link":_vm.items[0].url}},[_c('div',{staticClass:"cube_grid_img",attrs:{"data-ratio":"1:1"}},[_c('img',{attrs:{"slot":"icon","src":_vm.items[0].img},slot:"icon"})])]),_vm._v(" "),_c('grid-item',{staticClass:"cube_grid width_half"},[_c('div',{staticClass:"width_whole"},[_c('a',{attrs:{"href":_vm.items[1].url}},[_c('div',{staticClass:"cube_grid_img",attrs:{"data-ratio":"2:1"}},[_c('img',{attrs:{"slot":"icon","src":_vm.items[1].img},slot:"icon"})])])]),_vm._v(" "),_c('div',{staticClass:"width_half",attrs:{"link":_vm.items[2].url}},[_c('a',{attrs:{"href":_vm.items[2].url}},[_c('div',{staticClass:"cube_grid_img",attrs:{"data-ratio":"1:1"}},[_c('img',{attrs:{"slot":"icon","src":_vm.items[2].img},slot:"icon"})])])]),_vm._v(" "),_c('div',{staticClass:"width_half",attrs:{"link":_vm.items[3].url}},[_c('a',{attrs:{"href":_vm.items[3].url}},[_c('div',{staticClass:"cube_grid_img",attrs:{"data-ratio":"1:1"}},[_c('img',{attrs:{"slot":"icon","src":_vm.items[3].img},slot:"icon"})])])])])],1):(_vm.items.length === 5)?_c('grid',{staticClass:"cube_grids"},[_c('grid-item',{staticClass:"cube_grid width_half",attrs:{"link":_vm.items[0].url}},[_c('div',{staticClass:"cube_grid_img",attrs:{"data-ratio":"1:1"}},[_c('img',{attrs:{"slot":"icon","src":_vm.items[0].img},slot:"icon"})])]),_vm._v(" "),_c('grid-item',{staticClass:"cube_grid width_half"},[_c('div',{staticClass:"width_half"},[_c('a',{attrs:{"href":_vm.items[1].url}},[_c('div',{staticClass:"cube_grid_img",attrs:{"data-ratio":"1:1"}},[_c('img',{attrs:{"slot":"icon","src":_vm.items[1].img},slot:"icon"})])])]),_vm._v(" "),_c('div',{staticClass:"width_half",attrs:{"link":_vm.items[2].url}},[_c('a',{attrs:{"href":_vm.items[2].url}},[_c('div',{staticClass:"cube_grid_img",attrs:{"data-ratio":"1:1"}},[_c('img',{attrs:{"slot":"icon","src":_vm.items[2].img},slot:"icon"})])])]),_vm._v(" "),_c('div',{staticClass:"width_half",attrs:{"link":_vm.items[3].url}},[_c('a',{attrs:{"href":_vm.items[3].url}},[_c('div',{staticClass:"cube_grid_img",attrs:{"data-ratio":"1:1"}},[_c('img',{attrs:{"slot":"icon","src":_vm.items[3].img},slot:"icon"})])])]),_vm._v(" "),_c('div',{staticClass:"width_half",attrs:{"link":_vm.items[4].url}},[_c('a',{attrs:{"href":_vm.items[4].url}},[_c('div',{staticClass:"cube_grid_img",attrs:{"data-ratio":"1:1"}},[_c('img',{attrs:{"slot":"icon","src":_vm.items[4].img},slot:"icon"})])])])])],1):(_vm.items.length === 6)?_c('grid',{staticClass:"cube_grids"},[_c('grid-item',{staticClass:"cube_grid width_half"},[_c('a',{attrs:{"href":_vm.items[0].url}},[_c('div',{staticClass:"cube_grid_img",attrs:{"data-ratio":"2:1"}},[_c('img',{attrs:{"slot":"icon","src":_vm.items[0].img},slot:"icon"})])]),_vm._v(" "),_c('a',{attrs:{"href":_vm.items[1].url}},[_c('div',{staticClass:"cube_grid_img",attrs:{"data-ratio":"2:1","link":_vm.items[1].url}},[_c('img',{attrs:{"slot":"icon","src":_vm.items[1].img},slot:"icon"})])]),_vm._v(" "),_c('a',{attrs:{"href":_vm.items[2].url}},[_c('div',{staticClass:"cube_grid_img",attrs:{"data-ratio":"2:1","link":_vm.items[2].url}},[_c('img',{attrs:{"slot":"icon","src":_vm.items[2].img},slot:"icon"})])])]),_vm._v(" "),_c('grid-item',{staticClass:"cube_grid width_half"},[_c('a',{attrs:{"href":_vm.items[3].url}},[_c('div',{staticClass:"cube_grid_img",attrs:{"data-ratio":"2:1","link":_vm.items[3].url}},[_c('img',{attrs:{"slot":"icon","src":_vm.items[3].img},slot:"icon"})])]),_vm._v(" "),_c('a',{attrs:{"href":_vm.items[4].url}},[_c('div',{staticClass:"cube_grid_img",attrs:{"data-ratio":"2:1","link":_vm.items[4].url}},[_c('img',{attrs:{"slot":"icon","src":_vm.items[4].img},slot:"icon"})])]),_vm._v(" "),_c('a',{attrs:{"href":_vm.items[5].url}},[_c('div',{staticClass:"cube_grid_img",attrs:{"data-ratio":"2:1","link":_vm.items[5].url}},[_c('img',{attrs:{"slot":"icon","src":_vm.items[5].img},slot:"icon"})])])])],1):_vm._e()}
var staticRenderFns = []
var esExports = { render: render, staticRenderFns: staticRenderFns }
/* harmony default export */ __webpack_exports__["a"] = (esExports);

/***/ }),
/* 240 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vux_loader_src_script_loader_js_node_modules_vux_loader_src_script_loader_js_node_modules_vue_loader_lib_selector_type_script_index_0_index_vue__ = __webpack_require__(242);
/* unused harmony reexport namespace */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__babel_loader_node_modules_vux_loader_src_script_loader_js_node_modules_vue_loader_lib_selector_type_script_index_0_index_vue__ = __webpack_require__(243);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__node_modules_vue_loader_lib_template_compiler_index_id_data_v_5a531f45_hasScoped_false_buble_transforms_node_modules_vux_loader_src_before_template_compiler_loader_js_node_modules_vux_loader_src_template_loader_js_node_modules_vue_loader_lib_selector_type_template_index_0_index_vue__ = __webpack_require__(244);
function injectStyle (ssrContext) {
  __webpack_require__(241)
}
var normalizeComponent = __webpack_require__(0)
/* script */


/* template */

/* template functional */
var __vue_template_functional__ = false
/* styles */
var __vue_styles__ = injectStyle
/* scopeId */
var __vue_scopeId__ = null
/* moduleIdentifier (server only) */
var __vue_module_identifier__ = null
var Component = normalizeComponent(
  __WEBPACK_IMPORTED_MODULE_1__babel_loader_node_modules_vux_loader_src_script_loader_js_node_modules_vue_loader_lib_selector_type_script_index_0_index_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_2__node_modules_vue_loader_lib_template_compiler_index_id_data_v_5a531f45_hasScoped_false_buble_transforms_node_modules_vux_loader_src_before_template_compiler_loader_js_node_modules_vux_loader_src_template_loader_js_node_modules_vue_loader_lib_selector_type_template_index_0_index_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)

/* unused harmony default export */ var _unused_webpack_default_export = (Component.exports);


/***/ }),
/* 241 */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),
/* 242 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_vux_src_components_swiper_swiper_vue__ = __webpack_require__(8);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_vux_src_components_swiper_swiper_item_vue__ = __webpack_require__(15);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2_vux_src_components_grid_grid_vue__ = __webpack_require__(1);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_3_vux_src_components_grid_grid_item_vue__ = __webpack_require__(2);






/* unused harmony default export */ var _unused_webpack_default_export = ({
  props: {
    items: Array
  },
  components: {
    Swiper: __WEBPACK_IMPORTED_MODULE_0_vux_src_components_swiper_swiper_vue__["a" /* default */], SwiperItem: __WEBPACK_IMPORTED_MODULE_1_vux_src_components_swiper_swiper_item_vue__["a" /* default */], Grid: __WEBPACK_IMPORTED_MODULE_2_vux_src_components_grid_grid_vue__["a" /* default */], GridItem: __WEBPACK_IMPORTED_MODULE_3_vux_src_components_grid_grid_item_vue__["a" /* default */]
  },
  computed: {

    listTemp: function listTemp() {
      var list = this.items;
      var arrTemp = [];
      var index = 0;
      var sectionCount = 5;
      console.log(list.length);
      for (var i = 0; i < list.length; i++) {
        index = parseInt(i / sectionCount);
        console.log(index);
        if (arrTemp.length <= index) {
          arrTemp.push([]);
        }
        arrTemp[index].push(list[i]);
      }
      console.log(arrTemp);
      return arrTemp;
    }
  }
});

/***/ }),
/* 243 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_vux_src_components_swiper_swiper_vue__ = __webpack_require__(8);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_vux_src_components_swiper_swiper_item_vue__ = __webpack_require__(15);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2_vux_src_components_grid_grid_vue__ = __webpack_require__(1);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_3_vux_src_components_grid_grid_item_vue__ = __webpack_require__(2);






/* harmony default export */ __webpack_exports__["a"] = ({
  props: {
    items: Array
  },
  components: {
    Swiper: __WEBPACK_IMPORTED_MODULE_0_vux_src_components_swiper_swiper_vue__["a" /* default */], SwiperItem: __WEBPACK_IMPORTED_MODULE_1_vux_src_components_swiper_swiper_item_vue__["a" /* default */], Grid: __WEBPACK_IMPORTED_MODULE_2_vux_src_components_grid_grid_vue__["a" /* default */], GridItem: __WEBPACK_IMPORTED_MODULE_3_vux_src_components_grid_grid_item_vue__["a" /* default */]
  },
  computed: {

    listTemp: function listTemp() {
      var list = this.items;
      var arrTemp = [];
      var index = 0;
      var sectionCount = 5;
      console.log(list.length);
      for (var i = 0; i < list.length; i++) {
        index = parseInt(i / sectionCount);
        console.log(index);
        if (arrTemp.length <= index) {
          arrTemp.push([]);
        }
        arrTemp[index].push(list[i]);
      }
      console.log(arrTemp);
      return arrTemp;
    }
  }
});

/***/ }),
/* 244 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('swiper',{staticStyle:{"background":"#fff","margin-top":"5px"},attrs:{"show-dots":false,"dots-position":"center","height":"70px"}},_vm._l((_vm.listTemp),function(row,key){return _c('swiper-item',{key:key,attrs:{"dots-position":"center"}},[_c('div',{staticClass:"navlist"},[_c('grid',{attrs:{"cols":5}},_vm._l((row),function(item,index){return _c('grid-item',{key:index,attrs:{"label":item.title,"link":item.url}},[_c('img',{attrs:{"slot":"icon","src":item.thumb},slot:"icon"})])}))],1)])}))}
var staticRenderFns = []
var esExports = { render: render, staticRenderFns: staticRenderFns }
/* harmony default export */ __webpack_exports__["a"] = (esExports);

/***/ }),
/* 245 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vux_loader_src_script_loader_js_node_modules_vux_loader_src_script_loader_js_node_modules_vue_loader_lib_selector_type_script_index_0_index_vue__ = __webpack_require__(247);
/* unused harmony reexport namespace */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__babel_loader_node_modules_vux_loader_src_script_loader_js_node_modules_vue_loader_lib_selector_type_script_index_0_index_vue__ = __webpack_require__(248);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__node_modules_vue_loader_lib_template_compiler_index_id_data_v_33654204_hasScoped_true_buble_transforms_node_modules_vux_loader_src_before_template_compiler_loader_js_node_modules_vux_loader_src_template_loader_js_node_modules_vue_loader_lib_selector_type_template_index_0_index_vue__ = __webpack_require__(249);
function injectStyle (ssrContext) {
  __webpack_require__(246)
}
var normalizeComponent = __webpack_require__(0)
/* script */


/* template */

/* template functional */
var __vue_template_functional__ = false
/* styles */
var __vue_styles__ = injectStyle
/* scopeId */
var __vue_scopeId__ = "data-v-33654204"
/* moduleIdentifier (server only) */
var __vue_module_identifier__ = null
var Component = normalizeComponent(
  __WEBPACK_IMPORTED_MODULE_1__babel_loader_node_modules_vux_loader_src_script_loader_js_node_modules_vue_loader_lib_selector_type_script_index_0_index_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_2__node_modules_vue_loader_lib_template_compiler_index_id_data_v_33654204_hasScoped_true_buble_transforms_node_modules_vux_loader_src_before_template_compiler_loader_js_node_modules_vux_loader_src_template_loader_js_node_modules_vue_loader_lib_selector_type_template_index_0_index_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)

/* unused harmony default export */ var _unused_webpack_default_export = (Component.exports);


/***/ }),
/* 246 */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),
/* 247 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_vux_src_components_swiper_swiper_vue__ = __webpack_require__(8);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_vux_src_components_swiper_swiper_item_vue__ = __webpack_require__(15);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2_vux_src_components_grid_grid_vue__ = __webpack_require__(1);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_3_vux_src_components_grid_grid_item_vue__ = __webpack_require__(2);






/* unused harmony default export */ var _unused_webpack_default_export = ({
  props: {
    items: Array
  },
  components: {
    Swiper: __WEBPACK_IMPORTED_MODULE_0_vux_src_components_swiper_swiper_vue__["a" /* default */], SwiperItem: __WEBPACK_IMPORTED_MODULE_1_vux_src_components_swiper_swiper_item_vue__["a" /* default */], Grid: __WEBPACK_IMPORTED_MODULE_2_vux_src_components_grid_grid_vue__["a" /* default */], GridItem: __WEBPACK_IMPORTED_MODULE_3_vux_src_components_grid_grid_item_vue__["a" /* default */]
  },
  computed: {

    listTemp: function listTemp() {
      var list = this.items;
      var arrTemp = [];
      var index = 0;
      var sectionCount = 8;
      console.log(list.length);
      for (var i = 0; i < list.length; i++) {
        index = parseInt(i / sectionCount);
        console.log(index);
        if (arrTemp.length <= index) {
          arrTemp.push([]);
        }
        arrTemp[index].push(list[i]);
      }
      console.log(arrTemp);
      return arrTemp;
    }
  }
});

/***/ }),
/* 248 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_vux_src_components_swiper_swiper_vue__ = __webpack_require__(8);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_vux_src_components_swiper_swiper_item_vue__ = __webpack_require__(15);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2_vux_src_components_grid_grid_vue__ = __webpack_require__(1);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_3_vux_src_components_grid_grid_item_vue__ = __webpack_require__(2);






/* harmony default export */ __webpack_exports__["a"] = ({
  props: {
    items: Array
  },
  components: {
    Swiper: __WEBPACK_IMPORTED_MODULE_0_vux_src_components_swiper_swiper_vue__["a" /* default */], SwiperItem: __WEBPACK_IMPORTED_MODULE_1_vux_src_components_swiper_swiper_item_vue__["a" /* default */], Grid: __WEBPACK_IMPORTED_MODULE_2_vux_src_components_grid_grid_vue__["a" /* default */], GridItem: __WEBPACK_IMPORTED_MODULE_3_vux_src_components_grid_grid_item_vue__["a" /* default */]
  },
  computed: {

    listTemp: function listTemp() {
      var list = this.items;
      var arrTemp = [];
      var index = 0;
      var sectionCount = 8;
      console.log(list.length);
      for (var i = 0; i < list.length; i++) {
        index = parseInt(i / sectionCount);
        console.log(index);
        if (arrTemp.length <= index) {
          arrTemp.push([]);
        }
        arrTemp[index].push(list[i]);
      }
      console.log(arrTemp);
      return arrTemp;
    }
  }
});

/***/ }),
/* 249 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('swiper',{staticStyle:{"background":"#fff","margin-top":"5px","height":"180px"},attrs:{"dots-position":"center","dots-class":"custom-bottom"}},_vm._l((_vm.listTemp),function(row,key){return _c('swiper-item',{key:key,staticStyle:{"margin-top":"15px"},attrs:{"dots-position":"center"}},[_c('div',{staticClass:"navlist"},[_c('grid',{attrs:{"cols":4}},_vm._l((row),function(item,index){return _c('grid-item',{key:index,attrs:{"label":item.title,"link":item.url}},[_c('img',{attrs:{"slot":"icon","src":item.thumb},slot:"icon"})])}))],1)])}))}
var staticRenderFns = []
var esExports = { render: render, staticRenderFns: staticRenderFns }
/* harmony default export */ __webpack_exports__["a"] = (esExports);

/***/ }),
/* 250 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vux_loader_src_script_loader_js_node_modules_vux_loader_src_script_loader_js_node_modules_vue_loader_lib_selector_type_script_index_0_index_vue__ = __webpack_require__(252);
/* unused harmony reexport namespace */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__babel_loader_node_modules_vux_loader_src_script_loader_js_node_modules_vue_loader_lib_selector_type_script_index_0_index_vue__ = __webpack_require__(253);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__node_modules_vue_loader_lib_template_compiler_index_id_data_v_54d17681_hasScoped_false_buble_transforms_node_modules_vux_loader_src_before_template_compiler_loader_js_node_modules_vux_loader_src_template_loader_js_node_modules_vue_loader_lib_selector_type_template_index_0_index_vue__ = __webpack_require__(254);
function injectStyle (ssrContext) {
  __webpack_require__(251)
}
var normalizeComponent = __webpack_require__(0)
/* script */


/* template */

/* template functional */
var __vue_template_functional__ = false
/* styles */
var __vue_styles__ = injectStyle
/* scopeId */
var __vue_scopeId__ = null
/* moduleIdentifier (server only) */
var __vue_module_identifier__ = null
var Component = normalizeComponent(
  __WEBPACK_IMPORTED_MODULE_1__babel_loader_node_modules_vux_loader_src_script_loader_js_node_modules_vue_loader_lib_selector_type_script_index_0_index_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_2__node_modules_vue_loader_lib_template_compiler_index_id_data_v_54d17681_hasScoped_false_buble_transforms_node_modules_vux_loader_src_before_template_compiler_loader_js_node_modules_vux_loader_src_template_loader_js_node_modules_vue_loader_lib_selector_type_template_index_0_index_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)

/* unused harmony default export */ var _unused_webpack_default_export = (Component.exports);


/***/ }),
/* 251 */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),
/* 252 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_vux_src_components_grid_grid_vue__ = __webpack_require__(1);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_vux_src_components_grid_grid_item_vue__ = __webpack_require__(2);




/* unused harmony default export */ var _unused_webpack_default_export = ({
  props: {
    items: Array,
    default: []
  },
  components: {
    Grid: __WEBPACK_IMPORTED_MODULE_0_vux_src_components_grid_grid_vue__["a" /* default */],
    GridItem: __WEBPACK_IMPORTED_MODULE_1_vux_src_components_grid_grid_item_vue__["a" /* default */]
  }
});

/***/ }),
/* 253 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_vux_src_components_grid_grid_vue__ = __webpack_require__(1);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_vux_src_components_grid_grid_item_vue__ = __webpack_require__(2);




/* harmony default export */ __webpack_exports__["a"] = ({
  props: {
    items: Array,
    default: []
  },
  components: {
    Grid: __WEBPACK_IMPORTED_MODULE_0_vux_src_components_grid_grid_vue__["a" /* default */],
    GridItem: __WEBPACK_IMPORTED_MODULE_1_vux_src_components_grid_grid_item_vue__["a" /* default */]
  }
});

/***/ }),
/* 254 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',[_vm._m(0),_vm._v(" "),_c('grid',{staticStyle:{"background":"#FFF"},attrs:{"cols":3}},_vm._l((_vm.items),function(item,index){return _c('grid-item',{key:index,staticClass:"Info-grids",attrs:{"link":item.url}},[_c('div',{staticClass:"InfoPicture"},[_c('img',{staticClass:"lazyimg",attrs:{"src":item.thumb}})]),_vm._v(" "),_c('div',{staticClass:"InfoTitle"},[_vm._v(_vm._s(item.title))]),_vm._v(" "),_c('div',{staticClass:"InfoPrice"},[_vm._v("￥"+_vm._s(item.marketprice)+" "),_c('span',[_c('del',[_vm._v("￥"+_vm._s(item.productprice))])])])])}))],1)}
var staticRenderFns = [function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{staticClass:"left_title"},[_c('a',{attrs:{"href":""}},[_vm._v("推荐商品")])])}]
var esExports = { render: render, staticRenderFns: staticRenderFns }
/* harmony default export */ __webpack_exports__["a"] = (esExports);

/***/ }),
/* 255 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vux_loader_src_script_loader_js_node_modules_vux_loader_src_script_loader_js_node_modules_vue_loader_lib_selector_type_script_index_0_index_vue__ = __webpack_require__(257);
/* unused harmony reexport namespace */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__babel_loader_node_modules_vux_loader_src_script_loader_js_node_modules_vue_loader_lib_selector_type_script_index_0_index_vue__ = __webpack_require__(262);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__node_modules_vue_loader_lib_template_compiler_index_id_data_v_7e4dc048_hasScoped_true_buble_transforms_node_modules_vux_loader_src_before_template_compiler_loader_js_node_modules_vux_loader_src_template_loader_js_node_modules_vue_loader_lib_selector_type_template_index_0_index_vue__ = __webpack_require__(263);
function injectStyle (ssrContext) {
  __webpack_require__(256)
}
var normalizeComponent = __webpack_require__(0)
/* script */


/* template */

/* template functional */
var __vue_template_functional__ = false
/* styles */
var __vue_styles__ = injectStyle
/* scopeId */
var __vue_scopeId__ = "data-v-7e4dc048"
/* moduleIdentifier (server only) */
var __vue_module_identifier__ = null
var Component = normalizeComponent(
  __WEBPACK_IMPORTED_MODULE_1__babel_loader_node_modules_vux_loader_src_script_loader_js_node_modules_vue_loader_lib_selector_type_script_index_0_index_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_2__node_modules_vue_loader_lib_template_compiler_index_id_data_v_7e4dc048_hasScoped_true_buble_transforms_node_modules_vux_loader_src_before_template_compiler_loader_js_node_modules_vux_loader_src_template_loader_js_node_modules_vue_loader_lib_selector_type_template_index_0_index_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)

/* unused harmony default export */ var _unused_webpack_default_export = (Component.exports);


/***/ }),
/* 256 */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),
/* 257 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_assets_js_Lib__ = __webpack_require__(3);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_assets_js_api__ = __webpack_require__(4);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2_vux_src_components_tab_tab_vue__ = __webpack_require__(21);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_3_vux_src_components_tab_tab_item_vue__ = __webpack_require__(22);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_4_vux_src_components_swiper_swiper_vue__ = __webpack_require__(8);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_5_vux_src_components_swiper_swiper_item_vue__ = __webpack_require__(15);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_6_vux_src_components_x_dialog_index_vue__ = __webpack_require__(68);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_7_vux_src_components_grid_grid_vue__ = __webpack_require__(1);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_8_vux_src_components_grid_grid_item_vue__ = __webpack_require__(2);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_9_vux_src_directives_transfer_dom_index_js__ = __webpack_require__(6);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_10_vux_src_components_msg_index_vue__ = __webpack_require__(116);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_11_vux_src_components_x_button_index_vue__ = __webpack_require__(12);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_12_vux_src_components_view_box_index_vue__ = __webpack_require__(7);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_13_components__ = __webpack_require__(34);
















/* unused harmony default export */ var _unused_webpack_default_export = ({
  directives: {
    TransferDom: __WEBPACK_IMPORTED_MODULE_9_vux_src_directives_transfer_dom_index_js__["a" /* default */]
  },
  model: {
    prop: 'show',
    event: 'change'
  },
  props: {

    show: {
      type: Boolean,
      default: false
    },
    opensItems: {
      type: Array,
      default: false
    },
    errorTip: {
      type: String,
      default: ''
    },
    registercode: {
      type: Boolean,
      default: false
    }

  },
  data: function data() {
    return {
      openDialogStyle: {
        position: 'fixed',
        width: '100%',
        maxWidth: '100%',
        borderRadius: '0',
        height: '100%',
        backgroundColor: 'rgba(36, 35, 37, 0.8)'
      },
      index: 0,
      icon: 'warn'
    };
  },
  components: {
    Tab: __WEBPACK_IMPORTED_MODULE_2_vux_src_components_tab_tab_vue__["a" /* default */], TabItem: __WEBPACK_IMPORTED_MODULE_3_vux_src_components_tab_tab_item_vue__["a" /* default */], Swiper: __WEBPACK_IMPORTED_MODULE_4_vux_src_components_swiper_swiper_vue__["a" /* default */], SwiperItem: __WEBPACK_IMPORTED_MODULE_5_vux_src_components_swiper_swiper_item_vue__["a" /* default */], XDialog: __WEBPACK_IMPORTED_MODULE_6_vux_src_components_x_dialog_index_vue__["a" /* default */], Grid: __WEBPACK_IMPORTED_MODULE_7_vux_src_components_grid_grid_vue__["a" /* default */], GridItem: __WEBPACK_IMPORTED_MODULE_8_vux_src_components_grid_grid_item_vue__["a" /* default */], Msg: __WEBPACK_IMPORTED_MODULE_10_vux_src_components_msg_index_vue__["a" /* default */], LnButton: __WEBPACK_IMPORTED_MODULE_13_components__["b" /* LnButton */], XButton: __WEBPACK_IMPORTED_MODULE_11_vux_src_components_x_button_index_vue__["a" /* default */], ViewBox: __WEBPACK_IMPORTED_MODULE_12_vux_src_components_view_box_index_vue__["a" /* default */]
  },

  mounted: function mounted() {
    var self = this;
  },


  methods: {
    open: function open(id) {
      var _this = this;

      var self = this;
      var doorid = id;
      self.show = false;
      self.$vux.loading.show("正在请求开锁");
      __WEBPACK_IMPORTED_MODULE_1_assets_js_api__["a" /* default */].open(doorid).then(function (res) {
        self.$vux.loading.hide();

        if (res.err_code == 0) {

          _this.$vux.toast.show({
            text: res.data.content,
            type: 'warn',
            onShow: function onShow() {

              window.location.href = res.data.url;
            },
            onHide: function onHide() {

              window.location.href = res.data.url;
            }
          });
        }
      });
    },
    hide: function hide() {
      this.$emit('update:show', false);
      this.$emit('change', false);
    },
    url: function url(query, action) {
      return __WEBPACK_IMPORTED_MODULE_0_assets_js_Lib__["a" /* default */].M.url(query, action);
    }
  },

  computed: {
    lockHeight: function lockHeight() {

      return document.documentElement.clientHeight - 100;
    }
  },
  watch: {
    show: function show(val) {
      this.$emit('update:show', val);
      this.$emit(val ? 'on-show' : 'on-hide');
    }
  }
});

/***/ }),
/* 258 */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),
/* 259 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__libs_router__ = __webpack_require__(10);




/* unused harmony default export */ var _unused_webpack_default_export = ({
  name: 'msg',
  props: ['icon', 'title', 'description', 'buttons'],
  methods: {
    onClick: function onClick(handler, link) {
      typeof handler === 'function' && handler();
      link && Object(__WEBPACK_IMPORTED_MODULE_0__libs_router__["b" /* go */])(link, this.$router);
    }
  }
});

/***/ }),
/* 260 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__libs_router__ = __webpack_require__(10);




/* harmony default export */ __webpack_exports__["a"] = ({
  name: 'msg',
  props: ['icon', 'title', 'description', 'buttons'],
  methods: {
    onClick: function onClick(handler, link) {
      typeof handler === 'function' && handler();
      link && Object(__WEBPACK_IMPORTED_MODULE_0__libs_router__["b" /* go */])(link, this.$router);
    }
  }
});

/***/ }),
/* 261 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{staticClass:"weui-msg"},[_c('div',{staticClass:"weui-msg__icon-area"},[_c('i',{staticClass:"weui-icon_msg",class:("weui-icon-" + (_vm.icon || 'success'))})]),_vm._v(" "),_c('div',{staticClass:"weui-msg__text-area"},[_c('h2',{staticClass:"weui-msg__title",domProps:{"innerHTML":_vm._s(_vm.title)}}),_vm._v(" "),_c('p',{staticClass:"weui-msg__desc"},[_vm._t("description")],2),_vm._v(" "),(_vm.description)?_c('p',{staticClass:"weui-msg__desc",domProps:{"innerHTML":_vm._s(_vm.description)}}):_vm._e()]),_vm._v(" "),_c('div',{staticClass:"weui-msg__opr-area"},[_c('p',{staticClass:"weui-btn-area"},[_vm._t("buttons",_vm._l((_vm.buttons),function(button){return _c('a',{staticClass:"weui-btn",class:("weui-btn_" + (button.type)),attrs:{"href":"javascript:;"},on:{"click":function($event){_vm.onClick(button.onClick, button.link)}}},[_vm._v(_vm._s(button.text))])}))],2)])])}
var staticRenderFns = []
var esExports = { render: render, staticRenderFns: staticRenderFns }
/* harmony default export */ __webpack_exports__["a"] = (esExports);

/***/ }),
/* 262 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_assets_js_Lib__ = __webpack_require__(3);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_assets_js_api__ = __webpack_require__(4);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2_vux_src_components_tab_tab_vue__ = __webpack_require__(21);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_3_vux_src_components_tab_tab_item_vue__ = __webpack_require__(22);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_4_vux_src_components_swiper_swiper_vue__ = __webpack_require__(8);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_5_vux_src_components_swiper_swiper_item_vue__ = __webpack_require__(15);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_6_vux_src_components_x_dialog_index_vue__ = __webpack_require__(68);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_7_vux_src_components_grid_grid_vue__ = __webpack_require__(1);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_8_vux_src_components_grid_grid_item_vue__ = __webpack_require__(2);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_9_vux_src_directives_transfer_dom_index_js__ = __webpack_require__(6);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_10_vux_src_components_msg_index_vue__ = __webpack_require__(116);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_11_vux_src_components_x_button_index_vue__ = __webpack_require__(12);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_12_vux_src_components_view_box_index_vue__ = __webpack_require__(7);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_13_components__ = __webpack_require__(34);
















/* harmony default export */ __webpack_exports__["a"] = ({
  directives: {
    TransferDom: __WEBPACK_IMPORTED_MODULE_9_vux_src_directives_transfer_dom_index_js__["a" /* default */]
  },
  model: {
    prop: 'show',
    event: 'change'
  },
  props: {

    show: {
      type: Boolean,
      default: false
    },
    opensItems: {
      type: Array,
      default: false
    },
    errorTip: {
      type: String,
      default: ''
    },
    registercode: {
      type: Boolean,
      default: false
    }

  },
  data: function data() {
    return {
      openDialogStyle: {
        position: 'fixed',
        width: '100%',
        maxWidth: '100%',
        borderRadius: '0',
        height: '100%',
        backgroundColor: 'rgba(36, 35, 37, 0.8)'
      },
      index: 0,
      icon: 'warn'
    };
  },
  components: {
    Tab: __WEBPACK_IMPORTED_MODULE_2_vux_src_components_tab_tab_vue__["a" /* default */], TabItem: __WEBPACK_IMPORTED_MODULE_3_vux_src_components_tab_tab_item_vue__["a" /* default */], Swiper: __WEBPACK_IMPORTED_MODULE_4_vux_src_components_swiper_swiper_vue__["a" /* default */], SwiperItem: __WEBPACK_IMPORTED_MODULE_5_vux_src_components_swiper_swiper_item_vue__["a" /* default */], XDialog: __WEBPACK_IMPORTED_MODULE_6_vux_src_components_x_dialog_index_vue__["a" /* default */], Grid: __WEBPACK_IMPORTED_MODULE_7_vux_src_components_grid_grid_vue__["a" /* default */], GridItem: __WEBPACK_IMPORTED_MODULE_8_vux_src_components_grid_grid_item_vue__["a" /* default */], Msg: __WEBPACK_IMPORTED_MODULE_10_vux_src_components_msg_index_vue__["a" /* default */], LnButton: __WEBPACK_IMPORTED_MODULE_13_components__["b" /* LnButton */], XButton: __WEBPACK_IMPORTED_MODULE_11_vux_src_components_x_button_index_vue__["a" /* default */], ViewBox: __WEBPACK_IMPORTED_MODULE_12_vux_src_components_view_box_index_vue__["a" /* default */]
  },

  mounted: function mounted() {
    var self = this;
  },


  methods: {
    open: function open(id) {
      var _this = this;

      var self = this;
      var doorid = id;
      self.show = false;
      self.$vux.loading.show("正在请求开锁");
      __WEBPACK_IMPORTED_MODULE_1_assets_js_api__["a" /* default */].open(doorid).then(function (res) {
        self.$vux.loading.hide();

        if (res.err_code == 0) {

          _this.$vux.toast.show({
            text: res.data.content,
            type: 'warn',
            onShow: function onShow() {

              window.location.href = res.data.url;
            },
            onHide: function onHide() {

              window.location.href = res.data.url;
            }
          });
        }
      });
    },
    hide: function hide() {
      this.$emit('update:show', false);
      this.$emit('change', false);
    },
    url: function url(query, action) {
      return __WEBPACK_IMPORTED_MODULE_0_assets_js_Lib__["a" /* default */].M.url(query, action);
    }
  },

  computed: {
    lockHeight: function lockHeight() {

      return document.documentElement.clientHeight - 100;
    }
  },
  watch: {
    show: function show(val) {
      this.$emit('update:show', val);
      this.$emit(val ? 'on-show' : 'on-hide');
    }
  }
});

/***/ }),
/* 263 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{directives:[{name:"show",rawName:"v-show",value:(_vm.show),expression:"show"},{name:"transfer-dom",rawName:"v-transfer-dom"}],staticClass:"look-box",staticStyle:{"height":"100%"}},[_c('x-dialog',{attrs:{"dialog-style":_vm.openDialogStyle},model:{value:(_vm.show),callback:function ($$v) {_vm.show=$$v},expression:"show"}},[_c('view-box',{ref:"viewBox",staticStyle:{"height":"auto"},attrs:{"body-padding-bottom":"0px"}},[(_vm.errorTip)?_c('div',{staticStyle:{"margin-top":"100px"}},[_c('msg',{staticStyle:{"color":"white"},attrs:{"title":_vm.errorTip,"icon":_vm.icon}}),_vm._v(" "),(_vm.registercode)?_c('div',[_c('x-button',{attrs:{"type":"primary","link":_vm.url('register/guide',{p:1}),"mini":""}},[_vm._v("注册")])],1):_vm._e()],1):_vm._e(),_vm._v(" "),_c('div',{attrs:{"slot":"header"},slot:"header"},[(_vm.opensItems.length>0)?_c('tab',{directives:[{name:"else",rawName:"v-else"}],attrs:{"line-width":2,"active-color":"#F7624B"},model:{value:(_vm.index),callback:function ($$v) {_vm.index=$$v},expression:"index"}},_vm._l((_vm.opensItems),function(item,index){return _c('tab-item',{key:index,staticClass:"vux-center"},[_vm._v(_vm._s(item.title))])})):_vm._e()],1),_vm._v(" "),(_vm.opensItems.length>0)?_c('swiper',{attrs:{"height":_vm.lockHeight + 'px',"dots-position":"center","show-dots":false},model:{value:(_vm.index),callback:function ($$v) {_vm.index=$$v},expression:"index"}},_vm._l((_vm.opensItems),function(row,index){return _c('swiper-item',{key:index,staticStyle:{"overflow-y":"scroll"}},[_c('div',{staticClass:"locklist"},[_c('grid',{attrs:{"cols":3}},_vm._l((row.items),function(item,index){return (item.id)?_c('grid-item',{key:index,nativeOn:{"click":function($event){_vm.open(item.id)}}},[_c('img',{attrs:{"slot":"icon","src":item.thumb,"alt":"开门"},slot:"icon"}),_vm._v(" "),_c('span',{staticClass:"locklist__title"},[_vm._v(_vm._s(item.title)+" "+_vm._s(item.unit))])]):_vm._e()}))],1)])})):_vm._e(),_vm._v(" "),_c('div',{attrs:{"slot":"bottom"},on:{"click":_vm.hide},slot:"bottom"},[_c('span',{staticClass:"vux-close"})])],1)],1)],1)}
var staticRenderFns = []
var esExports = { render: render, staticRenderFns: staticRenderFns }
/* harmony default export */ __webpack_exports__["a"] = (esExports);

/***/ }),
/* 264 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vux_loader_src_script_loader_js_node_modules_vux_loader_src_script_loader_js_node_modules_vue_loader_lib_selector_type_script_index_0_index_vue__ = __webpack_require__(266);
/* unused harmony namespace reexport */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__babel_loader_node_modules_vux_loader_src_script_loader_js_node_modules_vue_loader_lib_selector_type_script_index_0_index_vue__ = __webpack_require__(267);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__node_modules_vue_loader_lib_template_compiler_index_id_data_v_6dbbae2e_hasScoped_false_buble_transforms_node_modules_vux_loader_src_before_template_compiler_loader_js_node_modules_vux_loader_src_template_loader_js_node_modules_vue_loader_lib_selector_type_template_index_0_index_vue__ = __webpack_require__(268);
function injectStyle (ssrContext) {
  __webpack_require__(265)
}
var normalizeComponent = __webpack_require__(0)
/* script */


/* template */

/* template functional */
var __vue_template_functional__ = false
/* styles */
var __vue_styles__ = injectStyle
/* scopeId */
var __vue_scopeId__ = null
/* moduleIdentifier (server only) */
var __vue_module_identifier__ = null
var Component = normalizeComponent(
  __WEBPACK_IMPORTED_MODULE_1__babel_loader_node_modules_vux_loader_src_script_loader_js_node_modules_vue_loader_lib_selector_type_script_index_0_index_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_2__node_modules_vue_loader_lib_template_compiler_index_id_data_v_6dbbae2e_hasScoped_false_buble_transforms_node_modules_vux_loader_src_before_template_compiler_loader_js_node_modules_vux_loader_src_template_loader_js_node_modules_vue_loader_lib_selector_type_template_index_0_index_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)

/* harmony default export */ __webpack_exports__["a"] = (Component.exports);


/***/ }),
/* 265 */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),
/* 266 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";


/* unused harmony default export */ var _unused_webpack_default_export = ({
    props: {
        text: {
            type: String,
            default: ''
        }
    }
});

/***/ }),
/* 267 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";


/* harmony default export */ __webpack_exports__["a"] = ({
    props: {
        text: {
            type: String,
            default: ''
        }
    }
});

/***/ }),
/* 268 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('label',{staticClass:"weui-agree"},[_c('div',{staticClass:"weui-agree__text"},[_vm._t("default")],2)])}
var staticRenderFns = []
var esExports = { render: render, staticRenderFns: staticRenderFns }
/* harmony default export */ __webpack_exports__["a"] = (esExports);

/***/ }),
/* 269 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vux_loader_src_script_loader_js_node_modules_vux_loader_src_script_loader_js_node_modules_vue_loader_lib_selector_type_script_index_0_index_vue__ = __webpack_require__(271);
/* unused harmony namespace reexport */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__babel_loader_node_modules_vux_loader_src_script_loader_js_node_modules_vue_loader_lib_selector_type_script_index_0_index_vue__ = __webpack_require__(272);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__node_modules_vue_loader_lib_template_compiler_index_id_data_v_11408b94_hasScoped_false_buble_transforms_node_modules_vux_loader_src_before_template_compiler_loader_js_node_modules_vux_loader_src_template_loader_js_node_modules_vue_loader_lib_selector_type_template_index_0_index_vue__ = __webpack_require__(273);
function injectStyle (ssrContext) {
  __webpack_require__(270)
}
var normalizeComponent = __webpack_require__(0)
/* script */


/* template */

/* template functional */
var __vue_template_functional__ = false
/* styles */
var __vue_styles__ = injectStyle
/* scopeId */
var __vue_scopeId__ = null
/* moduleIdentifier (server only) */
var __vue_module_identifier__ = null
var Component = normalizeComponent(
  __WEBPACK_IMPORTED_MODULE_1__babel_loader_node_modules_vux_loader_src_script_loader_js_node_modules_vue_loader_lib_selector_type_script_index_0_index_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_2__node_modules_vue_loader_lib_template_compiler_index_id_data_v_11408b94_hasScoped_false_buble_transforms_node_modules_vux_loader_src_before_template_compiler_loader_js_node_modules_vux_loader_src_template_loader_js_node_modules_vue_loader_lib_selector_type_template_index_0_index_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)

/* harmony default export */ __webpack_exports__["a"] = (Component.exports);


/***/ }),
/* 270 */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),
/* 271 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_vux_src_directives_transfer_dom_index_js__ = __webpack_require__(6);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_assets_js_api__ = __webpack_require__(4);




/* unused harmony default export */ var _unused_webpack_default_export = ({
  directives: {
    TransferDom: __WEBPACK_IMPORTED_MODULE_0_vux_src_directives_transfer_dom_index_js__["a" /* default */]
  },
  props: {
    title: String,
    desc: String,
    labelWidth: String,
    items: {
      type: Array,
      default: function _default() {
        return [];
      }
    },
    count: {
      type: Number,
      default: 9
    },
    lineHide: Boolean
  },
  data: function data() {
    return {
      index: 0,
      localIds: [],
      serverIds: [],
      gallery: ''
    };
  },


  methods: {
    handlePicture: function handlePicture() {
      var self = this;
      var picNum = self.count - self.items.length;
      wx.chooseImage({
        count: picNum > 0 ? picNum : 0,
        sizeType: ['compressed'],
        sourceType: ['album', 'camera'],
        success: function success(res) {
          for (var n = 0; n < res.localIds.length; n++) {
            if (window.__wxjs_is_wkwebview) {
              wx.getLocalImgData({
                localId: res.localIds[n],
                success: function success(r) {
                  self.localIds.push(r.localData);
                },
                fail: function fail() {
                  var localIds = self.localIds;
                  localIds.push(res.localIds[n]);
                  self.localIds = localIds.slice(-self.count);
                }
              });
            } else {
              self.localIds.push(res.localIds[n]);
            }
          }
          var index = 0;
          self.uploadPhotos(res.localIds, index);
        }
      });
    },
    uploadPhotos: function uploadPhotos(localIds, index) {
      var self = this;
      self.$vux.loading.show({ text: '正在上传' });
      wx.uploadImage({
        localId: localIds[index],
        isShowProgressTips: 0,
        success: function success(res) {
          __WEBPACK_IMPORTED_MODULE_1_assets_js_api__["a" /* default */].upload(res.serverId).then(function (obj) {
            index++;
            if (index < localIds.length) {
              self.uploadPhotos(localIds, index);
            } else {
              self.$vux.loading.hide();
            }
            self.items.push(obj.data.imgUrl);
            var serverId = self.serverIds.concat([res.serverId]);
            self.serverIds = serverId.slice(-self.count);
          });
        }
      });
    },
    onGalleryShow: function onGalleryShow(index) {
      this.index = index;
      this.gallery = this.items[index];
      this.$emit('on-show', index);
    },
    deletePhoto: function deletePhoto(index) {
      this.items.splice(index, 1);
      this.gallery = '';
      this.$emit('on-delete', index);
    }
  },
  computed: {
    labelStyles: function labelStyles() {
      return {
        width: this.labelWidthComputed || this.$parent.labelWidth || this.labelWidthComputed,
        textAlign: this.$parent.labelAlign,
        marginRight: this.$parent.labelMarginRight
      };
    },
    labelWidthComputed: function labelWidthComputed() {}
  }
});

/***/ }),
/* 272 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_vux_src_directives_transfer_dom_index_js__ = __webpack_require__(6);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_assets_js_api__ = __webpack_require__(4);




/* harmony default export */ __webpack_exports__["a"] = ({
  directives: {
    TransferDom: __WEBPACK_IMPORTED_MODULE_0_vux_src_directives_transfer_dom_index_js__["a" /* default */]
  },
  props: {
    title: String,
    desc: String,
    labelWidth: String,
    items: {
      type: Array,
      default: function _default() {
        return [];
      }
    },
    count: {
      type: Number,
      default: 9
    },
    lineHide: Boolean
  },
  data: function data() {
    return {
      index: 0,
      localIds: [],
      serverIds: [],
      gallery: ''
    };
  },


  methods: {
    handlePicture: function handlePicture() {
      var self = this;
      var picNum = self.count - self.items.length;
      wx.chooseImage({
        count: picNum > 0 ? picNum : 0,
        sizeType: ['compressed'],
        sourceType: ['album', 'camera'],
        success: function success(res) {
          for (var n = 0; n < res.localIds.length; n++) {
            if (window.__wxjs_is_wkwebview) {
              wx.getLocalImgData({
                localId: res.localIds[n],
                success: function success(r) {
                  self.localIds.push(r.localData);
                },
                fail: function fail() {
                  var localIds = self.localIds;
                  localIds.push(res.localIds[n]);
                  self.localIds = localIds.slice(-self.count);
                }
              });
            } else {
              self.localIds.push(res.localIds[n]);
            }
          }
          var index = 0;
          self.uploadPhotos(res.localIds, index);
        }
      });
    },
    uploadPhotos: function uploadPhotos(localIds, index) {
      var self = this;
      self.$vux.loading.show({ text: '正在上传' });
      wx.uploadImage({
        localId: localIds[index],
        isShowProgressTips: 0,
        success: function success(res) {
          __WEBPACK_IMPORTED_MODULE_1_assets_js_api__["a" /* default */].upload(res.serverId).then(function (obj) {
            index++;
            if (index < localIds.length) {
              self.uploadPhotos(localIds, index);
            } else {
              self.$vux.loading.hide();
            }
            self.items.push(obj.data.imgUrl);
            var serverId = self.serverIds.concat([res.serverId]);
            self.serverIds = serverId.slice(-self.count);
          });
        }
      });
    },
    onGalleryShow: function onGalleryShow(index) {
      this.index = index;
      this.gallery = this.items[index];
      this.$emit('on-show', index);
    },
    deletePhoto: function deletePhoto(index) {
      this.items.splice(index, 1);
      this.gallery = '';
      this.$emit('on-delete', index);
    }
  },
  computed: {
    labelStyles: function labelStyles() {
      return {
        width: this.labelWidthComputed || this.$parent.labelWidth || this.labelWidthComputed,
        textAlign: this.$parent.labelAlign,
        marginRight: this.$parent.labelMarginRight
      };
    },
    labelWidthComputed: function labelWidthComputed() {}
  }
});

/***/ }),
/* 273 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{staticClass:"weui-cell",class:{'line-hide':_vm.lineHide}},[_c('div',{staticClass:"weui-cell__bd"},[_c('div',{staticClass:"weui-uploader"},[(_vm.title || _vm.desc)?_c('div',{staticClass:"weui-uploader__hd"},[(_vm.title)?_c('p',{staticClass:"weui-uploader__title"},[_c('label',{staticClass:"weui-label",style:({width: _vm.labelWidth || _vm.$parent.labelWidth || _vm.labelWidthComputed, textAlign: _vm.$parent.labelAlign, marginRight: _vm.$parent.labelMarginRight}),domProps:{"innerHTML":_vm._s(_vm.title)}})]):_vm._e(),_vm._v(" "),(_vm.desc)?_c('div',{staticClass:"weui-uploader__info",domProps:{"innerHTML":_vm._s(_vm.desc)},on:{"click":function($event){_vm.$emit('on-desc')}}}):_vm._e()]):_vm._e(),_vm._v(" "),_c('div',{staticClass:"weui-uploader__bd"},[_c('ul',{staticClass:"weui-uploader__files",attrs:{"id":"uploaderFiles"}},_vm._l((_vm.items),function(photo,index){return _c('li',{staticClass:"weui-uploader__file",style:({backgroundImage: 'url('+photo+')'}),on:{"click":function($event){_vm.onGalleryShow(index)}}})})),_vm._v(" "),_c('div',{directives:[{name:"show",rawName:"v-show",value:(_vm.items.length < _vm.count),expression:"items.length < count"}],staticClass:"weui-uploader__input-box"},[_c('div',{staticClass:"weui-uploader__input",on:{"click":_vm.handlePicture}})])])]),_vm._v(" "),_c('div',{directives:[{name:"transfer-dom",rawName:"v-transfer-dom"}]},[_c('div',{staticClass:"weui-gallery",class:{'show':_vm.gallery}},[_c('span',{staticClass:"weui-gallery__img",style:('backgroundImage: url('+_vm.gallery+')'),on:{"click":function($event){_vm.gallery=''}}}),_vm._v(" "),_c('div',{staticClass:"weui-gallery__opr",on:{"click":function($event){_vm.deletePhoto(_vm.index)}}},[_vm._m(0)])])])])])}
var staticRenderFns = [function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('a',{staticClass:"weui-gallery__del",attrs:{"href":"javascript:"}},[_c('i',{staticClass:"weui-icon-delete weui-icon_gallery-delete"})])}]
var esExports = { render: render, staticRenderFns: staticRenderFns }
/* harmony default export */ __webpack_exports__["a"] = (esExports);

/***/ }),
/* 274 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vux_loader_src_script_loader_js_node_modules_vux_loader_src_script_loader_js_node_modules_vue_loader_lib_selector_type_script_index_0_index_vue__ = __webpack_require__(276);
/* unused harmony namespace reexport */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__babel_loader_node_modules_vux_loader_src_script_loader_js_node_modules_vue_loader_lib_selector_type_script_index_0_index_vue__ = __webpack_require__(277);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__node_modules_vue_loader_lib_template_compiler_index_id_data_v_25bd1390_hasScoped_true_buble_transforms_node_modules_vux_loader_src_before_template_compiler_loader_js_node_modules_vux_loader_src_template_loader_js_node_modules_vue_loader_lib_selector_type_template_index_0_index_vue__ = __webpack_require__(278);
function injectStyle (ssrContext) {
  __webpack_require__(275)
}
var normalizeComponent = __webpack_require__(0)
/* script */


/* template */

/* template functional */
var __vue_template_functional__ = false
/* styles */
var __vue_styles__ = injectStyle
/* scopeId */
var __vue_scopeId__ = "data-v-25bd1390"
/* moduleIdentifier (server only) */
var __vue_module_identifier__ = null
var Component = normalizeComponent(
  __WEBPACK_IMPORTED_MODULE_1__babel_loader_node_modules_vux_loader_src_script_loader_js_node_modules_vue_loader_lib_selector_type_script_index_0_index_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_2__node_modules_vue_loader_lib_template_compiler_index_id_data_v_25bd1390_hasScoped_true_buble_transforms_node_modules_vux_loader_src_before_template_compiler_loader_js_node_modules_vux_loader_src_template_loader_js_node_modules_vue_loader_lib_selector_type_template_index_0_index_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)

/* harmony default export */ __webpack_exports__["a"] = (Component.exports);


/***/ }),
/* 275 */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),
/* 276 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";


/* unused harmony default export */ var _unused_webpack_default_export = ({
    props: {
        items: {
            type: Array,
            default: []
        }
    },
    components: {}
});

/***/ }),
/* 277 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";


/* harmony default export */ __webpack_exports__["a"] = ({
    props: {
        items: {
            type: Array,
            default: []
        }
    },
    components: {}
});

/***/ }),
/* 278 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{staticClass:"weui-panel weui-panel_access"},[_c('div',{staticClass:"weui-panel__bd"},_vm._l((_vm.items),function(item,index){return _c('a',{key:index,staticClass:"weui-media-box weui-media-box_appmsg",attrs:{"href":item.url}},[(item.src)?_c('div',{staticClass:"weui-media-box__hd"},[_c('img',{staticClass:"weui-media-box__thumb",attrs:{"src":item.src,"alt":""}})]):_vm._e(),_vm._v(" "),_c('div',{staticClass:"weui-media-box__bd"},[_c('p',{staticClass:"weui-media-box__title",domProps:{"innerHTML":_vm._s(item.title)}}),_vm._v(" "),_c('p',{staticClass:"weui-media-box__desc",staticStyle:{"-webkit-box-orient":"vertical","margin-top":"10px"},domProps:{"innerHTML":_vm._s(item.desc)}})]),_vm._v(" "),_c('span',{staticClass:"weui-cell__ft"},[(item.status ==2)?_c('span',{staticClass:"vertical-middle",staticStyle:{"color":"red","font-size":"14px"}},[_vm._v("未处理  ")]):_vm._e(),_vm._v(" "),(item.status ==1)?_c('span',{staticClass:"vertical-middle",staticStyle:{"color":"#3CC51E","font-size":"14px"}},[_vm._v("已处理  ")]):_vm._e(),_vm._v(" "),(item.status ==3)?_c('span',{staticClass:"vertical-middle",staticStyle:{"color":"#459DD6","font-size":"14px"}},[_vm._v("处理中  ")]):_vm._e()])])}))])}
var staticRenderFns = []
var esExports = { render: render, staticRenderFns: staticRenderFns }
/* harmony default export */ __webpack_exports__["a"] = (esExports);

/***/ }),
/* 279 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vux_loader_src_script_loader_js_node_modules_vux_loader_src_script_loader_js_node_modules_vue_loader_lib_selector_type_script_index_0_index_vue__ = __webpack_require__(281);
/* unused harmony reexport namespace */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__babel_loader_node_modules_vux_loader_src_script_loader_js_node_modules_vue_loader_lib_selector_type_script_index_0_index_vue__ = __webpack_require__(282);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__node_modules_vue_loader_lib_template_compiler_index_id_data_v_ec45eddc_hasScoped_true_buble_transforms_node_modules_vux_loader_src_before_template_compiler_loader_js_node_modules_vux_loader_src_template_loader_js_node_modules_vue_loader_lib_selector_type_template_index_0_index_vue__ = __webpack_require__(283);
function injectStyle (ssrContext) {
  __webpack_require__(280)
}
var normalizeComponent = __webpack_require__(0)
/* script */


/* template */

/* template functional */
var __vue_template_functional__ = false
/* styles */
var __vue_styles__ = injectStyle
/* scopeId */
var __vue_scopeId__ = "data-v-ec45eddc"
/* moduleIdentifier (server only) */
var __vue_module_identifier__ = null
var Component = normalizeComponent(
  __WEBPACK_IMPORTED_MODULE_1__babel_loader_node_modules_vux_loader_src_script_loader_js_node_modules_vue_loader_lib_selector_type_script_index_0_index_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_2__node_modules_vue_loader_lib_template_compiler_index_id_data_v_ec45eddc_hasScoped_true_buble_transforms_node_modules_vux_loader_src_before_template_compiler_loader_js_node_modules_vux_loader_src_template_loader_js_node_modules_vue_loader_lib_selector_type_template_index_0_index_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)

/* unused harmony default export */ var _unused_webpack_default_export = (Component.exports);


/***/ }),
/* 280 */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),
/* 281 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_vux_src_components_x_header_index_vue__ = __webpack_require__(9);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_assets_js_Lib__ = __webpack_require__(3);





/* unused harmony default export */ var _unused_webpack_default_export = ({
  props: {
    title: {
      type: String,
      default: ''
    },
    status: {
      type: Number,
      default: 1
    }
  },
  components: {
    XHeader: __WEBPACK_IMPORTED_MODULE_0_vux_src_components_x_header_index_vue__["a" /* default */]
  },
  methods: {
    url: function url() {
      return __WEBPACK_IMPORTED_MODULE_1_assets_js_Lib__["a" /* default */].M.url('home');
    }
  }
});

/***/ }),
/* 282 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_vux_src_components_x_header_index_vue__ = __webpack_require__(9);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_assets_js_Lib__ = __webpack_require__(3);





/* harmony default export */ __webpack_exports__["a"] = ({
  props: {
    title: {
      type: String,
      default: ''
    },
    status: {
      type: Number,
      default: 1
    }
  },
  components: {
    XHeader: __WEBPACK_IMPORTED_MODULE_0_vux_src_components_x_header_index_vue__["a" /* default */]
  },
  methods: {
    url: function url() {
      return __WEBPACK_IMPORTED_MODULE_1_assets_js_Lib__["a" /* default */].M.url('home');
    }
  }
});

/***/ }),
/* 283 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return (_vm.status)?_c('x-header',{staticStyle:{"background-color":"#F7624B","position":"absolute","width":"100%","left":"0","top":"0"},attrs:{"title":_vm.title}},[_c('a',{staticStyle:{"color":"#FFFFFF"},attrs:{"slot":"right","href":_vm.url()},slot:"right"},[_vm._v("首页")])]):_vm._e()}
var staticRenderFns = []
var esExports = { render: render, staticRenderFns: staticRenderFns }
/* harmony default export */ __webpack_exports__["a"] = (esExports);

/***/ }),
/* 284 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vux_loader_src_script_loader_js_node_modules_vux_loader_src_script_loader_js_node_modules_vue_loader_lib_selector_type_script_index_0_index_vue__ = __webpack_require__(286);
/* unused harmony namespace reexport */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__babel_loader_node_modules_vux_loader_src_script_loader_js_node_modules_vue_loader_lib_selector_type_script_index_0_index_vue__ = __webpack_require__(287);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__node_modules_vue_loader_lib_template_compiler_index_id_data_v_09684a9e_hasScoped_true_buble_transforms_node_modules_vux_loader_src_before_template_compiler_loader_js_node_modules_vux_loader_src_template_loader_js_node_modules_vue_loader_lib_selector_type_template_index_0_index_vue__ = __webpack_require__(288);
function injectStyle (ssrContext) {
  __webpack_require__(285)
}
var normalizeComponent = __webpack_require__(0)
/* script */


/* template */

/* template functional */
var __vue_template_functional__ = false
/* styles */
var __vue_styles__ = injectStyle
/* scopeId */
var __vue_scopeId__ = "data-v-09684a9e"
/* moduleIdentifier (server only) */
var __vue_module_identifier__ = null
var Component = normalizeComponent(
  __WEBPACK_IMPORTED_MODULE_1__babel_loader_node_modules_vux_loader_src_script_loader_js_node_modules_vue_loader_lib_selector_type_script_index_0_index_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_2__node_modules_vue_loader_lib_template_compiler_index_id_data_v_09684a9e_hasScoped_true_buble_transforms_node_modules_vux_loader_src_before_template_compiler_loader_js_node_modules_vux_loader_src_template_loader_js_node_modules_vue_loader_lib_selector_type_template_index_0_index_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)

/* harmony default export */ __webpack_exports__["a"] = (Component.exports);


/***/ }),
/* 285 */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),
/* 286 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_vux_src_components_x_button_index_vue__ = __webpack_require__(12);



/* unused harmony default export */ var _unused_webpack_default_export = ({
  props: {
    title: String,
    default: '提交'
  },
  components: {
    XButton: __WEBPACK_IMPORTED_MODULE_0_vux_src_components_x_button_index_vue__["a" /* default */]
  }
});

/***/ }),
/* 287 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_vux_src_components_x_button_index_vue__ = __webpack_require__(12);



/* harmony default export */ __webpack_exports__["a"] = ({
  props: {
    title: String,
    default: '提交'
  },
  components: {
    XButton: __WEBPACK_IMPORTED_MODULE_0_vux_src_components_x_button_index_vue__["a" /* default */]
  }
});

/***/ }),
/* 288 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('x-button',{staticStyle:{"background-color":"#F7624B","color":"#ffffff"}},[_vm._v(_vm._s(_vm.title))])}
var staticRenderFns = []
var esExports = { render: render, staticRenderFns: staticRenderFns }
/* harmony default export */ __webpack_exports__["a"] = (esExports);

/***/ }),
/* 289 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vux_loader_src_script_loader_js_node_modules_vux_loader_src_script_loader_js_node_modules_vue_loader_lib_selector_type_script_index_0_index_vue__ = __webpack_require__(291);
/* unused harmony reexport namespace */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__babel_loader_node_modules_vux_loader_src_script_loader_js_node_modules_vue_loader_lib_selector_type_script_index_0_index_vue__ = __webpack_require__(292);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__node_modules_vue_loader_lib_template_compiler_index_id_data_v_031a4902_hasScoped_true_buble_transforms_node_modules_vux_loader_src_before_template_compiler_loader_js_node_modules_vux_loader_src_template_loader_js_node_modules_vue_loader_lib_selector_type_template_index_0_index_vue__ = __webpack_require__(293);
function injectStyle (ssrContext) {
  __webpack_require__(290)
}
var normalizeComponent = __webpack_require__(0)
/* script */


/* template */

/* template functional */
var __vue_template_functional__ = false
/* styles */
var __vue_styles__ = injectStyle
/* scopeId */
var __vue_scopeId__ = "data-v-031a4902"
/* moduleIdentifier (server only) */
var __vue_module_identifier__ = null
var Component = normalizeComponent(
  __WEBPACK_IMPORTED_MODULE_1__babel_loader_node_modules_vux_loader_src_script_loader_js_node_modules_vue_loader_lib_selector_type_script_index_0_index_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_2__node_modules_vue_loader_lib_template_compiler_index_id_data_v_031a4902_hasScoped_true_buble_transforms_node_modules_vux_loader_src_before_template_compiler_loader_js_node_modules_vux_loader_src_template_loader_js_node_modules_vue_loader_lib_selector_type_template_index_0_index_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)

/* unused harmony default export */ var _unused_webpack_default_export = (Component.exports);


/***/ }),
/* 290 */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),
/* 291 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_vux_src_components_tabbar_tabbar_vue__ = __webpack_require__(19);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_vux_src_components_tabbar_tabbar_item_vue__ = __webpack_require__(25);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2_vux_src_components_sticky_index_vue__ = __webpack_require__(35);





/* unused harmony default export */ var _unused_webpack_default_export = ({
  props: {
    title: String,
    default: '提交'
  },
  components: {
    Tabbar: __WEBPACK_IMPORTED_MODULE_0_vux_src_components_tabbar_tabbar_vue__["a" /* default */], TabbarItem: __WEBPACK_IMPORTED_MODULE_1_vux_src_components_tabbar_tabbar_item_vue__["a" /* default */], Sticky: __WEBPACK_IMPORTED_MODULE_2_vux_src_components_sticky_index_vue__["a" /* default */]
  }
});

/***/ }),
/* 292 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_vux_src_components_tabbar_tabbar_vue__ = __webpack_require__(19);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_vux_src_components_tabbar_tabbar_item_vue__ = __webpack_require__(25);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2_vux_src_components_sticky_index_vue__ = __webpack_require__(35);





/* harmony default export */ __webpack_exports__["a"] = ({
  props: {
    title: String,
    default: '提交'
  },
  components: {
    Tabbar: __WEBPACK_IMPORTED_MODULE_0_vux_src_components_tabbar_tabbar_vue__["a" /* default */], TabbarItem: __WEBPACK_IMPORTED_MODULE_1_vux_src_components_tabbar_tabbar_item_vue__["a" /* default */], Sticky: __WEBPACK_IMPORTED_MODULE_2_vux_src_components_sticky_index_vue__["a" /* default */]
  }
});

/***/ }),
/* 293 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('tabbar',{attrs:{"slot":"bottom"},slot:"bottom"},[_c('tabbar-item',[_c('span',{staticStyle:{"font-size":"18px"},attrs:{"slot":"label"},slot:"label"},[_vm._v(_vm._s(_vm.title))])])],1)}
var staticRenderFns = []
var esExports = { render: render, staticRenderFns: staticRenderFns }
/* harmony default export */ __webpack_exports__["a"] = (esExports);

/***/ }),
/* 294 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vux_loader_src_script_loader_js_node_modules_vux_loader_src_script_loader_js_node_modules_vue_loader_lib_selector_type_script_index_0_index_vue__ = __webpack_require__(296);
/* unused harmony namespace reexport */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__babel_loader_node_modules_vux_loader_src_script_loader_js_node_modules_vue_loader_lib_selector_type_script_index_0_index_vue__ = __webpack_require__(297);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__node_modules_vue_loader_lib_template_compiler_index_id_data_v_4d7c50cc_hasScoped_false_buble_transforms_node_modules_vux_loader_src_before_template_compiler_loader_js_node_modules_vux_loader_src_template_loader_js_node_modules_vue_loader_lib_selector_type_template_index_0_index_vue__ = __webpack_require__(298);
function injectStyle (ssrContext) {
  __webpack_require__(295)
}
var normalizeComponent = __webpack_require__(0)
/* script */


/* template */

/* template functional */
var __vue_template_functional__ = false
/* styles */
var __vue_styles__ = injectStyle
/* scopeId */
var __vue_scopeId__ = null
/* moduleIdentifier (server only) */
var __vue_module_identifier__ = null
var Component = normalizeComponent(
  __WEBPACK_IMPORTED_MODULE_1__babel_loader_node_modules_vux_loader_src_script_loader_js_node_modules_vue_loader_lib_selector_type_script_index_0_index_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_2__node_modules_vue_loader_lib_template_compiler_index_id_data_v_4d7c50cc_hasScoped_false_buble_transforms_node_modules_vux_loader_src_before_template_compiler_loader_js_node_modules_vux_loader_src_template_loader_js_node_modules_vue_loader_lib_selector_type_template_index_0_index_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)

/* harmony default export */ __webpack_exports__["a"] = (Component.exports);


/***/ }),
/* 295 */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),
/* 296 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_vux_src_components_previewer_index_vue__ = __webpack_require__(82);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_vux_src_components_flexbox_flexbox_vue__ = __webpack_require__(31);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2_vux_src_components_flexbox_flexbox_item_vue__ = __webpack_require__(32);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_3_vux_src_directives_transfer_dom_index_js__ = __webpack_require__(6);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_4_vux_src_components_swiper_swiper_vue__ = __webpack_require__(8);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_5_vux_src_components_swiper_swiper_item_vue__ = __webpack_require__(15);








/* unused harmony default export */ var _unused_webpack_default_export = ({
  directives: {
    TransferDom: __WEBPACK_IMPORTED_MODULE_3_vux_src_directives_transfer_dom_index_js__["a" /* default */]
  },
  components: {
    Previewer: __WEBPACK_IMPORTED_MODULE_0_vux_src_components_previewer_index_vue__["a" /* default */], Flexbox: __WEBPACK_IMPORTED_MODULE_1_vux_src_components_flexbox_flexbox_vue__["a" /* default */], FlexboxItem: __WEBPACK_IMPORTED_MODULE_2_vux_src_components_flexbox_flexbox_item_vue__["a" /* default */], Swiper: __WEBPACK_IMPORTED_MODULE_4_vux_src_components_swiper_swiper_vue__["a" /* default */], SwiperItem: __WEBPACK_IMPORTED_MODULE_5_vux_src_components_swiper_swiper_item_vue__["a" /* default */]
  },
  props: {
    list: {
      type: Array,
      default: function _default() {
        return [];
      }
    },
    num: {
      type: Number,
      default: 4
    },
    type: {
      type: Number,
      default: 1
    },
    tagheight: {
      type: String,
      default: '180px'
    }
  },
  data: {
    options: {
      getThumbBoundsFn: function getThumbBoundsFn(index) {

        var thumbnail = document.querySelectorAll('.previewer-demo-img')[index];

        var pageYScroll = window.pageYOffset || document.documentElement.scrollTop;

        var rect = thumbnail.getBoundingClientRect();

        return { x: rect.left, y: rect.top + pageYScroll, w: rect.width };
      }
    }
  },
  methods: {
    logIndexChange: function logIndexChange(arg) {
      console.log(arg);
    },
    show: function show(index) {
      console.log(index);
      this.$refs.previewer.show(index);
    }
  }
});

/***/ }),
/* 297 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_vux_src_components_previewer_index_vue__ = __webpack_require__(82);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_vux_src_components_flexbox_flexbox_vue__ = __webpack_require__(31);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2_vux_src_components_flexbox_flexbox_item_vue__ = __webpack_require__(32);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_3_vux_src_directives_transfer_dom_index_js__ = __webpack_require__(6);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_4_vux_src_components_swiper_swiper_vue__ = __webpack_require__(8);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_5_vux_src_components_swiper_swiper_item_vue__ = __webpack_require__(15);








/* harmony default export */ __webpack_exports__["a"] = ({
  directives: {
    TransferDom: __WEBPACK_IMPORTED_MODULE_3_vux_src_directives_transfer_dom_index_js__["a" /* default */]
  },
  components: {
    Previewer: __WEBPACK_IMPORTED_MODULE_0_vux_src_components_previewer_index_vue__["a" /* default */], Flexbox: __WEBPACK_IMPORTED_MODULE_1_vux_src_components_flexbox_flexbox_vue__["a" /* default */], FlexboxItem: __WEBPACK_IMPORTED_MODULE_2_vux_src_components_flexbox_flexbox_item_vue__["a" /* default */], Swiper: __WEBPACK_IMPORTED_MODULE_4_vux_src_components_swiper_swiper_vue__["a" /* default */], SwiperItem: __WEBPACK_IMPORTED_MODULE_5_vux_src_components_swiper_swiper_item_vue__["a" /* default */]
  },
  props: {
    list: {
      type: Array,
      default: function _default() {
        return [];
      }
    },
    num: {
      type: Number,
      default: 4
    },
    type: {
      type: Number,
      default: 1
    },
    tagheight: {
      type: String,
      default: '180px'
    }
  },
  data: {
    options: {
      getThumbBoundsFn: function getThumbBoundsFn(index) {

        var thumbnail = document.querySelectorAll('.previewer-demo-img')[index];

        var pageYScroll = window.pageYOffset || document.documentElement.scrollTop;

        var rect = thumbnail.getBoundingClientRect();

        return { x: rect.left, y: rect.top + pageYScroll, w: rect.width };
      }
    }
  },
  methods: {
    logIndexChange: function logIndexChange(arg) {
      console.log(arg);
    },
    show: function show(index) {
      console.log(index);
      this.$refs.previewer.show(index);
    }
  }
});

/***/ }),
/* 298 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{style:(_vm.type ===1 ? 'margin: 10px 10px':'')},[(_vm.type ===1)?_c('flexbox',_vm._l((_vm.list),function(item,index){return _c('flexbox-item',{key:index,attrs:{"span":_vm.num}},[_c('div',{staticClass:"preview-img",style:({backgroundImage: 'url('+item.src+')'}),on:{"click":function($event){_vm.show(index)}}})])})):(_vm.type ===2)?_c('swiper',{attrs:{"auto":"","height":_vm.tagheight,"show-dots":false}},_vm._l((_vm.list),function(item,index){return _c('swiper-item',{key:index,staticClass:"swiper-demo-img",nativeOn:{"click":function($event){_vm.show(index)}}},[_c('img',{attrs:{"src":item.src,"height":_vm.tagheight}})])})):(_vm.type ===4)?_vm._l((_vm.list),function(row,index){return _c('div',[_c('img',{staticClass:"qrcode",staticStyle:{"width":"60px","height":"60px"},attrs:{"src":row.src,"alt":""},on:{"click":function($event){_vm.show(index)}}})])}):_vm._l((_vm.list),function(row,index){return _c('div',{staticStyle:{"padding":"0px 10px","font-size":"0"}},[_c('img',{attrs:{"src":row.src,"alt":"","width":"100%","height":"100%"},on:{"click":function($event){_vm.show(index)}}})])}),_vm._v(" "),_c('div',{directives:[{name:"transfer-dom",rawName:"v-transfer-dom"}]},[_c('previewer',{ref:"previewer",attrs:{"list":_vm.list,"options":_vm.options},on:{"on-index-change":_vm.logIndexChange}})],1)],2)}
var staticRenderFns = []
var esExports = { render: render, staticRenderFns: staticRenderFns }
/* harmony default export */ __webpack_exports__["a"] = (esExports);

/***/ }),
/* 299 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vux_loader_src_script_loader_js_node_modules_vux_loader_src_script_loader_js_node_modules_vue_loader_lib_selector_type_script_index_0_index_vue__ = __webpack_require__(300);
/* unused harmony reexport namespace */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__babel_loader_node_modules_vux_loader_src_script_loader_js_node_modules_vue_loader_lib_selector_type_script_index_0_index_vue__ = __webpack_require__(301);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__node_modules_vue_loader_lib_template_compiler_index_id_data_v_0822d61a_hasScoped_false_buble_transforms_node_modules_vux_loader_src_before_template_compiler_loader_js_node_modules_vux_loader_src_template_loader_js_node_modules_vue_loader_lib_selector_type_template_index_0_index_vue__ = __webpack_require__(302);
var normalizeComponent = __webpack_require__(0)
/* script */


/* template */

/* template functional */
var __vue_template_functional__ = false
/* styles */
var __vue_styles__ = null
/* scopeId */
var __vue_scopeId__ = null
/* moduleIdentifier (server only) */
var __vue_module_identifier__ = null
var Component = normalizeComponent(
  __WEBPACK_IMPORTED_MODULE_1__babel_loader_node_modules_vux_loader_src_script_loader_js_node_modules_vue_loader_lib_selector_type_script_index_0_index_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_2__node_modules_vue_loader_lib_template_compiler_index_id_data_v_0822d61a_hasScoped_false_buble_transforms_node_modules_vux_loader_src_before_template_compiler_loader_js_node_modules_vux_loader_src_template_loader_js_node_modules_vue_loader_lib_selector_type_template_index_0_index_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)

/* unused harmony default export */ var _unused_webpack_default_export = (Component.exports);


/***/ }),
/* 300 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_vux_src_components_swiper_swiper_vue__ = __webpack_require__(8);



/* unused harmony default export */ var _unused_webpack_default_export = ({
  data: function data() {
    return {
      tagheight: '120px'
    };
  },

  props: {
    items: Array
  },
  components: {
    Swiper: __WEBPACK_IMPORTED_MODULE_0_vux_src_components_swiper_swiper_vue__["a" /* default */]
  },

  mounted: function mounted() {
    var w = window.innerWidth;
    w = (w > 600 ? 600 : w) * (1 / 5);
    var height = w + 'px';
    this.tagheight = height;
  }
});

/***/ }),
/* 301 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_vux_src_components_swiper_swiper_vue__ = __webpack_require__(8);



/* harmony default export */ __webpack_exports__["a"] = ({
  data: function data() {
    return {
      tagheight: '120px'
    };
  },

  props: {
    items: Array
  },
  components: {
    Swiper: __WEBPACK_IMPORTED_MODULE_0_vux_src_components_swiper_swiper_vue__["a" /* default */]
  },

  mounted: function mounted() {
    var w = window.innerWidth;
    w = (w > 600 ? 600 : w) * (1 / 5);
    var height = w + 'px';
    this.tagheight = height;
  }
});

/***/ }),
/* 302 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('swiper',{attrs:{"list":_vm.items,"auto":"","dots-position":"center","show-desc-mask":false,"height":_vm.tagheight}})}
var staticRenderFns = []
var esExports = { render: render, staticRenderFns: staticRenderFns }
/* harmony default export */ __webpack_exports__["a"] = (esExports);

/***/ }),
/* 303 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vux_loader_src_script_loader_js_node_modules_vux_loader_src_script_loader_js_node_modules_vue_loader_lib_selector_type_script_index_0_index_vue__ = __webpack_require__(305);
/* unused harmony namespace reexport */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__babel_loader_node_modules_vux_loader_src_script_loader_js_node_modules_vue_loader_lib_selector_type_script_index_0_index_vue__ = __webpack_require__(306);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__node_modules_vue_loader_lib_template_compiler_index_id_data_v_e620eeae_hasScoped_true_buble_transforms_node_modules_vux_loader_src_before_template_compiler_loader_js_node_modules_vux_loader_src_template_loader_js_node_modules_vue_loader_lib_selector_type_template_index_0_index_vue__ = __webpack_require__(307);
function injectStyle (ssrContext) {
  __webpack_require__(304)
}
var normalizeComponent = __webpack_require__(0)
/* script */


/* template */

/* template functional */
var __vue_template_functional__ = false
/* styles */
var __vue_styles__ = injectStyle
/* scopeId */
var __vue_scopeId__ = "data-v-e620eeae"
/* moduleIdentifier (server only) */
var __vue_module_identifier__ = null
var Component = normalizeComponent(
  __WEBPACK_IMPORTED_MODULE_1__babel_loader_node_modules_vux_loader_src_script_loader_js_node_modules_vue_loader_lib_selector_type_script_index_0_index_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_2__node_modules_vue_loader_lib_template_compiler_index_id_data_v_e620eeae_hasScoped_true_buble_transforms_node_modules_vux_loader_src_before_template_compiler_loader_js_node_modules_vux_loader_src_template_loader_js_node_modules_vue_loader_lib_selector_type_template_index_0_index_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)

/* harmony default export */ __webpack_exports__["a"] = (Component.exports);


/***/ }),
/* 304 */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),
/* 305 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";


/* unused harmony default export */ var _unused_webpack_default_export = ({
    props: {
        num: Number
    },
    data: function data() {
        return {
            isOrange: false,
            isGreen: false,
            siginImg: [{
                imgUrl: __webpack_require__(124)
            }, {
                imgUrl: __webpack_require__(125)
            }, {
                imgUrl: __webpack_require__(126)
            }, {
                imgUrl: __webpack_require__(127)
            }, {
                imgUrl: __webpack_require__(128)
            }, {
                imgUrl: __webpack_require__(129)
            }],
            siginUrl: ''
        };
    },
    created: function created() {
        this.siginFun(this.num);
    },

    methods: {
        siginFun: function siginFun(num) {
            this.siginUrl = this.siginImg[0].imgUrl;
            if (num >= 0 && num <= 20) {
                this.isOrange = true;
                if (num >= 0 && num <= 5) {
                    this.siginUrl = this.siginImg[1].imgUrl;
                } else if (num >= 6 && num <= 10) {
                    this.siginUrl = this.siginImg[2].imgUrl;
                } else if (num >= 11 && num <= 20) {
                    this.siginUrl = this.siginImg[3].imgUrl;
                }
            } else if (num >= 21) {
                this.isOrange = false;
                this.isGreen = true;
                if (num >= 21 && num <= 25) {
                    this.siginUrl = this.siginImg[4].imgUrl;
                } else if (num >= 26) {
                    this.siginUrl = this.siginImg[5].imgUrl;
                }
            }
        }
    },
    watch: {
        num: function num(_num) {
            var self = this;

            this.siginUrl = this.siginImg[0].imgUrl;
            if (_num >= 0 && _num <= 20) {
                this.isOrange = true;
                if (_num >= 0 && _num <= 5) {
                    this.siginUrl = this.siginImg[1].imgUrl;
                } else if (_num >= 6 && _num <= 10) {
                    this.siginUrl = this.siginImg[2].imgUrl;
                } else if (_num >= 11 && _num <= 20) {
                    this.siginUrl = this.siginImg[3].imgUrl;
                }
            } else if (_num >= 21) {
                this.isOrange = false;
                this.isGreen = true;
                if (_num >= 21 && _num <= 25) {
                    this.siginUrl = this.siginImg[4].imgUrl;
                } else if (_num >= 26) {
                    this.siginUrl = this.siginImg[5].imgUrl;
                }
            }
        }
    }
});

/***/ }),
/* 306 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";


/* harmony default export */ __webpack_exports__["a"] = ({
    props: {
        num: Number
    },
    data: function data() {
        return {
            isOrange: false,
            isGreen: false,
            siginImg: [{
                imgUrl: __webpack_require__(124)
            }, {
                imgUrl: __webpack_require__(125)
            }, {
                imgUrl: __webpack_require__(126)
            }, {
                imgUrl: __webpack_require__(127)
            }, {
                imgUrl: __webpack_require__(128)
            }, {
                imgUrl: __webpack_require__(129)
            }],
            siginUrl: ''
        };
    },
    created: function created() {
        this.siginFun(this.num);
    },

    methods: {
        siginFun: function siginFun(num) {
            this.siginUrl = this.siginImg[0].imgUrl;
            if (num >= 0 && num <= 20) {
                this.isOrange = true;
                if (num >= 0 && num <= 5) {
                    this.siginUrl = this.siginImg[1].imgUrl;
                } else if (num >= 6 && num <= 10) {
                    this.siginUrl = this.siginImg[2].imgUrl;
                } else if (num >= 11 && num <= 20) {
                    this.siginUrl = this.siginImg[3].imgUrl;
                }
            } else if (num >= 21) {
                this.isOrange = false;
                this.isGreen = true;
                if (num >= 21 && num <= 25) {
                    this.siginUrl = this.siginImg[4].imgUrl;
                } else if (num >= 26) {
                    this.siginUrl = this.siginImg[5].imgUrl;
                }
            }
        }
    },
    watch: {
        num: function num(_num) {
            var self = this;

            this.siginUrl = this.siginImg[0].imgUrl;
            if (_num >= 0 && _num <= 20) {
                this.isOrange = true;
                if (_num >= 0 && _num <= 5) {
                    this.siginUrl = this.siginImg[1].imgUrl;
                } else if (_num >= 6 && _num <= 10) {
                    this.siginUrl = this.siginImg[2].imgUrl;
                } else if (_num >= 11 && _num <= 20) {
                    this.siginUrl = this.siginImg[3].imgUrl;
                }
            } else if (_num >= 21) {
                this.isOrange = false;
                this.isGreen = true;
                if (_num >= 21 && _num <= 25) {
                    this.siginUrl = this.siginImg[4].imgUrl;
                } else if (_num >= 26) {
                    this.siginUrl = this.siginImg[5].imgUrl;
                }
            }
        }
    }
});

/***/ }),
/* 307 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',[_c('div',{staticClass:"sign-wrapper"},[_c('img',{attrs:{"src":_vm.siginUrl}}),_vm._v(" "),_c('span',{staticClass:"sign-number",class:{'colorO':_vm.isOrange,'colorG':_vm.isGreen}},[_vm._v(_vm._s(_vm.num))])])])}
var staticRenderFns = []
var esExports = { render: render, staticRenderFns: staticRenderFns }
/* harmony default export */ __webpack_exports__["a"] = (esExports);

/***/ }),
/* 308 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__ln_filter_bar__ = __webpack_require__(309);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__ln_filter_bar_pop__ = __webpack_require__(80);
/* harmony reexport (binding) */ __webpack_require__.d(__webpack_exports__, "a", function() { return __WEBPACK_IMPORTED_MODULE_0__ln_filter_bar__["a"]; });
/* unused harmony reexport LnFilterBarPop */





/***/ }),
/* 309 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vux_loader_src_script_loader_js_node_modules_vux_loader_src_script_loader_js_node_modules_vue_loader_lib_selector_type_script_index_0_ln_filter_bar_vue__ = __webpack_require__(311);
/* unused harmony namespace reexport */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__babel_loader_node_modules_vux_loader_src_script_loader_js_node_modules_vue_loader_lib_selector_type_script_index_0_ln_filter_bar_vue__ = __webpack_require__(316);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__node_modules_vue_loader_lib_template_compiler_index_id_data_v_fe688900_hasScoped_false_buble_transforms_node_modules_vux_loader_src_before_template_compiler_loader_js_node_modules_vux_loader_src_template_loader_js_node_modules_vue_loader_lib_selector_type_template_index_0_ln_filter_bar_vue__ = __webpack_require__(317);
function injectStyle (ssrContext) {
  __webpack_require__(310)
}
var normalizeComponent = __webpack_require__(0)
/* script */


/* template */

/* template functional */
var __vue_template_functional__ = false
/* styles */
var __vue_styles__ = injectStyle
/* scopeId */
var __vue_scopeId__ = null
/* moduleIdentifier (server only) */
var __vue_module_identifier__ = null
var Component = normalizeComponent(
  __WEBPACK_IMPORTED_MODULE_1__babel_loader_node_modules_vux_loader_src_script_loader_js_node_modules_vue_loader_lib_selector_type_script_index_0_ln_filter_bar_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_2__node_modules_vue_loader_lib_template_compiler_index_id_data_v_fe688900_hasScoped_false_buble_transforms_node_modules_vux_loader_src_before_template_compiler_loader_js_node_modules_vux_loader_src_template_loader_js_node_modules_vue_loader_lib_selector_type_template_index_0_ln_filter_bar_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)

/* harmony default export */ __webpack_exports__["a"] = (Component.exports);


/***/ }),
/* 310 */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),
/* 311 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__ln_filter_bar_pop__ = __webpack_require__(80);



/* unused harmony default export */ var _unused_webpack_default_export = ({
  name: 'ln-filter-bar',
  components: {
    LnFilterBarPop: __WEBPACK_IMPORTED_MODULE_0__ln_filter_bar_pop__["a" /* default */]
  },
  props: {
    top: String,
    menus: {
      type: Array,
      required: true,
      validator: function validator(value) {
        return true;
      }
    }
  },
  data: function data() {
    return {
      show: false,
      hasTabHeader: false,
      selectedMenu: {},
      selectedIndex: undefined
    };
  },

  methods: {
    onItemClick: function onItemClick(menu, index) {
      this.show = true;
      this.selectedMenu = menu;
      this.selectedIndex = index;
      this.hasTabHeader = menu.tabs.length > 1 ? 1 : 0;
      this.$emit('on-click-item', menu, index);
    },
    onChangeSelect: function onChangeSelect(index) {
      var selectData = [];
      this.menus.forEach(function (barMenu, index, arr) {
        var _selectBarData = {};
        _selectBarData.name = barMenu.name;
        _selectBarData.value = barMenu.value;
        _selectBarData.tab = {};

        var tab = barMenu.tabs[barMenu.selectIndex];
        _selectBarData.tab.name = tab.name;
        _selectBarData.tab.value = tab.value;

        var mainItem = tab.detailList[tab.selectIndex];
        var name = mainItem.name,
            value = mainItem.value,
            list = mainItem.list,
            selectIndex = mainItem.selectIndex;

        _selectBarData.tab.mainItem = {};
        _selectBarData.tab.mainItem.name = name;
        _selectBarData.tab.mainItem.value = value;

        var subItem = false;
        if (list) {
          subItem = list[selectIndex];
          _selectBarData.tab.mainItem.subItem = {};
          _selectBarData.tab.mainItem.subItem.name = subItem.name;
          _selectBarData.tab.mainItem.subItem.value = subItem.value;
        } else {
          _selectBarData.tab.mainItem.subItem = subItem;
        }
        selectData.push(_selectBarData);
      });
      this.$emit('on-change-select', selectData, this.selectedIndex);
    }
  },
  watch: {
    show: function show(val) {
      if (val === false) {
        this.selectedMenu = {};
        this.selectedIndex = undefined;
      }
    }
  }
});

/***/ }),
/* 312 */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),
/* 313 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_object_keys__ = __webpack_require__(18);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_object_keys___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_object_keys__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_vux_src_components_tab_tab_vue__ = __webpack_require__(21);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2_vux_src_components_tab_tab_item_vue__ = __webpack_require__(22);





/* unused harmony default export */ var _unused_webpack_default_export = ({
  name: 'ln-filter-bar-pop',
  components: {
    Tab: __WEBPACK_IMPORTED_MODULE_1_vux_src_components_tab_tab_vue__["a" /* default */], TabItem: __WEBPACK_IMPORTED_MODULE_2_vux_src_components_tab_tab_item_vue__["a" /* default */]
  },
  props: {
    value: {
      type: Boolean,
      default: true
    },
    menu: {
      type: Object
    },
    hasTabHeader: {
      type: Boolean,
      default: true
    }
  },
  data: function data() {
    return {
      selectIndexTab: 0,
      currentSelectIndex: 0,
      sideMenus: [],
      items: [],
      range: {}
    };
  },
  beforeMount: function beforeMount() {
    this.initData();
  },

  watch: {
    menu: function menu(val) {
      this.initData();
    },
    selectIndexTab: function selectIndexTab(val, oldval) {
      if (val !== oldval) {

        this.initData(val);
      }
    }
  },
  methods: {
    initData: function initData(tabIndex) {
      var tmpTabIndx = 0;
      tabIndex === undefined ? tmpTabIndx = this.menu.selectIndex : tmpTabIndx = tabIndex;

      this.sideMenus = this.menu.tabs[tmpTabIndx];

      if (this.selectIndexTab === this.menu.selectIndex) {
        this.currentSelectIndex = this.sideMenus.selectIndex;
      }

      if (this.sideMenus.detailList.length) {
        var items = this.sideMenus.detailList[this.currentSelectIndex];
        if (items.hasOwnProperty('list') && items.list.length) {
          this.items = items;
        } else {
          this.items = false;
        }
      }
    },
    onSideItem: function onSideItem(item, index) {
      if (this.currentSelectIndex !== index) {
        this.currentSelectIndex = index;

        if (this.sideMenus.detailList[index].list) {
          this.items = this.sideMenus.detailList[index];
        } else {

          this.changeSelect(index);
        }
      }
    },
    onListClick: function onListClick(item, index) {
      this.changeSelect(index);
    },
    onFilterClick: function onFilterClick(tab, index, idx) {
      tab.detailList[idx].selectIndex = idx;
      if (!this.range[index]) {
        this.range[index] = { name: tab.name, value: {} };
        this.range[index].value[idx] = tab.detailList[idx].value;
      } else {
        if (!this.range[index].value[idx]) {
          this.range[index].value[idx] = tab.detailList[idx].value;
        } else {
          delete this.range[index].value[idx];
          tab.detailList[idx].selectIndex = -1;
        }
      }
    },
    changeSelect: function changeSelect(index) {

      this.menu.selectIndex = this.selectIndexTab;

      this.sideMenus.selectIndex = this.currentSelectIndex;
      if (this.items) {

        this.items.selectIndex = index;

        this.menu.name = this.items.list[this.items.selectIndex].name;
        this.menu.value = this.items.list[this.items.selectIndex].value;
      } else {

        this.menu.name = this.sideMenus.detailList[this.sideMenus.selectIndex].name;
        this.menu.value = this.sideMenus.detailList[this.sideMenus.selectIndex].value;
      }
      this.$emit('on-change-select', index);
      this.$emit('input', false);
    },
    changeRangeSelect: function changeRangeSelect() {
      this.menu.name = '筛选';
      for (var i in this.range) {
        if (__WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_object_keys___default()(this.range[i].value).length === 0) {
          delete this.range[i];
        }
      }
      this.menu.value = __WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_object_keys___default()(this.range).length > 0 ? this.range : '';
      this.$emit('on-change-select', this.range);
      this.$emit('input', false);
    },
    handleEnsure: function handleEnsure() {
      this.changeRangeSelect();
    },
    handleClean: function handleClean() {
      this.menu.tabs.map(function (item) {
        item.detailList.map(function (_item) {
          _item.selectIndex = -1;
        });
      });
      this.range = {};
    }
  }
});

/***/ }),
/* 314 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_object_keys__ = __webpack_require__(18);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_object_keys___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_object_keys__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_vux_src_components_tab_tab_vue__ = __webpack_require__(21);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2_vux_src_components_tab_tab_item_vue__ = __webpack_require__(22);





/* harmony default export */ __webpack_exports__["a"] = ({
  name: 'ln-filter-bar-pop',
  components: {
    Tab: __WEBPACK_IMPORTED_MODULE_1_vux_src_components_tab_tab_vue__["a" /* default */], TabItem: __WEBPACK_IMPORTED_MODULE_2_vux_src_components_tab_tab_item_vue__["a" /* default */]
  },
  props: {
    value: {
      type: Boolean,
      default: true
    },
    menu: {
      type: Object
    },
    hasTabHeader: {
      type: Boolean,
      default: true
    }
  },
  data: function data() {
    return {
      selectIndexTab: 0,
      currentSelectIndex: 0,
      sideMenus: [],
      items: [],
      range: {}
    };
  },
  beforeMount: function beforeMount() {
    this.initData();
  },

  watch: {
    menu: function menu(val) {
      this.initData();
    },
    selectIndexTab: function selectIndexTab(val, oldval) {
      if (val !== oldval) {

        this.initData(val);
      }
    }
  },
  methods: {
    initData: function initData(tabIndex) {
      var tmpTabIndx = 0;
      tabIndex === undefined ? tmpTabIndx = this.menu.selectIndex : tmpTabIndx = tabIndex;

      this.sideMenus = this.menu.tabs[tmpTabIndx];

      if (this.selectIndexTab === this.menu.selectIndex) {
        this.currentSelectIndex = this.sideMenus.selectIndex;
      }

      if (this.sideMenus.detailList.length) {
        var items = this.sideMenus.detailList[this.currentSelectIndex];
        if (items.hasOwnProperty('list') && items.list.length) {
          this.items = items;
        } else {
          this.items = false;
        }
      }
    },
    onSideItem: function onSideItem(item, index) {
      if (this.currentSelectIndex !== index) {
        this.currentSelectIndex = index;

        if (this.sideMenus.detailList[index].list) {
          this.items = this.sideMenus.detailList[index];
        } else {

          this.changeSelect(index);
        }
      }
    },
    onListClick: function onListClick(item, index) {
      this.changeSelect(index);
    },
    onFilterClick: function onFilterClick(tab, index, idx) {
      tab.detailList[idx].selectIndex = idx;
      if (!this.range[index]) {
        this.range[index] = { name: tab.name, value: {} };
        this.range[index].value[idx] = tab.detailList[idx].value;
      } else {
        if (!this.range[index].value[idx]) {
          this.range[index].value[idx] = tab.detailList[idx].value;
        } else {
          delete this.range[index].value[idx];
          tab.detailList[idx].selectIndex = -1;
        }
      }
    },
    changeSelect: function changeSelect(index) {

      this.menu.selectIndex = this.selectIndexTab;

      this.sideMenus.selectIndex = this.currentSelectIndex;
      if (this.items) {

        this.items.selectIndex = index;

        this.menu.name = this.items.list[this.items.selectIndex].name;
        this.menu.value = this.items.list[this.items.selectIndex].value;
      } else {

        this.menu.name = this.sideMenus.detailList[this.sideMenus.selectIndex].name;
        this.menu.value = this.sideMenus.detailList[this.sideMenus.selectIndex].value;
      }
      this.$emit('on-change-select', index);
      this.$emit('input', false);
    },
    changeRangeSelect: function changeRangeSelect() {
      this.menu.name = '筛选';
      for (var i in this.range) {
        if (__WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_object_keys___default()(this.range[i].value).length === 0) {
          delete this.range[i];
        }
      }
      this.menu.value = __WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_object_keys___default()(this.range).length > 0 ? this.range : '';
      this.$emit('on-change-select', this.range);
      this.$emit('input', false);
    },
    handleEnsure: function handleEnsure() {
      this.changeRangeSelect();
    },
    handleClean: function handleClean() {
      this.menu.tabs.map(function (item) {
        item.detailList.map(function (_item) {
          _item.selectIndex = -1;
        });
      });
      this.range = {};
    }
  }
});

/***/ }),
/* 315 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{staticClass:"dropdown-wrapper"},[(_vm.hasTabHeader && _vm.menu.type!=='filter')?_c('tab',{staticClass:"dropdown-tab",attrs:{"line-width":1},model:{value:(_vm.selectIndexTab),callback:function ($$v) {_vm.selectIndexTab=$$v},expression:"selectIndexTab"}},_vm._l((_vm.menu.tabs),function(tab,index){return _c('tab-item',{key:index,attrs:{"selected":_vm.selectIndexTab == index},on:{"on-item-click":_vm.onTabClick}},[_vm._v(_vm._s(tab.name))])})):_vm._e(),_vm._v(" "),(_vm.menu.type!=='filter')?_c('div',{staticClass:"dropdown-main"},[_c('div',{staticClass:"dropdown-main__side",class:{'full-line': !_vm.items}},_vm._l((_vm.sideMenus.detailList),function(item,index){return _c('a',{staticClass:"dropdown-list",class:{'active': _vm.currentSelectIndex == index},attrs:{"href":"javascript:;","role":"button"},on:{"click":function($event){_vm.onSideItem(item, index)}}},[_c('span',[_vm._v(_vm._s(item.name))])])})),_vm._v(" "),(_vm.items)?_c('div',{staticClass:"dropdown-main__list"},_vm._l((_vm.items.list),function(item,index){return _c('a',{key:index,staticClass:"dropdown-list",class:{'active': _vm.items.selectIndex == index},on:{"click":function($event){_vm.onListClick(item, index)}}},[_c('span',[_vm._v(_vm._s(item.name))])])})):_vm._e()]):_vm._e(),_vm._v(" "),(_vm.menu.type==='filter')?_c('div',{staticClass:"dropdown-filter"},[_vm._l((_vm.menu.tabs),function(tab,index){return _c('dl',{key:index},[_c('dt',[_vm._v(_vm._s(tab.name))]),_vm._v(" "),_c('dd',{staticClass:"radioarea"},_vm._l((tab.detailList),function(item,idx){return _c('label',{staticClass:"btn",class:{'btn-weak': item.selectIndex === idx},on:{"click":function($event){_vm.onFilterClick(tab, index, idx)}}},[_c('span',[_vm._v(_vm._s(item.name))])])}))])}),_vm._v(" "),_c('div',{staticClass:"btnarea vux-1px-t"},[_c('a',{staticClass:"vux-1px-r",attrs:{"href":"javascript:;","role":"button"},on:{"click":_vm.handleClean}},[_vm._v("重置")]),_vm._v(" "),_c('a',{attrs:{"href":"javascript:;","role":"button"},on:{"click":_vm.handleEnsure}},[_vm._v("保存")])])],2):_vm._e()],1)}
var staticRenderFns = []
var esExports = { render: render, staticRenderFns: staticRenderFns }
/* harmony default export */ __webpack_exports__["a"] = (esExports);

/***/ }),
/* 316 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__ln_filter_bar_pop__ = __webpack_require__(80);



/* harmony default export */ __webpack_exports__["a"] = ({
  name: 'ln-filter-bar',
  components: {
    LnFilterBarPop: __WEBPACK_IMPORTED_MODULE_0__ln_filter_bar_pop__["a" /* default */]
  },
  props: {
    top: String,
    menus: {
      type: Array,
      required: true,
      validator: function validator(value) {
        return true;
      }
    }
  },
  data: function data() {
    return {
      show: false,
      hasTabHeader: false,
      selectedMenu: {},
      selectedIndex: undefined
    };
  },

  methods: {
    onItemClick: function onItemClick(menu, index) {
      this.show = true;
      this.selectedMenu = menu;
      this.selectedIndex = index;
      this.hasTabHeader = menu.tabs.length > 1 ? 1 : 0;
      this.$emit('on-click-item', menu, index);
    },
    onChangeSelect: function onChangeSelect(index) {
      var selectData = [];
      this.menus.forEach(function (barMenu, index, arr) {
        var _selectBarData = {};
        _selectBarData.name = barMenu.name;
        _selectBarData.value = barMenu.value;
        _selectBarData.tab = {};

        var tab = barMenu.tabs[barMenu.selectIndex];
        _selectBarData.tab.name = tab.name;
        _selectBarData.tab.value = tab.value;

        var mainItem = tab.detailList[tab.selectIndex];
        var name = mainItem.name,
            value = mainItem.value,
            list = mainItem.list,
            selectIndex = mainItem.selectIndex;

        _selectBarData.tab.mainItem = {};
        _selectBarData.tab.mainItem.name = name;
        _selectBarData.tab.mainItem.value = value;

        var subItem = false;
        if (list) {
          subItem = list[selectIndex];
          _selectBarData.tab.mainItem.subItem = {};
          _selectBarData.tab.mainItem.subItem.name = subItem.name;
          _selectBarData.tab.mainItem.subItem.value = subItem.value;
        } else {
          _selectBarData.tab.mainItem.subItem = subItem;
        }
        selectData.push(_selectBarData);
      });
      this.$emit('on-change-select', selectData, this.selectedIndex);
    }
  },
  watch: {
    show: function show(val) {
      if (val === false) {
        this.selectedMenu = {};
        this.selectedIndex = undefined;
      }
    }
  }
});

/***/ }),
/* 317 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',[(_vm.show)?_c('transition',[_c('div',{staticClass:"weui-mask",staticStyle:{"z-index":"1"},on:{"click":function($event){_vm.show=false}}})]):_vm._e(),_vm._v(" "),_c('div',{staticClass:"ln-filter-container",class:{fixed:_vm.show},style:({top:_vm.top})},[_c('div',{staticClass:"filter-wrapper"},_vm._l((_vm.menus),function(menu,index){return _c('a',{staticClass:"dropdown-toggle caret",class:{active: index == _vm.selectedIndex},on:{"click":function($event){_vm.onItemClick(menu, index)}}},[_vm._v(_vm._s(menu.name))])})),_vm._v(" "),(_vm.show)?_c('ln-filter-bar-pop',{attrs:{"hasTabHeader":_vm.hasTabHeader,"menu":_vm.selectedMenu},on:{"on-change-select":_vm.onChangeSelect},model:{value:(_vm.show),callback:function ($$v) {_vm.show=$$v},expression:"show"}}):_vm._e()],1)],1)}
var staticRenderFns = []
var esExports = { render: render, staticRenderFns: staticRenderFns }
/* harmony default export */ __webpack_exports__["a"] = (esExports);

/***/ }),
/* 318 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vux_loader_src_script_loader_js_node_modules_vux_loader_src_script_loader_js_node_modules_vue_loader_lib_selector_type_script_index_0_indexApp_vue__ = __webpack_require__(320);
/* unused harmony namespace reexport */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__babel_loader_node_modules_vux_loader_src_script_loader_js_node_modules_vue_loader_lib_selector_type_script_index_0_indexApp_vue__ = __webpack_require__(326);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__node_modules_vue_loader_lib_template_compiler_index_id_data_v_0f9849d6_hasScoped_true_buble_transforms_node_modules_vux_loader_src_before_template_compiler_loader_js_node_modules_vux_loader_src_template_loader_js_node_modules_vue_loader_lib_selector_type_template_index_0_indexApp_vue__ = __webpack_require__(327);
function injectStyle (ssrContext) {
  __webpack_require__(319)
}
var normalizeComponent = __webpack_require__(0)
/* script */


/* template */

/* template functional */
var __vue_template_functional__ = false
/* styles */
var __vue_styles__ = injectStyle
/* scopeId */
var __vue_scopeId__ = "data-v-0f9849d6"
/* moduleIdentifier (server only) */
var __vue_module_identifier__ = null
var Component = normalizeComponent(
  __WEBPACK_IMPORTED_MODULE_1__babel_loader_node_modules_vux_loader_src_script_loader_js_node_modules_vue_loader_lib_selector_type_script_index_0_indexApp_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_2__node_modules_vue_loader_lib_template_compiler_index_id_data_v_0f9849d6_hasScoped_true_buble_transforms_node_modules_vux_loader_src_before_template_compiler_loader_js_node_modules_vux_loader_src_template_loader_js_node_modules_vue_loader_lib_selector_type_template_index_0_indexApp_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)

/* harmony default export */ __webpack_exports__["a"] = (Component.exports);


/***/ }),
/* 319 */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),
/* 320 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_vux_src_components_spinner_index_vue__ = __webpack_require__(130);



/* unused harmony default export */ var _unused_webpack_default_export = ({
  props: {
    title: {
      type: String,
      default: ' '
    },
    loadIng: {
      type: Boolean,
      default: false
    }
  },
  components: {
    Spinner: __WEBPACK_IMPORTED_MODULE_0_vux_src_components_spinner_index_vue__["a" /* default */]
  }
});

/***/ }),
/* 321 */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),
/* 322 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__spinner__ = __webpack_require__(131);



var types = ['android', 'ios', 'ios-small', 'bubbles', 'circles', 'crescent', 'dots', 'lines', 'ripple', 'spiral'];

/* unused harmony default export */ var _unused_webpack_default_export = ({
  name: 'spinner',
  mounted: function mounted() {
    var _this = this;

    this.$nextTick(function () {
      Object(__WEBPACK_IMPORTED_MODULE_0__spinner__["a" /* default */])(_this.$el, _this.type, _this.size);
    });
  },

  props: {
    type: {
      type: String,
      default: 'ios'
    },
    size: String
  },
  computed: {
    styles: function styles() {
      if (typeof this.size !== 'undefined' && this.size !== '28px') {
        return {
          width: this.size,
          height: this.size
        };
      }
    },
    className: function className() {
      var rs = {};
      for (var i = 0; i < types.length; i++) {
        rs['vux-spinner-' + types[i]] = this.type === types[i];
      }
      return rs;
    }
  }
});

/***/ }),
/* 323 */
/***/ (function(module, exports) {

var lastTime = 0;
var vendors = ['webkit', 'moz'];
for (var x = 0; x < vendors.length && !window.requestAnimationFrame; ++x) {
  window.requestAnimationFrame = window[vendors[x] + 'RequestAnimationFrame'];
  window.cancelAnimationFrame = window[vendors[x] + 'CancelAnimationFrame'] || window[vendors[x] + 'CancelRequestAnimationFrame'];
}

if (!window.requestAnimationFrame) {
  window.requestAnimationFrame = function (callback, element) {
    var currTime = new Date().getTime();
    var timeToCall = Math.max(0, 16 - (currTime - lastTime));
    var id = window.setTimeout(function () {
      callback(currTime + timeToCall);
    }, timeToCall);
    lastTime = currTime + timeToCall;
    return id;
  };
}
if (!window.cancelAnimationFrame) {
  window.cancelAnimationFrame = function (id) {
    clearTimeout(id);
  };
}

/***/ }),
/* 324 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__spinner__ = __webpack_require__(131);



var types = ['android', 'ios', 'ios-small', 'bubbles', 'circles', 'crescent', 'dots', 'lines', 'ripple', 'spiral'];

/* harmony default export */ __webpack_exports__["a"] = ({
  name: 'spinner',
  mounted: function mounted() {
    var _this = this;

    this.$nextTick(function () {
      Object(__WEBPACK_IMPORTED_MODULE_0__spinner__["a" /* default */])(_this.$el, _this.type, _this.size);
    });
  },

  props: {
    type: {
      type: String,
      default: 'ios'
    },
    size: String
  },
  computed: {
    styles: function styles() {
      if (typeof this.size !== 'undefined' && this.size !== '28px') {
        return {
          width: this.size,
          height: this.size
        };
      }
    },
    className: function className() {
      var rs = {};
      for (var i = 0; i < types.length; i++) {
        rs['vux-spinner-' + types[i]] = this.type === types[i];
      }
      return rs;
    }
  }
});

/***/ }),
/* 325 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('span',{staticClass:"vux-spinner",class:_vm.className,style:(_vm.styles)})}
var staticRenderFns = []
var esExports = { render: render, staticRenderFns: staticRenderFns }
/* harmony default export */ __webpack_exports__["a"] = (esExports);

/***/ }),
/* 326 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_vux_src_components_spinner_index_vue__ = __webpack_require__(130);



/* harmony default export */ __webpack_exports__["a"] = ({
  props: {
    title: {
      type: String,
      default: ' '
    },
    loadIng: {
      type: Boolean,
      default: false
    }
  },
  components: {
    Spinner: __WEBPACK_IMPORTED_MODULE_0_vux_src_components_spinner_index_vue__["a" /* default */]
  }
});

/***/ }),
/* 327 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return (_vm.loadIng)?_c('div',{staticClass:"loading-box"},[_c('div',{staticClass:"center"},[_c('spinner',{staticClass:"spinner",attrs:{"type":"android","size":"40px"}}),_vm._v(" "),(_vm.title)?_c('div',{staticClass:"title"},[_vm._v(_vm._s(_vm.title))]):_vm._e()],1)]):_vm._e()}
var staticRenderFns = []
var esExports = { render: render, staticRenderFns: staticRenderFns }
/* harmony default export */ __webpack_exports__["a"] = (esExports);

/***/ }),
/* 328 */,
/* 329 */,
/* 330 */,
/* 331 */,
/* 332 */,
/* 333 */,
/* 334 */,
/* 335 */,
/* 336 */,
/* 337 */,
/* 338 */,
/* 339 */,
/* 340 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_vux_loader_src_script_loader_js_vux_loader_src_script_loader_js_vue_loader_lib_selector_type_script_index_0_checker_vue__ = __webpack_require__(410);
/* unused harmony namespace reexport */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__babel_loader_vux_loader_src_script_loader_js_vue_loader_lib_selector_type_script_index_0_checker_vue__ = __webpack_require__(411);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__vue_loader_lib_template_compiler_index_id_data_v_a5c9f890_hasScoped_false_buble_transforms_vux_loader_src_before_template_compiler_loader_js_vux_loader_src_template_loader_js_vue_loader_lib_selector_type_template_index_0_checker_vue__ = __webpack_require__(412);
function injectStyle (ssrContext) {
  __webpack_require__(409)
}
var normalizeComponent = __webpack_require__(0)
/* script */


/* template */

/* template functional */
var __vue_template_functional__ = false
/* styles */
var __vue_styles__ = injectStyle
/* scopeId */
var __vue_scopeId__ = null
/* moduleIdentifier (server only) */
var __vue_module_identifier__ = null
var Component = normalizeComponent(
  __WEBPACK_IMPORTED_MODULE_1__babel_loader_vux_loader_src_script_loader_js_vue_loader_lib_selector_type_script_index_0_checker_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_2__vue_loader_lib_template_compiler_index_id_data_v_a5c9f890_hasScoped_false_buble_transforms_vux_loader_src_before_template_compiler_loader_js_vux_loader_src_template_loader_js_vue_loader_lib_selector_type_template_index_0_checker_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)

/* harmony default export */ __webpack_exports__["a"] = (Component.exports);


/***/ }),
/* 341 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_vux_loader_src_script_loader_js_vux_loader_src_script_loader_js_vue_loader_lib_selector_type_script_index_0_checker_item_vue__ = __webpack_require__(414);
/* unused harmony namespace reexport */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__babel_loader_vux_loader_src_script_loader_js_vue_loader_lib_selector_type_script_index_0_checker_item_vue__ = __webpack_require__(415);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__vue_loader_lib_template_compiler_index_id_data_v_dcb1243a_hasScoped_false_buble_transforms_vux_loader_src_before_template_compiler_loader_js_vux_loader_src_template_loader_js_vue_loader_lib_selector_type_template_index_0_checker_item_vue__ = __webpack_require__(416);
function injectStyle (ssrContext) {
  __webpack_require__(413)
}
var normalizeComponent = __webpack_require__(0)
/* script */


/* template */

/* template functional */
var __vue_template_functional__ = false
/* styles */
var __vue_styles__ = injectStyle
/* scopeId */
var __vue_scopeId__ = null
/* moduleIdentifier (server only) */
var __vue_module_identifier__ = null
var Component = normalizeComponent(
  __WEBPACK_IMPORTED_MODULE_1__babel_loader_vux_loader_src_script_loader_js_vue_loader_lib_selector_type_script_index_0_checker_item_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_2__vue_loader_lib_template_compiler_index_id_data_v_dcb1243a_hasScoped_false_buble_transforms_vux_loader_src_before_template_compiler_loader_js_vux_loader_src_template_loader_js_vue_loader_lib_selector_type_template_index_0_checker_item_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)

/* harmony default export */ __webpack_exports__["a"] = (Component.exports);


/***/ }),
/* 342 */,
/* 343 */,
/* 344 */,
/* 345 */,
/* 346 */,
/* 347 */,
/* 348 */,
/* 349 */,
/* 350 */,
/* 351 */,
/* 352 */,
/* 353 */,
/* 354 */,
/* 355 */,
/* 356 */,
/* 357 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/**
  * vue-router v3.0.1
  * (c) 2017 Evan You
  * @license MIT
  */
/*  */

function assert (condition, message) {
  if (!condition) {
    throw new Error(("[vue-router] " + message))
  }
}

function warn (condition, message) {
  if (false) {
    typeof console !== 'undefined' && console.warn(("[vue-router] " + message));
  }
}

function isError (err) {
  return Object.prototype.toString.call(err).indexOf('Error') > -1
}

var View = {
  name: 'router-view',
  functional: true,
  props: {
    name: {
      type: String,
      default: 'default'
    }
  },
  render: function render (_, ref) {
    var props = ref.props;
    var children = ref.children;
    var parent = ref.parent;
    var data = ref.data;

    data.routerView = true;

    // directly use parent context's createElement() function
    // so that components rendered by router-view can resolve named slots
    var h = parent.$createElement;
    var name = props.name;
    var route = parent.$route;
    var cache = parent._routerViewCache || (parent._routerViewCache = {});

    // determine current view depth, also check to see if the tree
    // has been toggled inactive but kept-alive.
    var depth = 0;
    var inactive = false;
    while (parent && parent._routerRoot !== parent) {
      if (parent.$vnode && parent.$vnode.data.routerView) {
        depth++;
      }
      if (parent._inactive) {
        inactive = true;
      }
      parent = parent.$parent;
    }
    data.routerViewDepth = depth;

    // render previous view if the tree is inactive and kept-alive
    if (inactive) {
      return h(cache[name], data, children)
    }

    var matched = route.matched[depth];
    // render empty node if no matched route
    if (!matched) {
      cache[name] = null;
      return h()
    }

    var component = cache[name] = matched.components[name];

    // attach instance registration hook
    // this will be called in the instance's injected lifecycle hooks
    data.registerRouteInstance = function (vm, val) {
      // val could be undefined for unregistration
      var current = matched.instances[name];
      if (
        (val && current !== vm) ||
        (!val && current === vm)
      ) {
        matched.instances[name] = val;
      }
    }

    // also register instance in prepatch hook
    // in case the same component instance is reused across different routes
    ;(data.hook || (data.hook = {})).prepatch = function (_, vnode) {
      matched.instances[name] = vnode.componentInstance;
    };

    // resolve props
    var propsToPass = data.props = resolveProps(route, matched.props && matched.props[name]);
    if (propsToPass) {
      // clone to prevent mutation
      propsToPass = data.props = extend({}, propsToPass);
      // pass non-declared props as attrs
      var attrs = data.attrs = data.attrs || {};
      for (var key in propsToPass) {
        if (!component.props || !(key in component.props)) {
          attrs[key] = propsToPass[key];
          delete propsToPass[key];
        }
      }
    }

    return h(component, data, children)
  }
};

function resolveProps (route, config) {
  switch (typeof config) {
    case 'undefined':
      return
    case 'object':
      return config
    case 'function':
      return config(route)
    case 'boolean':
      return config ? route.params : undefined
    default:
      if (false) {
        warn(
          false,
          "props in \"" + (route.path) + "\" is a " + (typeof config) + ", " +
          "expecting an object, function or boolean."
        );
      }
  }
}

function extend (to, from) {
  for (var key in from) {
    to[key] = from[key];
  }
  return to
}

/*  */

var encodeReserveRE = /[!'()*]/g;
var encodeReserveReplacer = function (c) { return '%' + c.charCodeAt(0).toString(16); };
var commaRE = /%2C/g;

// fixed encodeURIComponent which is more conformant to RFC3986:
// - escapes [!'()*]
// - preserve commas
var encode = function (str) { return encodeURIComponent(str)
  .replace(encodeReserveRE, encodeReserveReplacer)
  .replace(commaRE, ','); };

var decode = decodeURIComponent;

function resolveQuery (
  query,
  extraQuery,
  _parseQuery
) {
  if ( extraQuery === void 0 ) extraQuery = {};

  var parse = _parseQuery || parseQuery;
  var parsedQuery;
  try {
    parsedQuery = parse(query || '');
  } catch (e) {
    "production" !== 'production' && warn(false, e.message);
    parsedQuery = {};
  }
  for (var key in extraQuery) {
    parsedQuery[key] = extraQuery[key];
  }
  return parsedQuery
}

function parseQuery (query) {
  var res = {};

  query = query.trim().replace(/^(\?|#|&)/, '');

  if (!query) {
    return res
  }

  query.split('&').forEach(function (param) {
    var parts = param.replace(/\+/g, ' ').split('=');
    var key = decode(parts.shift());
    var val = parts.length > 0
      ? decode(parts.join('='))
      : null;

    if (res[key] === undefined) {
      res[key] = val;
    } else if (Array.isArray(res[key])) {
      res[key].push(val);
    } else {
      res[key] = [res[key], val];
    }
  });

  return res
}

function stringifyQuery (obj) {
  var res = obj ? Object.keys(obj).map(function (key) {
    var val = obj[key];

    if (val === undefined) {
      return ''
    }

    if (val === null) {
      return encode(key)
    }

    if (Array.isArray(val)) {
      var result = [];
      val.forEach(function (val2) {
        if (val2 === undefined) {
          return
        }
        if (val2 === null) {
          result.push(encode(key));
        } else {
          result.push(encode(key) + '=' + encode(val2));
        }
      });
      return result.join('&')
    }

    return encode(key) + '=' + encode(val)
  }).filter(function (x) { return x.length > 0; }).join('&') : null;
  return res ? ("?" + res) : ''
}

/*  */


var trailingSlashRE = /\/?$/;

function createRoute (
  record,
  location,
  redirectedFrom,
  router
) {
  var stringifyQuery$$1 = router && router.options.stringifyQuery;

  var query = location.query || {};
  try {
    query = clone(query);
  } catch (e) {}

  var route = {
    name: location.name || (record && record.name),
    meta: (record && record.meta) || {},
    path: location.path || '/',
    hash: location.hash || '',
    query: query,
    params: location.params || {},
    fullPath: getFullPath(location, stringifyQuery$$1),
    matched: record ? formatMatch(record) : []
  };
  if (redirectedFrom) {
    route.redirectedFrom = getFullPath(redirectedFrom, stringifyQuery$$1);
  }
  return Object.freeze(route)
}

function clone (value) {
  if (Array.isArray(value)) {
    return value.map(clone)
  } else if (value && typeof value === 'object') {
    var res = {};
    for (var key in value) {
      res[key] = clone(value[key]);
    }
    return res
  } else {
    return value
  }
}

// the starting route that represents the initial state
var START = createRoute(null, {
  path: '/'
});

function formatMatch (record) {
  var res = [];
  while (record) {
    res.unshift(record);
    record = record.parent;
  }
  return res
}

function getFullPath (
  ref,
  _stringifyQuery
) {
  var path = ref.path;
  var query = ref.query; if ( query === void 0 ) query = {};
  var hash = ref.hash; if ( hash === void 0 ) hash = '';

  var stringify = _stringifyQuery || stringifyQuery;
  return (path || '/') + stringify(query) + hash
}

function isSameRoute (a, b) {
  if (b === START) {
    return a === b
  } else if (!b) {
    return false
  } else if (a.path && b.path) {
    return (
      a.path.replace(trailingSlashRE, '') === b.path.replace(trailingSlashRE, '') &&
      a.hash === b.hash &&
      isObjectEqual(a.query, b.query)
    )
  } else if (a.name && b.name) {
    return (
      a.name === b.name &&
      a.hash === b.hash &&
      isObjectEqual(a.query, b.query) &&
      isObjectEqual(a.params, b.params)
    )
  } else {
    return false
  }
}

function isObjectEqual (a, b) {
  if ( a === void 0 ) a = {};
  if ( b === void 0 ) b = {};

  // handle null value #1566
  if (!a || !b) { return a === b }
  var aKeys = Object.keys(a);
  var bKeys = Object.keys(b);
  if (aKeys.length !== bKeys.length) {
    return false
  }
  return aKeys.every(function (key) {
    var aVal = a[key];
    var bVal = b[key];
    // check nested equality
    if (typeof aVal === 'object' && typeof bVal === 'object') {
      return isObjectEqual(aVal, bVal)
    }
    return String(aVal) === String(bVal)
  })
}

function isIncludedRoute (current, target) {
  return (
    current.path.replace(trailingSlashRE, '/').indexOf(
      target.path.replace(trailingSlashRE, '/')
    ) === 0 &&
    (!target.hash || current.hash === target.hash) &&
    queryIncludes(current.query, target.query)
  )
}

function queryIncludes (current, target) {
  for (var key in target) {
    if (!(key in current)) {
      return false
    }
  }
  return true
}

/*  */

// work around weird flow bug
var toTypes = [String, Object];
var eventTypes = [String, Array];

var Link = {
  name: 'router-link',
  props: {
    to: {
      type: toTypes,
      required: true
    },
    tag: {
      type: String,
      default: 'a'
    },
    exact: Boolean,
    append: Boolean,
    replace: Boolean,
    activeClass: String,
    exactActiveClass: String,
    event: {
      type: eventTypes,
      default: 'click'
    }
  },
  render: function render (h) {
    var this$1 = this;

    var router = this.$router;
    var current = this.$route;
    var ref = router.resolve(this.to, current, this.append);
    var location = ref.location;
    var route = ref.route;
    var href = ref.href;

    var classes = {};
    var globalActiveClass = router.options.linkActiveClass;
    var globalExactActiveClass = router.options.linkExactActiveClass;
    // Support global empty active class
    var activeClassFallback = globalActiveClass == null
            ? 'router-link-active'
            : globalActiveClass;
    var exactActiveClassFallback = globalExactActiveClass == null
            ? 'router-link-exact-active'
            : globalExactActiveClass;
    var activeClass = this.activeClass == null
            ? activeClassFallback
            : this.activeClass;
    var exactActiveClass = this.exactActiveClass == null
            ? exactActiveClassFallback
            : this.exactActiveClass;
    var compareTarget = location.path
      ? createRoute(null, location, null, router)
      : route;

    classes[exactActiveClass] = isSameRoute(current, compareTarget);
    classes[activeClass] = this.exact
      ? classes[exactActiveClass]
      : isIncludedRoute(current, compareTarget);

    var handler = function (e) {
      if (guardEvent(e)) {
        if (this$1.replace) {
          router.replace(location);
        } else {
          router.push(location);
        }
      }
    };

    var on = { click: guardEvent };
    if (Array.isArray(this.event)) {
      this.event.forEach(function (e) { on[e] = handler; });
    } else {
      on[this.event] = handler;
    }

    var data = {
      class: classes
    };

    if (this.tag === 'a') {
      data.on = on;
      data.attrs = { href: href };
    } else {
      // find the first <a> child and apply listener and href
      var a = findAnchor(this.$slots.default);
      if (a) {
        // in case the <a> is a static node
        a.isStatic = false;
        var extend = _Vue.util.extend;
        var aData = a.data = extend({}, a.data);
        aData.on = on;
        var aAttrs = a.data.attrs = extend({}, a.data.attrs);
        aAttrs.href = href;
      } else {
        // doesn't have <a> child, apply listener to self
        data.on = on;
      }
    }

    return h(this.tag, data, this.$slots.default)
  }
};

function guardEvent (e) {
  // don't redirect with control keys
  if (e.metaKey || e.altKey || e.ctrlKey || e.shiftKey) { return }
  // don't redirect when preventDefault called
  if (e.defaultPrevented) { return }
  // don't redirect on right click
  if (e.button !== undefined && e.button !== 0) { return }
  // don't redirect if `target="_blank"`
  if (e.currentTarget && e.currentTarget.getAttribute) {
    var target = e.currentTarget.getAttribute('target');
    if (/\b_blank\b/i.test(target)) { return }
  }
  // this may be a Weex event which doesn't have this method
  if (e.preventDefault) {
    e.preventDefault();
  }
  return true
}

function findAnchor (children) {
  if (children) {
    var child;
    for (var i = 0; i < children.length; i++) {
      child = children[i];
      if (child.tag === 'a') {
        return child
      }
      if (child.children && (child = findAnchor(child.children))) {
        return child
      }
    }
  }
}

var _Vue;

function install (Vue) {
  if (install.installed && _Vue === Vue) { return }
  install.installed = true;

  _Vue = Vue;

  var isDef = function (v) { return v !== undefined; };

  var registerInstance = function (vm, callVal) {
    var i = vm.$options._parentVnode;
    if (isDef(i) && isDef(i = i.data) && isDef(i = i.registerRouteInstance)) {
      i(vm, callVal);
    }
  };

  Vue.mixin({
    beforeCreate: function beforeCreate () {
      if (isDef(this.$options.router)) {
        this._routerRoot = this;
        this._router = this.$options.router;
        this._router.init(this);
        Vue.util.defineReactive(this, '_route', this._router.history.current);
      } else {
        this._routerRoot = (this.$parent && this.$parent._routerRoot) || this;
      }
      registerInstance(this, this);
    },
    destroyed: function destroyed () {
      registerInstance(this);
    }
  });

  Object.defineProperty(Vue.prototype, '$router', {
    get: function get () { return this._routerRoot._router }
  });

  Object.defineProperty(Vue.prototype, '$route', {
    get: function get () { return this._routerRoot._route }
  });

  Vue.component('router-view', View);
  Vue.component('router-link', Link);

  var strats = Vue.config.optionMergeStrategies;
  // use the same hook merging strategy for route hooks
  strats.beforeRouteEnter = strats.beforeRouteLeave = strats.beforeRouteUpdate = strats.created;
}

/*  */

var inBrowser = typeof window !== 'undefined';

/*  */

function resolvePath (
  relative,
  base,
  append
) {
  var firstChar = relative.charAt(0);
  if (firstChar === '/') {
    return relative
  }

  if (firstChar === '?' || firstChar === '#') {
    return base + relative
  }

  var stack = base.split('/');

  // remove trailing segment if:
  // - not appending
  // - appending to trailing slash (last segment is empty)
  if (!append || !stack[stack.length - 1]) {
    stack.pop();
  }

  // resolve relative path
  var segments = relative.replace(/^\//, '').split('/');
  for (var i = 0; i < segments.length; i++) {
    var segment = segments[i];
    if (segment === '..') {
      stack.pop();
    } else if (segment !== '.') {
      stack.push(segment);
    }
  }

  // ensure leading slash
  if (stack[0] !== '') {
    stack.unshift('');
  }

  return stack.join('/')
}

function parsePath (path) {
  var hash = '';
  var query = '';

  var hashIndex = path.indexOf('#');
  if (hashIndex >= 0) {
    hash = path.slice(hashIndex);
    path = path.slice(0, hashIndex);
  }

  var queryIndex = path.indexOf('?');
  if (queryIndex >= 0) {
    query = path.slice(queryIndex + 1);
    path = path.slice(0, queryIndex);
  }

  return {
    path: path,
    query: query,
    hash: hash
  }
}

function cleanPath (path) {
  return path.replace(/\/\//g, '/')
}

var isarray = Array.isArray || function (arr) {
  return Object.prototype.toString.call(arr) == '[object Array]';
};

/**
 * Expose `pathToRegexp`.
 */
var pathToRegexp_1 = pathToRegexp;
var parse_1 = parse;
var compile_1 = compile;
var tokensToFunction_1 = tokensToFunction;
var tokensToRegExp_1 = tokensToRegExp;

/**
 * The main path matching regexp utility.
 *
 * @type {RegExp}
 */
var PATH_REGEXP = new RegExp([
  // Match escaped characters that would otherwise appear in future matches.
  // This allows the user to escape special characters that won't transform.
  '(\\\\.)',
  // Match Express-style parameters and un-named parameters with a prefix
  // and optional suffixes. Matches appear as:
  //
  // "/:test(\\d+)?" => ["/", "test", "\d+", undefined, "?", undefined]
  // "/route(\\d+)"  => [undefined, undefined, undefined, "\d+", undefined, undefined]
  // "/*"            => ["/", undefined, undefined, undefined, undefined, "*"]
  '([\\/.])?(?:(?:\\:(\\w+)(?:\\(((?:\\\\.|[^\\\\()])+)\\))?|\\(((?:\\\\.|[^\\\\()])+)\\))([+*?])?|(\\*))'
].join('|'), 'g');

/**
 * Parse a string for the raw tokens.
 *
 * @param  {string}  str
 * @param  {Object=} options
 * @return {!Array}
 */
function parse (str, options) {
  var tokens = [];
  var key = 0;
  var index = 0;
  var path = '';
  var defaultDelimiter = options && options.delimiter || '/';
  var res;

  while ((res = PATH_REGEXP.exec(str)) != null) {
    var m = res[0];
    var escaped = res[1];
    var offset = res.index;
    path += str.slice(index, offset);
    index = offset + m.length;

    // Ignore already escaped sequences.
    if (escaped) {
      path += escaped[1];
      continue
    }

    var next = str[index];
    var prefix = res[2];
    var name = res[3];
    var capture = res[4];
    var group = res[5];
    var modifier = res[6];
    var asterisk = res[7];

    // Push the current path onto the tokens.
    if (path) {
      tokens.push(path);
      path = '';
    }

    var partial = prefix != null && next != null && next !== prefix;
    var repeat = modifier === '+' || modifier === '*';
    var optional = modifier === '?' || modifier === '*';
    var delimiter = res[2] || defaultDelimiter;
    var pattern = capture || group;

    tokens.push({
      name: name || key++,
      prefix: prefix || '',
      delimiter: delimiter,
      optional: optional,
      repeat: repeat,
      partial: partial,
      asterisk: !!asterisk,
      pattern: pattern ? escapeGroup(pattern) : (asterisk ? '.*' : '[^' + escapeString(delimiter) + ']+?')
    });
  }

  // Match any characters still remaining.
  if (index < str.length) {
    path += str.substr(index);
  }

  // If the path exists, push it onto the end.
  if (path) {
    tokens.push(path);
  }

  return tokens
}

/**
 * Compile a string to a template function for the path.
 *
 * @param  {string}             str
 * @param  {Object=}            options
 * @return {!function(Object=, Object=)}
 */
function compile (str, options) {
  return tokensToFunction(parse(str, options))
}

/**
 * Prettier encoding of URI path segments.
 *
 * @param  {string}
 * @return {string}
 */
function encodeURIComponentPretty (str) {
  return encodeURI(str).replace(/[\/?#]/g, function (c) {
    return '%' + c.charCodeAt(0).toString(16).toUpperCase()
  })
}

/**
 * Encode the asterisk parameter. Similar to `pretty`, but allows slashes.
 *
 * @param  {string}
 * @return {string}
 */
function encodeAsterisk (str) {
  return encodeURI(str).replace(/[?#]/g, function (c) {
    return '%' + c.charCodeAt(0).toString(16).toUpperCase()
  })
}

/**
 * Expose a method for transforming tokens into the path function.
 */
function tokensToFunction (tokens) {
  // Compile all the tokens into regexps.
  var matches = new Array(tokens.length);

  // Compile all the patterns before compilation.
  for (var i = 0; i < tokens.length; i++) {
    if (typeof tokens[i] === 'object') {
      matches[i] = new RegExp('^(?:' + tokens[i].pattern + ')$');
    }
  }

  return function (obj, opts) {
    var path = '';
    var data = obj || {};
    var options = opts || {};
    var encode = options.pretty ? encodeURIComponentPretty : encodeURIComponent;

    for (var i = 0; i < tokens.length; i++) {
      var token = tokens[i];

      if (typeof token === 'string') {
        path += token;

        continue
      }

      var value = data[token.name];
      var segment;

      if (value == null) {
        if (token.optional) {
          // Prepend partial segment prefixes.
          if (token.partial) {
            path += token.prefix;
          }

          continue
        } else {
          throw new TypeError('Expected "' + token.name + '" to be defined')
        }
      }

      if (isarray(value)) {
        if (!token.repeat) {
          throw new TypeError('Expected "' + token.name + '" to not repeat, but received `' + JSON.stringify(value) + '`')
        }

        if (value.length === 0) {
          if (token.optional) {
            continue
          } else {
            throw new TypeError('Expected "' + token.name + '" to not be empty')
          }
        }

        for (var j = 0; j < value.length; j++) {
          segment = encode(value[j]);

          if (!matches[i].test(segment)) {
            throw new TypeError('Expected all "' + token.name + '" to match "' + token.pattern + '", but received `' + JSON.stringify(segment) + '`')
          }

          path += (j === 0 ? token.prefix : token.delimiter) + segment;
        }

        continue
      }

      segment = token.asterisk ? encodeAsterisk(value) : encode(value);

      if (!matches[i].test(segment)) {
        throw new TypeError('Expected "' + token.name + '" to match "' + token.pattern + '", but received "' + segment + '"')
      }

      path += token.prefix + segment;
    }

    return path
  }
}

/**
 * Escape a regular expression string.
 *
 * @param  {string} str
 * @return {string}
 */
function escapeString (str) {
  return str.replace(/([.+*?=^!:${}()[\]|\/\\])/g, '\\$1')
}

/**
 * Escape the capturing group by escaping special characters and meaning.
 *
 * @param  {string} group
 * @return {string}
 */
function escapeGroup (group) {
  return group.replace(/([=!:$\/()])/g, '\\$1')
}

/**
 * Attach the keys as a property of the regexp.
 *
 * @param  {!RegExp} re
 * @param  {Array}   keys
 * @return {!RegExp}
 */
function attachKeys (re, keys) {
  re.keys = keys;
  return re
}

/**
 * Get the flags for a regexp from the options.
 *
 * @param  {Object} options
 * @return {string}
 */
function flags (options) {
  return options.sensitive ? '' : 'i'
}

/**
 * Pull out keys from a regexp.
 *
 * @param  {!RegExp} path
 * @param  {!Array}  keys
 * @return {!RegExp}
 */
function regexpToRegexp (path, keys) {
  // Use a negative lookahead to match only capturing groups.
  var groups = path.source.match(/\((?!\?)/g);

  if (groups) {
    for (var i = 0; i < groups.length; i++) {
      keys.push({
        name: i,
        prefix: null,
        delimiter: null,
        optional: false,
        repeat: false,
        partial: false,
        asterisk: false,
        pattern: null
      });
    }
  }

  return attachKeys(path, keys)
}

/**
 * Transform an array into a regexp.
 *
 * @param  {!Array}  path
 * @param  {Array}   keys
 * @param  {!Object} options
 * @return {!RegExp}
 */
function arrayToRegexp (path, keys, options) {
  var parts = [];

  for (var i = 0; i < path.length; i++) {
    parts.push(pathToRegexp(path[i], keys, options).source);
  }

  var regexp = new RegExp('(?:' + parts.join('|') + ')', flags(options));

  return attachKeys(regexp, keys)
}

/**
 * Create a path regexp from string input.
 *
 * @param  {string}  path
 * @param  {!Array}  keys
 * @param  {!Object} options
 * @return {!RegExp}
 */
function stringToRegexp (path, keys, options) {
  return tokensToRegExp(parse(path, options), keys, options)
}

/**
 * Expose a function for taking tokens and returning a RegExp.
 *
 * @param  {!Array}          tokens
 * @param  {(Array|Object)=} keys
 * @param  {Object=}         options
 * @return {!RegExp}
 */
function tokensToRegExp (tokens, keys, options) {
  if (!isarray(keys)) {
    options = /** @type {!Object} */ (keys || options);
    keys = [];
  }

  options = options || {};

  var strict = options.strict;
  var end = options.end !== false;
  var route = '';

  // Iterate over the tokens and create our regexp string.
  for (var i = 0; i < tokens.length; i++) {
    var token = tokens[i];

    if (typeof token === 'string') {
      route += escapeString(token);
    } else {
      var prefix = escapeString(token.prefix);
      var capture = '(?:' + token.pattern + ')';

      keys.push(token);

      if (token.repeat) {
        capture += '(?:' + prefix + capture + ')*';
      }

      if (token.optional) {
        if (!token.partial) {
          capture = '(?:' + prefix + '(' + capture + '))?';
        } else {
          capture = prefix + '(' + capture + ')?';
        }
      } else {
        capture = prefix + '(' + capture + ')';
      }

      route += capture;
    }
  }

  var delimiter = escapeString(options.delimiter || '/');
  var endsWithDelimiter = route.slice(-delimiter.length) === delimiter;

  // In non-strict mode we allow a slash at the end of match. If the path to
  // match already ends with a slash, we remove it for consistency. The slash
  // is valid at the end of a path match, not in the middle. This is important
  // in non-ending mode, where "/test/" shouldn't match "/test//route".
  if (!strict) {
    route = (endsWithDelimiter ? route.slice(0, -delimiter.length) : route) + '(?:' + delimiter + '(?=$))?';
  }

  if (end) {
    route += '$';
  } else {
    // In non-ending mode, we need the capturing groups to match as much as
    // possible by using a positive lookahead to the end or next path segment.
    route += strict && endsWithDelimiter ? '' : '(?=' + delimiter + '|$)';
  }

  return attachKeys(new RegExp('^' + route, flags(options)), keys)
}

/**
 * Normalize the given path string, returning a regular expression.
 *
 * An empty array can be passed in for the keys, which will hold the
 * placeholder key descriptions. For example, using `/user/:id`, `keys` will
 * contain `[{ name: 'id', delimiter: '/', optional: false, repeat: false }]`.
 *
 * @param  {(string|RegExp|Array)} path
 * @param  {(Array|Object)=}       keys
 * @param  {Object=}               options
 * @return {!RegExp}
 */
function pathToRegexp (path, keys, options) {
  if (!isarray(keys)) {
    options = /** @type {!Object} */ (keys || options);
    keys = [];
  }

  options = options || {};

  if (path instanceof RegExp) {
    return regexpToRegexp(path, /** @type {!Array} */ (keys))
  }

  if (isarray(path)) {
    return arrayToRegexp(/** @type {!Array} */ (path), /** @type {!Array} */ (keys), options)
  }

  return stringToRegexp(/** @type {string} */ (path), /** @type {!Array} */ (keys), options)
}

pathToRegexp_1.parse = parse_1;
pathToRegexp_1.compile = compile_1;
pathToRegexp_1.tokensToFunction = tokensToFunction_1;
pathToRegexp_1.tokensToRegExp = tokensToRegExp_1;

/*  */

// $flow-disable-line
var regexpCompileCache = Object.create(null);

function fillParams (
  path,
  params,
  routeMsg
) {
  try {
    var filler =
      regexpCompileCache[path] ||
      (regexpCompileCache[path] = pathToRegexp_1.compile(path));
    return filler(params || {}, { pretty: true })
  } catch (e) {
    if (false) {
      warn(false, ("missing param for " + routeMsg + ": " + (e.message)));
    }
    return ''
  }
}

/*  */

function createRouteMap (
  routes,
  oldPathList,
  oldPathMap,
  oldNameMap
) {
  // the path list is used to control path matching priority
  var pathList = oldPathList || [];
  // $flow-disable-line
  var pathMap = oldPathMap || Object.create(null);
  // $flow-disable-line
  var nameMap = oldNameMap || Object.create(null);

  routes.forEach(function (route) {
    addRouteRecord(pathList, pathMap, nameMap, route);
  });

  // ensure wildcard routes are always at the end
  for (var i = 0, l = pathList.length; i < l; i++) {
    if (pathList[i] === '*') {
      pathList.push(pathList.splice(i, 1)[0]);
      l--;
      i--;
    }
  }

  return {
    pathList: pathList,
    pathMap: pathMap,
    nameMap: nameMap
  }
}

function addRouteRecord (
  pathList,
  pathMap,
  nameMap,
  route,
  parent,
  matchAs
) {
  var path = route.path;
  var name = route.name;
  if (false) {
    assert(path != null, "\"path\" is required in a route configuration.");
    assert(
      typeof route.component !== 'string',
      "route config \"component\" for path: " + (String(path || name)) + " cannot be a " +
      "string id. Use an actual component instead."
    );
  }

  var pathToRegexpOptions = route.pathToRegexpOptions || {};
  var normalizedPath = normalizePath(
    path,
    parent,
    pathToRegexpOptions.strict
  );

  if (typeof route.caseSensitive === 'boolean') {
    pathToRegexpOptions.sensitive = route.caseSensitive;
  }

  var record = {
    path: normalizedPath,
    regex: compileRouteRegex(normalizedPath, pathToRegexpOptions),
    components: route.components || { default: route.component },
    instances: {},
    name: name,
    parent: parent,
    matchAs: matchAs,
    redirect: route.redirect,
    beforeEnter: route.beforeEnter,
    meta: route.meta || {},
    props: route.props == null
      ? {}
      : route.components
        ? route.props
        : { default: route.props }
  };

  if (route.children) {
    // Warn if route is named, does not redirect and has a default child route.
    // If users navigate to this route by name, the default child will
    // not be rendered (GH Issue #629)
    if (false) {
      if (route.name && !route.redirect && route.children.some(function (child) { return /^\/?$/.test(child.path); })) {
        warn(
          false,
          "Named Route '" + (route.name) + "' has a default child route. " +
          "When navigating to this named route (:to=\"{name: '" + (route.name) + "'\"), " +
          "the default child route will not be rendered. Remove the name from " +
          "this route and use the name of the default child route for named " +
          "links instead."
        );
      }
    }
    route.children.forEach(function (child) {
      var childMatchAs = matchAs
        ? cleanPath((matchAs + "/" + (child.path)))
        : undefined;
      addRouteRecord(pathList, pathMap, nameMap, child, record, childMatchAs);
    });
  }

  if (route.alias !== undefined) {
    var aliases = Array.isArray(route.alias)
      ? route.alias
      : [route.alias];

    aliases.forEach(function (alias) {
      var aliasRoute = {
        path: alias,
        children: route.children
      };
      addRouteRecord(
        pathList,
        pathMap,
        nameMap,
        aliasRoute,
        parent,
        record.path || '/' // matchAs
      );
    });
  }

  if (!pathMap[record.path]) {
    pathList.push(record.path);
    pathMap[record.path] = record;
  }

  if (name) {
    if (!nameMap[name]) {
      nameMap[name] = record;
    } else if (false) {
      warn(
        false,
        "Duplicate named routes definition: " +
        "{ name: \"" + name + "\", path: \"" + (record.path) + "\" }"
      );
    }
  }
}

function compileRouteRegex (path, pathToRegexpOptions) {
  var regex = pathToRegexp_1(path, [], pathToRegexpOptions);
  if (false) {
    var keys = Object.create(null);
    regex.keys.forEach(function (key) {
      warn(!keys[key.name], ("Duplicate param keys in route with path: \"" + path + "\""));
      keys[key.name] = true;
    });
  }
  return regex
}

function normalizePath (path, parent, strict) {
  if (!strict) { path = path.replace(/\/$/, ''); }
  if (path[0] === '/') { return path }
  if (parent == null) { return path }
  return cleanPath(((parent.path) + "/" + path))
}

/*  */


function normalizeLocation (
  raw,
  current,
  append,
  router
) {
  var next = typeof raw === 'string' ? { path: raw } : raw;
  // named target
  if (next.name || next._normalized) {
    return next
  }

  // relative params
  if (!next.path && next.params && current) {
    next = assign({}, next);
    next._normalized = true;
    var params = assign(assign({}, current.params), next.params);
    if (current.name) {
      next.name = current.name;
      next.params = params;
    } else if (current.matched.length) {
      var rawPath = current.matched[current.matched.length - 1].path;
      next.path = fillParams(rawPath, params, ("path " + (current.path)));
    } else if (false) {
      warn(false, "relative params navigation requires a current route.");
    }
    return next
  }

  var parsedPath = parsePath(next.path || '');
  var basePath = (current && current.path) || '/';
  var path = parsedPath.path
    ? resolvePath(parsedPath.path, basePath, append || next.append)
    : basePath;

  var query = resolveQuery(
    parsedPath.query,
    next.query,
    router && router.options.parseQuery
  );

  var hash = next.hash || parsedPath.hash;
  if (hash && hash.charAt(0) !== '#') {
    hash = "#" + hash;
  }

  return {
    _normalized: true,
    path: path,
    query: query,
    hash: hash
  }
}

function assign (a, b) {
  for (var key in b) {
    a[key] = b[key];
  }
  return a
}

/*  */


function createMatcher (
  routes,
  router
) {
  var ref = createRouteMap(routes);
  var pathList = ref.pathList;
  var pathMap = ref.pathMap;
  var nameMap = ref.nameMap;

  function addRoutes (routes) {
    createRouteMap(routes, pathList, pathMap, nameMap);
  }

  function match (
    raw,
    currentRoute,
    redirectedFrom
  ) {
    var location = normalizeLocation(raw, currentRoute, false, router);
    var name = location.name;

    if (name) {
      var record = nameMap[name];
      if (false) {
        warn(record, ("Route with name '" + name + "' does not exist"));
      }
      if (!record) { return _createRoute(null, location) }
      var paramNames = record.regex.keys
        .filter(function (key) { return !key.optional; })
        .map(function (key) { return key.name; });

      if (typeof location.params !== 'object') {
        location.params = {};
      }

      if (currentRoute && typeof currentRoute.params === 'object') {
        for (var key in currentRoute.params) {
          if (!(key in location.params) && paramNames.indexOf(key) > -1) {
            location.params[key] = currentRoute.params[key];
          }
        }
      }

      if (record) {
        location.path = fillParams(record.path, location.params, ("named route \"" + name + "\""));
        return _createRoute(record, location, redirectedFrom)
      }
    } else if (location.path) {
      location.params = {};
      for (var i = 0; i < pathList.length; i++) {
        var path = pathList[i];
        var record$1 = pathMap[path];
        if (matchRoute(record$1.regex, location.path, location.params)) {
          return _createRoute(record$1, location, redirectedFrom)
        }
      }
    }
    // no match
    return _createRoute(null, location)
  }

  function redirect (
    record,
    location
  ) {
    var originalRedirect = record.redirect;
    var redirect = typeof originalRedirect === 'function'
        ? originalRedirect(createRoute(record, location, null, router))
        : originalRedirect;

    if (typeof redirect === 'string') {
      redirect = { path: redirect };
    }

    if (!redirect || typeof redirect !== 'object') {
      if (false) {
        warn(
          false, ("invalid redirect option: " + (JSON.stringify(redirect)))
        );
      }
      return _createRoute(null, location)
    }

    var re = redirect;
    var name = re.name;
    var path = re.path;
    var query = location.query;
    var hash = location.hash;
    var params = location.params;
    query = re.hasOwnProperty('query') ? re.query : query;
    hash = re.hasOwnProperty('hash') ? re.hash : hash;
    params = re.hasOwnProperty('params') ? re.params : params;

    if (name) {
      // resolved named direct
      var targetRecord = nameMap[name];
      if (false) {
        assert(targetRecord, ("redirect failed: named route \"" + name + "\" not found."));
      }
      return match({
        _normalized: true,
        name: name,
        query: query,
        hash: hash,
        params: params
      }, undefined, location)
    } else if (path) {
      // 1. resolve relative redirect
      var rawPath = resolveRecordPath(path, record);
      // 2. resolve params
      var resolvedPath = fillParams(rawPath, params, ("redirect route with path \"" + rawPath + "\""));
      // 3. rematch with existing query and hash
      return match({
        _normalized: true,
        path: resolvedPath,
        query: query,
        hash: hash
      }, undefined, location)
    } else {
      if (false) {
        warn(false, ("invalid redirect option: " + (JSON.stringify(redirect))));
      }
      return _createRoute(null, location)
    }
  }

  function alias (
    record,
    location,
    matchAs
  ) {
    var aliasedPath = fillParams(matchAs, location.params, ("aliased route with path \"" + matchAs + "\""));
    var aliasedMatch = match({
      _normalized: true,
      path: aliasedPath
    });
    if (aliasedMatch) {
      var matched = aliasedMatch.matched;
      var aliasedRecord = matched[matched.length - 1];
      location.params = aliasedMatch.params;
      return _createRoute(aliasedRecord, location)
    }
    return _createRoute(null, location)
  }

  function _createRoute (
    record,
    location,
    redirectedFrom
  ) {
    if (record && record.redirect) {
      return redirect(record, redirectedFrom || location)
    }
    if (record && record.matchAs) {
      return alias(record, location, record.matchAs)
    }
    return createRoute(record, location, redirectedFrom, router)
  }

  return {
    match: match,
    addRoutes: addRoutes
  }
}

function matchRoute (
  regex,
  path,
  params
) {
  var m = path.match(regex);

  if (!m) {
    return false
  } else if (!params) {
    return true
  }

  for (var i = 1, len = m.length; i < len; ++i) {
    var key = regex.keys[i - 1];
    var val = typeof m[i] === 'string' ? decodeURIComponent(m[i]) : m[i];
    if (key) {
      params[key.name] = val;
    }
  }

  return true
}

function resolveRecordPath (path, record) {
  return resolvePath(path, record.parent ? record.parent.path : '/', true)
}

/*  */


var positionStore = Object.create(null);

function setupScroll () {
  // Fix for #1585 for Firefox
  window.history.replaceState({ key: getStateKey() }, '');
  window.addEventListener('popstate', function (e) {
    saveScrollPosition();
    if (e.state && e.state.key) {
      setStateKey(e.state.key);
    }
  });
}

function handleScroll (
  router,
  to,
  from,
  isPop
) {
  if (!router.app) {
    return
  }

  var behavior = router.options.scrollBehavior;
  if (!behavior) {
    return
  }

  if (false) {
    assert(typeof behavior === 'function', "scrollBehavior must be a function");
  }

  // wait until re-render finishes before scrolling
  router.app.$nextTick(function () {
    var position = getScrollPosition();
    var shouldScroll = behavior(to, from, isPop ? position : null);

    if (!shouldScroll) {
      return
    }

    if (typeof shouldScroll.then === 'function') {
      shouldScroll.then(function (shouldScroll) {
        scrollToPosition((shouldScroll), position);
      }).catch(function (err) {
        if (false) {
          assert(false, err.toString());
        }
      });
    } else {
      scrollToPosition(shouldScroll, position);
    }
  });
}

function saveScrollPosition () {
  var key = getStateKey();
  if (key) {
    positionStore[key] = {
      x: window.pageXOffset,
      y: window.pageYOffset
    };
  }
}

function getScrollPosition () {
  var key = getStateKey();
  if (key) {
    return positionStore[key]
  }
}

function getElementPosition (el, offset) {
  var docEl = document.documentElement;
  var docRect = docEl.getBoundingClientRect();
  var elRect = el.getBoundingClientRect();
  return {
    x: elRect.left - docRect.left - offset.x,
    y: elRect.top - docRect.top - offset.y
  }
}

function isValidPosition (obj) {
  return isNumber(obj.x) || isNumber(obj.y)
}

function normalizePosition (obj) {
  return {
    x: isNumber(obj.x) ? obj.x : window.pageXOffset,
    y: isNumber(obj.y) ? obj.y : window.pageYOffset
  }
}

function normalizeOffset (obj) {
  return {
    x: isNumber(obj.x) ? obj.x : 0,
    y: isNumber(obj.y) ? obj.y : 0
  }
}

function isNumber (v) {
  return typeof v === 'number'
}

function scrollToPosition (shouldScroll, position) {
  var isObject = typeof shouldScroll === 'object';
  if (isObject && typeof shouldScroll.selector === 'string') {
    var el = document.querySelector(shouldScroll.selector);
    if (el) {
      var offset = shouldScroll.offset && typeof shouldScroll.offset === 'object' ? shouldScroll.offset : {};
      offset = normalizeOffset(offset);
      position = getElementPosition(el, offset);
    } else if (isValidPosition(shouldScroll)) {
      position = normalizePosition(shouldScroll);
    }
  } else if (isObject && isValidPosition(shouldScroll)) {
    position = normalizePosition(shouldScroll);
  }

  if (position) {
    window.scrollTo(position.x, position.y);
  }
}

/*  */

var supportsPushState = inBrowser && (function () {
  var ua = window.navigator.userAgent;

  if (
    (ua.indexOf('Android 2.') !== -1 || ua.indexOf('Android 4.0') !== -1) &&
    ua.indexOf('Mobile Safari') !== -1 &&
    ua.indexOf('Chrome') === -1 &&
    ua.indexOf('Windows Phone') === -1
  ) {
    return false
  }

  return window.history && 'pushState' in window.history
})();

// use User Timing api (if present) for more accurate key precision
var Time = inBrowser && window.performance && window.performance.now
  ? window.performance
  : Date;

var _key = genKey();

function genKey () {
  return Time.now().toFixed(3)
}

function getStateKey () {
  return _key
}

function setStateKey (key) {
  _key = key;
}

function pushState (url, replace) {
  saveScrollPosition();
  // try...catch the pushState call to get around Safari
  // DOM Exception 18 where it limits to 100 pushState calls
  var history = window.history;
  try {
    if (replace) {
      history.replaceState({ key: _key }, '', url);
    } else {
      _key = genKey();
      history.pushState({ key: _key }, '', url);
    }
  } catch (e) {
    window.location[replace ? 'replace' : 'assign'](url);
  }
}

function replaceState (url) {
  pushState(url, true);
}

/*  */

function runQueue (queue, fn, cb) {
  var step = function (index) {
    if (index >= queue.length) {
      cb();
    } else {
      if (queue[index]) {
        fn(queue[index], function () {
          step(index + 1);
        });
      } else {
        step(index + 1);
      }
    }
  };
  step(0);
}

/*  */

function resolveAsyncComponents (matched) {
  return function (to, from, next) {
    var hasAsync = false;
    var pending = 0;
    var error = null;

    flatMapComponents(matched, function (def, _, match, key) {
      // if it's a function and doesn't have cid attached,
      // assume it's an async component resolve function.
      // we are not using Vue's default async resolving mechanism because
      // we want to halt the navigation until the incoming component has been
      // resolved.
      if (typeof def === 'function' && def.cid === undefined) {
        hasAsync = true;
        pending++;

        var resolve = once(function (resolvedDef) {
          if (isESModule(resolvedDef)) {
            resolvedDef = resolvedDef.default;
          }
          // save resolved on async factory in case it's used elsewhere
          def.resolved = typeof resolvedDef === 'function'
            ? resolvedDef
            : _Vue.extend(resolvedDef);
          match.components[key] = resolvedDef;
          pending--;
          if (pending <= 0) {
            next();
          }
        });

        var reject = once(function (reason) {
          var msg = "Failed to resolve async component " + key + ": " + reason;
          "production" !== 'production' && warn(false, msg);
          if (!error) {
            error = isError(reason)
              ? reason
              : new Error(msg);
            next(error);
          }
        });

        var res;
        try {
          res = def(resolve, reject);
        } catch (e) {
          reject(e);
        }
        if (res) {
          if (typeof res.then === 'function') {
            res.then(resolve, reject);
          } else {
            // new syntax in Vue 2.3
            var comp = res.component;
            if (comp && typeof comp.then === 'function') {
              comp.then(resolve, reject);
            }
          }
        }
      }
    });

    if (!hasAsync) { next(); }
  }
}

function flatMapComponents (
  matched,
  fn
) {
  return flatten(matched.map(function (m) {
    return Object.keys(m.components).map(function (key) { return fn(
      m.components[key],
      m.instances[key],
      m, key
    ); })
  }))
}

function flatten (arr) {
  return Array.prototype.concat.apply([], arr)
}

var hasSymbol =
  typeof Symbol === 'function' &&
  typeof Symbol.toStringTag === 'symbol';

function isESModule (obj) {
  return obj.__esModule || (hasSymbol && obj[Symbol.toStringTag] === 'Module')
}

// in Webpack 2, require.ensure now also returns a Promise
// so the resolve/reject functions may get called an extra time
// if the user uses an arrow function shorthand that happens to
// return that Promise.
function once (fn) {
  var called = false;
  return function () {
    var args = [], len = arguments.length;
    while ( len-- ) args[ len ] = arguments[ len ];

    if (called) { return }
    called = true;
    return fn.apply(this, args)
  }
}

/*  */

var History = function History (router, base) {
  this.router = router;
  this.base = normalizeBase(base);
  // start with a route object that stands for "nowhere"
  this.current = START;
  this.pending = null;
  this.ready = false;
  this.readyCbs = [];
  this.readyErrorCbs = [];
  this.errorCbs = [];
};

History.prototype.listen = function listen (cb) {
  this.cb = cb;
};

History.prototype.onReady = function onReady (cb, errorCb) {
  if (this.ready) {
    cb();
  } else {
    this.readyCbs.push(cb);
    if (errorCb) {
      this.readyErrorCbs.push(errorCb);
    }
  }
};

History.prototype.onError = function onError (errorCb) {
  this.errorCbs.push(errorCb);
};

History.prototype.transitionTo = function transitionTo (location, onComplete, onAbort) {
    var this$1 = this;

  var route = this.router.match(location, this.current);
  this.confirmTransition(route, function () {
    this$1.updateRoute(route);
    onComplete && onComplete(route);
    this$1.ensureURL();

    // fire ready cbs once
    if (!this$1.ready) {
      this$1.ready = true;
      this$1.readyCbs.forEach(function (cb) { cb(route); });
    }
  }, function (err) {
    if (onAbort) {
      onAbort(err);
    }
    if (err && !this$1.ready) {
      this$1.ready = true;
      this$1.readyErrorCbs.forEach(function (cb) { cb(err); });
    }
  });
};

History.prototype.confirmTransition = function confirmTransition (route, onComplete, onAbort) {
    var this$1 = this;

  var current = this.current;
  var abort = function (err) {
    if (isError(err)) {
      if (this$1.errorCbs.length) {
        this$1.errorCbs.forEach(function (cb) { cb(err); });
      } else {
        warn(false, 'uncaught error during route navigation:');
        console.error(err);
      }
    }
    onAbort && onAbort(err);
  };
  if (
    isSameRoute(route, current) &&
    // in the case the route map has been dynamically appended to
    route.matched.length === current.matched.length
  ) {
    this.ensureURL();
    return abort()
  }

  var ref = resolveQueue(this.current.matched, route.matched);
    var updated = ref.updated;
    var deactivated = ref.deactivated;
    var activated = ref.activated;

  var queue = [].concat(
    // in-component leave guards
    extractLeaveGuards(deactivated),
    // global before hooks
    this.router.beforeHooks,
    // in-component update hooks
    extractUpdateHooks(updated),
    // in-config enter guards
    activated.map(function (m) { return m.beforeEnter; }),
    // async components
    resolveAsyncComponents(activated)
  );

  this.pending = route;
  var iterator = function (hook, next) {
    if (this$1.pending !== route) {
      return abort()
    }
    try {
      hook(route, current, function (to) {
        if (to === false || isError(to)) {
          // next(false) -> abort navigation, ensure current URL
          this$1.ensureURL(true);
          abort(to);
        } else if (
          typeof to === 'string' ||
          (typeof to === 'object' && (
            typeof to.path === 'string' ||
            typeof to.name === 'string'
          ))
        ) {
          // next('/') or next({ path: '/' }) -> redirect
          abort();
          if (typeof to === 'object' && to.replace) {
            this$1.replace(to);
          } else {
            this$1.push(to);
          }
        } else {
          // confirm transition and pass on the value
          next(to);
        }
      });
    } catch (e) {
      abort(e);
    }
  };

  runQueue(queue, iterator, function () {
    var postEnterCbs = [];
    var isValid = function () { return this$1.current === route; };
    // wait until async components are resolved before
    // extracting in-component enter guards
    var enterGuards = extractEnterGuards(activated, postEnterCbs, isValid);
    var queue = enterGuards.concat(this$1.router.resolveHooks);
    runQueue(queue, iterator, function () {
      if (this$1.pending !== route) {
        return abort()
      }
      this$1.pending = null;
      onComplete(route);
      if (this$1.router.app) {
        this$1.router.app.$nextTick(function () {
          postEnterCbs.forEach(function (cb) { cb(); });
        });
      }
    });
  });
};

History.prototype.updateRoute = function updateRoute (route) {
  var prev = this.current;
  this.current = route;
  this.cb && this.cb(route);
  this.router.afterHooks.forEach(function (hook) {
    hook && hook(route, prev);
  });
};

function normalizeBase (base) {
  if (!base) {
    if (inBrowser) {
      // respect <base> tag
      var baseEl = document.querySelector('base');
      base = (baseEl && baseEl.getAttribute('href')) || '/';
      // strip full URL origin
      base = base.replace(/^https?:\/\/[^\/]+/, '');
    } else {
      base = '/';
    }
  }
  // make sure there's the starting slash
  if (base.charAt(0) !== '/') {
    base = '/' + base;
  }
  // remove trailing slash
  return base.replace(/\/$/, '')
}

function resolveQueue (
  current,
  next
) {
  var i;
  var max = Math.max(current.length, next.length);
  for (i = 0; i < max; i++) {
    if (current[i] !== next[i]) {
      break
    }
  }
  return {
    updated: next.slice(0, i),
    activated: next.slice(i),
    deactivated: current.slice(i)
  }
}

function extractGuards (
  records,
  name,
  bind,
  reverse
) {
  var guards = flatMapComponents(records, function (def, instance, match, key) {
    var guard = extractGuard(def, name);
    if (guard) {
      return Array.isArray(guard)
        ? guard.map(function (guard) { return bind(guard, instance, match, key); })
        : bind(guard, instance, match, key)
    }
  });
  return flatten(reverse ? guards.reverse() : guards)
}

function extractGuard (
  def,
  key
) {
  if (typeof def !== 'function') {
    // extend now so that global mixins are applied.
    def = _Vue.extend(def);
  }
  return def.options[key]
}

function extractLeaveGuards (deactivated) {
  return extractGuards(deactivated, 'beforeRouteLeave', bindGuard, true)
}

function extractUpdateHooks (updated) {
  return extractGuards(updated, 'beforeRouteUpdate', bindGuard)
}

function bindGuard (guard, instance) {
  if (instance) {
    return function boundRouteGuard () {
      return guard.apply(instance, arguments)
    }
  }
}

function extractEnterGuards (
  activated,
  cbs,
  isValid
) {
  return extractGuards(activated, 'beforeRouteEnter', function (guard, _, match, key) {
    return bindEnterGuard(guard, match, key, cbs, isValid)
  })
}

function bindEnterGuard (
  guard,
  match,
  key,
  cbs,
  isValid
) {
  return function routeEnterGuard (to, from, next) {
    return guard(to, from, function (cb) {
      next(cb);
      if (typeof cb === 'function') {
        cbs.push(function () {
          // #750
          // if a router-view is wrapped with an out-in transition,
          // the instance may not have been registered at this time.
          // we will need to poll for registration until current route
          // is no longer valid.
          poll(cb, match.instances, key, isValid);
        });
      }
    })
  }
}

function poll (
  cb, // somehow flow cannot infer this is a function
  instances,
  key,
  isValid
) {
  if (instances[key]) {
    cb(instances[key]);
  } else if (isValid()) {
    setTimeout(function () {
      poll(cb, instances, key, isValid);
    }, 16);
  }
}

/*  */


var HTML5History = (function (History$$1) {
  function HTML5History (router, base) {
    var this$1 = this;

    History$$1.call(this, router, base);

    var expectScroll = router.options.scrollBehavior;

    if (expectScroll) {
      setupScroll();
    }

    var initLocation = getLocation(this.base);
    window.addEventListener('popstate', function (e) {
      var current = this$1.current;

      // Avoiding first `popstate` event dispatched in some browsers but first
      // history route not updated since async guard at the same time.
      var location = getLocation(this$1.base);
      if (this$1.current === START && location === initLocation) {
        return
      }

      this$1.transitionTo(location, function (route) {
        if (expectScroll) {
          handleScroll(router, route, current, true);
        }
      });
    });
  }

  if ( History$$1 ) HTML5History.__proto__ = History$$1;
  HTML5History.prototype = Object.create( History$$1 && History$$1.prototype );
  HTML5History.prototype.constructor = HTML5History;

  HTML5History.prototype.go = function go (n) {
    window.history.go(n);
  };

  HTML5History.prototype.push = function push (location, onComplete, onAbort) {
    var this$1 = this;

    var ref = this;
    var fromRoute = ref.current;
    this.transitionTo(location, function (route) {
      pushState(cleanPath(this$1.base + route.fullPath));
      handleScroll(this$1.router, route, fromRoute, false);
      onComplete && onComplete(route);
    }, onAbort);
  };

  HTML5History.prototype.replace = function replace (location, onComplete, onAbort) {
    var this$1 = this;

    var ref = this;
    var fromRoute = ref.current;
    this.transitionTo(location, function (route) {
      replaceState(cleanPath(this$1.base + route.fullPath));
      handleScroll(this$1.router, route, fromRoute, false);
      onComplete && onComplete(route);
    }, onAbort);
  };

  HTML5History.prototype.ensureURL = function ensureURL (push) {
    if (getLocation(this.base) !== this.current.fullPath) {
      var current = cleanPath(this.base + this.current.fullPath);
      push ? pushState(current) : replaceState(current);
    }
  };

  HTML5History.prototype.getCurrentLocation = function getCurrentLocation () {
    return getLocation(this.base)
  };

  return HTML5History;
}(History));

function getLocation (base) {
  var path = window.location.pathname;
  if (base && path.indexOf(base) === 0) {
    path = path.slice(base.length);
  }
  return (path || '/') + window.location.search + window.location.hash
}

/*  */


var HashHistory = (function (History$$1) {
  function HashHistory (router, base, fallback) {
    History$$1.call(this, router, base);
    // check history fallback deeplinking
    if (fallback && checkFallback(this.base)) {
      return
    }
    ensureSlash();
  }

  if ( History$$1 ) HashHistory.__proto__ = History$$1;
  HashHistory.prototype = Object.create( History$$1 && History$$1.prototype );
  HashHistory.prototype.constructor = HashHistory;

  // this is delayed until the app mounts
  // to avoid the hashchange listener being fired too early
  HashHistory.prototype.setupListeners = function setupListeners () {
    var this$1 = this;

    var router = this.router;
    var expectScroll = router.options.scrollBehavior;
    var supportsScroll = supportsPushState && expectScroll;

    if (supportsScroll) {
      setupScroll();
    }

    window.addEventListener(supportsPushState ? 'popstate' : 'hashchange', function () {
      var current = this$1.current;
      if (!ensureSlash()) {
        return
      }
      this$1.transitionTo(getHash(), function (route) {
        if (supportsScroll) {
          handleScroll(this$1.router, route, current, true);
        }
        if (!supportsPushState) {
          replaceHash(route.fullPath);
        }
      });
    });
  };

  HashHistory.prototype.push = function push (location, onComplete, onAbort) {
    var this$1 = this;

    var ref = this;
    var fromRoute = ref.current;
    this.transitionTo(location, function (route) {
      pushHash(route.fullPath);
      handleScroll(this$1.router, route, fromRoute, false);
      onComplete && onComplete(route);
    }, onAbort);
  };

  HashHistory.prototype.replace = function replace (location, onComplete, onAbort) {
    var this$1 = this;

    var ref = this;
    var fromRoute = ref.current;
    this.transitionTo(location, function (route) {
      replaceHash(route.fullPath);
      handleScroll(this$1.router, route, fromRoute, false);
      onComplete && onComplete(route);
    }, onAbort);
  };

  HashHistory.prototype.go = function go (n) {
    window.history.go(n);
  };

  HashHistory.prototype.ensureURL = function ensureURL (push) {
    var current = this.current.fullPath;
    if (getHash() !== current) {
      push ? pushHash(current) : replaceHash(current);
    }
  };

  HashHistory.prototype.getCurrentLocation = function getCurrentLocation () {
    return getHash()
  };

  return HashHistory;
}(History));

function checkFallback (base) {
  var location = getLocation(base);
  if (!/^\/#/.test(location)) {
    window.location.replace(
      cleanPath(base + '/#' + location)
    );
    return true
  }
}

function ensureSlash () {
  var path = getHash();
  if (path.charAt(0) === '/') {
    return true
  }
  replaceHash('/' + path);
  return false
}

function getHash () {
  // We can't use window.location.hash here because it's not
  // consistent across browsers - Firefox will pre-decode it!
  var href = window.location.href;
  var index = href.indexOf('#');
  return index === -1 ? '' : href.slice(index + 1)
}

function getUrl (path) {
  var href = window.location.href;
  var i = href.indexOf('#');
  var base = i >= 0 ? href.slice(0, i) : href;
  return (base + "#" + path)
}

function pushHash (path) {
  if (supportsPushState) {
    pushState(getUrl(path));
  } else {
    window.location.hash = path;
  }
}

function replaceHash (path) {
  if (supportsPushState) {
    replaceState(getUrl(path));
  } else {
    window.location.replace(getUrl(path));
  }
}

/*  */


var AbstractHistory = (function (History$$1) {
  function AbstractHistory (router, base) {
    History$$1.call(this, router, base);
    this.stack = [];
    this.index = -1;
  }

  if ( History$$1 ) AbstractHistory.__proto__ = History$$1;
  AbstractHistory.prototype = Object.create( History$$1 && History$$1.prototype );
  AbstractHistory.prototype.constructor = AbstractHistory;

  AbstractHistory.prototype.push = function push (location, onComplete, onAbort) {
    var this$1 = this;

    this.transitionTo(location, function (route) {
      this$1.stack = this$1.stack.slice(0, this$1.index + 1).concat(route);
      this$1.index++;
      onComplete && onComplete(route);
    }, onAbort);
  };

  AbstractHistory.prototype.replace = function replace (location, onComplete, onAbort) {
    var this$1 = this;

    this.transitionTo(location, function (route) {
      this$1.stack = this$1.stack.slice(0, this$1.index).concat(route);
      onComplete && onComplete(route);
    }, onAbort);
  };

  AbstractHistory.prototype.go = function go (n) {
    var this$1 = this;

    var targetIndex = this.index + n;
    if (targetIndex < 0 || targetIndex >= this.stack.length) {
      return
    }
    var route = this.stack[targetIndex];
    this.confirmTransition(route, function () {
      this$1.index = targetIndex;
      this$1.updateRoute(route);
    });
  };

  AbstractHistory.prototype.getCurrentLocation = function getCurrentLocation () {
    var current = this.stack[this.stack.length - 1];
    return current ? current.fullPath : '/'
  };

  AbstractHistory.prototype.ensureURL = function ensureURL () {
    // noop
  };

  return AbstractHistory;
}(History));

/*  */

var VueRouter = function VueRouter (options) {
  if ( options === void 0 ) options = {};

  this.app = null;
  this.apps = [];
  this.options = options;
  this.beforeHooks = [];
  this.resolveHooks = [];
  this.afterHooks = [];
  this.matcher = createMatcher(options.routes || [], this);

  var mode = options.mode || 'hash';
  this.fallback = mode === 'history' && !supportsPushState && options.fallback !== false;
  if (this.fallback) {
    mode = 'hash';
  }
  if (!inBrowser) {
    mode = 'abstract';
  }
  this.mode = mode;

  switch (mode) {
    case 'history':
      this.history = new HTML5History(this, options.base);
      break
    case 'hash':
      this.history = new HashHistory(this, options.base, this.fallback);
      break
    case 'abstract':
      this.history = new AbstractHistory(this, options.base);
      break
    default:
      if (false) {
        assert(false, ("invalid mode: " + mode));
      }
  }
};

var prototypeAccessors = { currentRoute: { configurable: true } };

VueRouter.prototype.match = function match (
  raw,
  current,
  redirectedFrom
) {
  return this.matcher.match(raw, current, redirectedFrom)
};

prototypeAccessors.currentRoute.get = function () {
  return this.history && this.history.current
};

VueRouter.prototype.init = function init (app /* Vue component instance */) {
    var this$1 = this;

  "production" !== 'production' && assert(
    install.installed,
    "not installed. Make sure to call `Vue.use(VueRouter)` " +
    "before creating root instance."
  );

  this.apps.push(app);

  // main app already initialized.
  if (this.app) {
    return
  }

  this.app = app;

  var history = this.history;

  if (history instanceof HTML5History) {
    history.transitionTo(history.getCurrentLocation());
  } else if (history instanceof HashHistory) {
    var setupHashListener = function () {
      history.setupListeners();
    };
    history.transitionTo(
      history.getCurrentLocation(),
      setupHashListener,
      setupHashListener
    );
  }

  history.listen(function (route) {
    this$1.apps.forEach(function (app) {
      app._route = route;
    });
  });
};

VueRouter.prototype.beforeEach = function beforeEach (fn) {
  return registerHook(this.beforeHooks, fn)
};

VueRouter.prototype.beforeResolve = function beforeResolve (fn) {
  return registerHook(this.resolveHooks, fn)
};

VueRouter.prototype.afterEach = function afterEach (fn) {
  return registerHook(this.afterHooks, fn)
};

VueRouter.prototype.onReady = function onReady (cb, errorCb) {
  this.history.onReady(cb, errorCb);
};

VueRouter.prototype.onError = function onError (errorCb) {
  this.history.onError(errorCb);
};

VueRouter.prototype.push = function push (location, onComplete, onAbort) {
  this.history.push(location, onComplete, onAbort);
};

VueRouter.prototype.replace = function replace (location, onComplete, onAbort) {
  this.history.replace(location, onComplete, onAbort);
};

VueRouter.prototype.go = function go (n) {
  this.history.go(n);
};

VueRouter.prototype.back = function back () {
  this.go(-1);
};

VueRouter.prototype.forward = function forward () {
  this.go(1);
};

VueRouter.prototype.getMatchedComponents = function getMatchedComponents (to) {
  var route = to
    ? to.matched
      ? to
      : this.resolve(to).route
    : this.currentRoute;
  if (!route) {
    return []
  }
  return [].concat.apply([], route.matched.map(function (m) {
    return Object.keys(m.components).map(function (key) {
      return m.components[key]
    })
  }))
};

VueRouter.prototype.resolve = function resolve (
  to,
  current,
  append
) {
  var location = normalizeLocation(
    to,
    current || this.history.current,
    append,
    this
  );
  var route = this.match(location, current);
  var fullPath = route.redirectedFrom || route.fullPath;
  var base = this.history.base;
  var href = createHref(base, fullPath, this.mode);
  return {
    location: location,
    route: route,
    href: href,
    // for backwards compat
    normalizedTo: location,
    resolved: route
  }
};

VueRouter.prototype.addRoutes = function addRoutes (routes) {
  this.matcher.addRoutes(routes);
  if (this.history.current !== START) {
    this.history.transitionTo(this.history.getCurrentLocation());
  }
};

Object.defineProperties( VueRouter.prototype, prototypeAccessors );

function registerHook (list, fn) {
  list.push(fn);
  return function () {
    var i = list.indexOf(fn);
    if (i > -1) { list.splice(i, 1); }
  }
}

function createHref (base, fullPath, mode) {
  var path = mode === 'hash' ? '#' + fullPath : fullPath;
  return base ? cleanPath(base + '/' + path) : path
}

VueRouter.install = install;
VueRouter.version = '3.0.1';

if (inBrowser && window.Vue) {
  window.Vue.use(VueRouter);
}

/* harmony default export */ __webpack_exports__["a"] = (VueRouter);


/***/ }),
/* 358 */,
/* 359 */,
/* 360 */,
/* 361 */,
/* 362 */,
/* 363 */,
/* 364 */,
/* 365 */,
/* 366 */,
/* 367 */,
/* 368 */,
/* 369 */,
/* 370 */,
/* 371 */,
/* 372 */,
/* 373 */,
/* 374 */,
/* 375 */,
/* 376 */,
/* 377 */,
/* 378 */,
/* 379 */,
/* 380 */,
/* 381 */,
/* 382 */,
/* 383 */,
/* 384 */,
/* 385 */,
/* 386 */,
/* 387 */,
/* 388 */,
/* 389 */,
/* 390 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_vux_loader_src_script_loader_js_vux_loader_src_script_loader_js_vue_loader_lib_selector_type_script_index_0_index_vue__ = __webpack_require__(445);
/* unused harmony namespace reexport */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__babel_loader_vux_loader_src_script_loader_js_vue_loader_lib_selector_type_script_index_0_index_vue__ = __webpack_require__(446);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__vue_loader_lib_template_compiler_index_id_data_v_3fc027bb_hasScoped_false_buble_transforms_vux_loader_src_before_template_compiler_loader_js_vux_loader_src_template_loader_js_vue_loader_lib_selector_type_template_index_0_index_vue__ = __webpack_require__(447);
function injectStyle (ssrContext) {
  __webpack_require__(444)
}
var normalizeComponent = __webpack_require__(0)
/* script */


/* template */

/* template functional */
var __vue_template_functional__ = false
/* styles */
var __vue_styles__ = injectStyle
/* scopeId */
var __vue_scopeId__ = null
/* moduleIdentifier (server only) */
var __vue_module_identifier__ = null
var Component = normalizeComponent(
  __WEBPACK_IMPORTED_MODULE_1__babel_loader_vux_loader_src_script_loader_js_vue_loader_lib_selector_type_script_index_0_index_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_2__vue_loader_lib_template_compiler_index_id_data_v_3fc027bb_hasScoped_false_buble_transforms_vux_loader_src_before_template_compiler_loader_js_vux_loader_src_template_loader_js_vue_loader_lib_selector_type_template_index_0_index_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)

/* harmony default export */ __webpack_exports__["a"] = (Component.exports);


/***/ }),
/* 391 */,
/* 392 */,
/* 393 */,
/* 394 */,
/* 395 */,
/* 396 */,
/* 397 */,
/* 398 */,
/* 399 */,
/* 400 */,
/* 401 */,
/* 402 */,
/* 403 */,
/* 404 */,
/* 405 */,
/* 406 */,
/* 407 */,
/* 408 */,
/* 409 */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),
/* 410 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";


/* unused harmony default export */ var _unused_webpack_default_export = ({
  name: 'checker',
  props: {
    defaultItemClass: String,
    selectedItemClass: String,
    disabledItemClass: String,
    type: {
      type: String,
      default: 'radio'
    },
    value: [String, Number, Array, Object],
    max: Number,
    radioRequired: Boolean
  },
  watch: {
    value: function value(newValue) {
      this.currentValue = newValue;
    },
    currentValue: function currentValue(val) {
      this.$emit('input', val);
      this.$emit('on-change', val);
    }
  },
  data: function data() {
    return {
      currentValue: this.value
    };
  }
});

/***/ }),
/* 411 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";


/* harmony default export */ __webpack_exports__["a"] = ({
  name: 'checker',
  props: {
    defaultItemClass: String,
    selectedItemClass: String,
    disabledItemClass: String,
    type: {
      type: String,
      default: 'radio'
    },
    value: [String, Number, Array, Object],
    max: Number,
    radioRequired: Boolean
  },
  watch: {
    value: function value(newValue) {
      this.currentValue = newValue;
    },
    currentValue: function currentValue(val) {
      this.$emit('input', val);
      this.$emit('on-change', val);
    }
  },
  data: function data() {
    return {
      currentValue: this.value
    };
  }
});

/***/ }),
/* 412 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{staticClass:"vux-checker-box"},[_vm._t("default")],2)}
var staticRenderFns = []
var esExports = { render: render, staticRenderFns: staticRenderFns }
/* harmony default export */ __webpack_exports__["a"] = (esExports);

/***/ }),
/* 413 */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),
/* 414 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_json_stringify__ = __webpack_require__(13);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_json_stringify___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_json_stringify__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_babel_runtime_helpers_typeof__ = __webpack_require__(20);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_babel_runtime_helpers_typeof___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_1_babel_runtime_helpers_typeof__);




/* unused harmony default export */ var _unused_webpack_default_export = ({
  name: 'checker-item',
  props: {
    value: {
      type: [String, Number, Object],
      required: true
    },
    disabled: Boolean
  },
  watch: {
    disabled: function disabled(val) {
      if (val && this.$parent.type === 'radio' && this.value === this.$parent.currentValue) {
        this.$parent.currentValue = '';
      }
    }
  },
  computed: {
    classNames: function classNames() {
      var _this = this;

      var isSimpleValue = typeof this.value === 'string' || typeof this.value === 'number';
      var names = {
        'vux-tap-active': !this.disabled
      };

      if (this.$parent.defaultItemClass) {
        names[this.$parent.defaultItemClass] = true;
      }

      if (this.$parent.selectedItemClass) {
        var selected = false;
        if (this.$parent.type === 'radio') {
          if (isSimpleValue && this.$parent.currentValue === this.value) {
            selected = true;
          } else if (__WEBPACK_IMPORTED_MODULE_1_babel_runtime_helpers_typeof___default()(this.value) === 'object' && isEqual(this.$parent.currentValue, this.value)) {
            selected = true;
          }
        } else {
          if (typeof this.value === 'string') {
            if (this.$parent.currentValue.indexOf(this.value) > -1) {
              selected = true;
            }
          } else if (this.$parent.currentValue && this.$parent.currentValue.length) {
            var match = this.$parent.currentValue.filter(function (one) {
              return isEqual(one, _this.value);
            });
            selected = match.length > 0;
          }
        }
        names[this.$parent.selectedItemClass] = selected;
      }

      if (this.$parent.disabledItemClass) {
        names[this.$parent.disabledItemClass] = this.disabled;
      }

      return names;
    }
  },
  methods: {
    select: function select() {
      if (this.$parent.type === 'radio') {
        this.selectRadio();
      } else {
        this.selectCheckbox();
      }
    },
    selectRadio: function selectRadio() {
      if (!this.disabled) {
        if (this.$parent.currentValue === this.value) {
          if (!this.$parent.radioRequired) {
            this.$parent.currentValue = '';
          }
        } else {
          this.$parent.currentValue = this.value;
        }
      }
      this.$emit('on-item-click', this.value, this.disabled);
    },
    selectCheckbox: function selectCheckbox() {
      if (!this.$parent.currentValue || this.$parent.currentValue === null) {
        this.$parent.currentValue = [];
      }
      var isSimpleValue = typeof this.value === 'string' || typeof this.value === 'number';
      if (!this.disabled) {
        var index = -1;
        if (isSimpleValue) {
          index = this.$parent.currentValue.indexOf(this.value);
        } else {
          index = this.$parent.currentValue.map(function (one) {
            return __WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_json_stringify___default()(one);
          }).indexOf(__WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_json_stringify___default()(this.value));
        }
        if (index > -1) {
          this.$parent.currentValue.splice(index, 1);
        } else {
          if (!this.$parent.max || this.$parent.max && this.$parent.currentValue !== null && this.$parent.currentValue.length < this.$parent.max) {
            if (!this.$parent.currentValue || !this.$parent.currentValue.length) {
              this.$parent.currentValue = [];
            }
            this.$parent.currentValue.push(this.value);
          }
        }
      }
      this.$emit('on-item-click', this.value, this.disabled);
    }
  }
});

function isEqual(obj1, obj2) {
  return __WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_json_stringify___default()(obj1) === __WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_json_stringify___default()(obj2);
}

/***/ }),
/* 415 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_json_stringify__ = __webpack_require__(13);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_json_stringify___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_json_stringify__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_babel_runtime_helpers_typeof__ = __webpack_require__(20);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_babel_runtime_helpers_typeof___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_1_babel_runtime_helpers_typeof__);




/* harmony default export */ __webpack_exports__["a"] = ({
  name: 'checker-item',
  props: {
    value: {
      type: [String, Number, Object],
      required: true
    },
    disabled: Boolean
  },
  watch: {
    disabled: function disabled(val) {
      if (val && this.$parent.type === 'radio' && this.value === this.$parent.currentValue) {
        this.$parent.currentValue = '';
      }
    }
  },
  computed: {
    classNames: function classNames() {
      var _this = this;

      var isSimpleValue = typeof this.value === 'string' || typeof this.value === 'number';
      var names = {
        'vux-tap-active': !this.disabled
      };

      if (this.$parent.defaultItemClass) {
        names[this.$parent.defaultItemClass] = true;
      }

      if (this.$parent.selectedItemClass) {
        var selected = false;
        if (this.$parent.type === 'radio') {
          if (isSimpleValue && this.$parent.currentValue === this.value) {
            selected = true;
          } else if (__WEBPACK_IMPORTED_MODULE_1_babel_runtime_helpers_typeof___default()(this.value) === 'object' && isEqual(this.$parent.currentValue, this.value)) {
            selected = true;
          }
        } else {
          if (typeof this.value === 'string') {
            if (this.$parent.currentValue.indexOf(this.value) > -1) {
              selected = true;
            }
          } else if (this.$parent.currentValue && this.$parent.currentValue.length) {
            var match = this.$parent.currentValue.filter(function (one) {
              return isEqual(one, _this.value);
            });
            selected = match.length > 0;
          }
        }
        names[this.$parent.selectedItemClass] = selected;
      }

      if (this.$parent.disabledItemClass) {
        names[this.$parent.disabledItemClass] = this.disabled;
      }

      return names;
    }
  },
  methods: {
    select: function select() {
      if (this.$parent.type === 'radio') {
        this.selectRadio();
      } else {
        this.selectCheckbox();
      }
    },
    selectRadio: function selectRadio() {
      if (!this.disabled) {
        if (this.$parent.currentValue === this.value) {
          if (!this.$parent.radioRequired) {
            this.$parent.currentValue = '';
          }
        } else {
          this.$parent.currentValue = this.value;
        }
      }
      this.$emit('on-item-click', this.value, this.disabled);
    },
    selectCheckbox: function selectCheckbox() {
      if (!this.$parent.currentValue || this.$parent.currentValue === null) {
        this.$parent.currentValue = [];
      }
      var isSimpleValue = typeof this.value === 'string' || typeof this.value === 'number';
      if (!this.disabled) {
        var index = -1;
        if (isSimpleValue) {
          index = this.$parent.currentValue.indexOf(this.value);
        } else {
          index = this.$parent.currentValue.map(function (one) {
            return __WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_json_stringify___default()(one);
          }).indexOf(__WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_json_stringify___default()(this.value));
        }
        if (index > -1) {
          this.$parent.currentValue.splice(index, 1);
        } else {
          if (!this.$parent.max || this.$parent.max && this.$parent.currentValue !== null && this.$parent.currentValue.length < this.$parent.max) {
            if (!this.$parent.currentValue || !this.$parent.currentValue.length) {
              this.$parent.currentValue = [];
            }
            this.$parent.currentValue.push(this.value);
          }
        }
      }
      this.$emit('on-item-click', this.value, this.disabled);
    }
  }
});

function isEqual(obj1, obj2) {
  return __WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_json_stringify___default()(obj1) === __WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_json_stringify___default()(obj2);
}

/***/ }),
/* 416 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{staticClass:"vux-checker-item",class:_vm.classNames,on:{"click":_vm.select}},[_vm._t("default")],2)}
var staticRenderFns = []
var esExports = { render: render, staticRenderFns: staticRenderFns }
/* harmony default export */ __webpack_exports__["a"] = (esExports);

/***/ }),
/* 417 */,
/* 418 */,
/* 419 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
// Vue插件，Tap事件处理
// Author: 陈哈哈 yoojiachen@gmail.com

var _IS_MOBILE = /mobile|table|ip(ad|hone|od)|android/i.test(
  navigator.userAgent
);

var plugin = {
  bind: function(el, binding) {
    el.binding_ref = binding;
    el.tapEventHandler = function(evt) {
      if (el.disabled) return;
      evt.stopPropagation();
      evt.cancelBubble = true;
      var value = el.binding_ref.value;
      value.method.call(this, ...Object.values(value.params || {}), evt);
    };
    el.tapEventHandler_nop = function(evt) {};
    if (_IS_MOBILE) {
      el.addEventListener('touchstart', el.tapEventHandler, true);
      el.addEventListener('touchend', el.tapEventHandler_nop, true);
    } else {
      el.addEventListener('click', el.tapEventHandler, true);
    }
  },
  unbind: function(el) {
    if (_IS_MOBILE) {
      el.removeEventListener('touchstart', el.tapEventHandler, true);
      el.removeEventListener('touchend', el.tapEventHandler_nop, true);
    } else {
      el.removeEventListener('click', el.tapEventHandler, true);
    }
  },
  update: function(el, binding) {
    el.binding_ref = binding;
  }
};

/* harmony default export */ __webpack_exports__["a"] = ({
  tap: plugin
});


/***/ }),
/* 420 */
/***/ (function(module, exports, __webpack_require__) {

(function(global, factory) {
  module.exports = factory();
})(this, function() {
  'use strict';

  // 定义键盘引擎内部键盘响应逻辑
  // Author: 陈哈哈 yoojiachen@gmail.com

  var frm = __webpack_require__(953);
  var def = __webpack_require__(542);
  var hlp = __webpack_require__(954);

  /** 全局配置 */
  var _GlobalConf = {
    // 键位提供器
    keyProvider: frm.Chain.create({}),
    // 布局提供器
    layoutProvider: frm.Chain.create({}),
    // 布局混合
    mixiner: frm.Each.create()
  };

  /**
   * 初始化
   */
  function init(options) {
    ////// 注册布局提供器 START //////

    // 民用键盘布局：
    var _LAYOUT_CIVIL = 'layout.c';
    frm.Cached.reg(
      options.align === def.KB_ALIGN.JUSTIFY ?
        {
          row0: hlp.keysOf(def.S_CIVIL_PVS.substr(0, 9)), // 京津沪晋冀蒙辽吉黑
          row1: hlp.keysOf(def.S_CIVIL_PVS.substr(9, 9)), // 苏浙皖闽赣鲁豫鄂湘
          row2: hlp.keysOf(def.S_CIVIL_PVS.substr(18, 9)), // 粤桂琼渝川贵云藏陕
          row3: hlp.keysOf(
            // 甘青宁新-+
            def.S_CIVIL_PVS.substr(27, 4) + ' ' + ' ' + def.S_DEL_OK
          )
        } :
        {
          row0: hlp.keysOf(def.S_CIVIL_PVS.substr(0, 9)), // 京津沪晋冀蒙辽吉黑
          row1: hlp.keysOf(def.S_CIVIL_PVS.substr(9, 8)), // 苏浙皖闽赣鲁豫鄂
          row2: hlp.keysOf(def.S_CIVIL_PVS.substr(17, 8)), // 湘粤桂琼渝川贵云
          row3: hlp.keysOf(def.S_CIVIL_PVS.substr(25, 6) + def.S_DEL_OK) // 藏陕甘青宁新-+
        },
      _LAYOUT_CIVIL,
      0
    );
    frm.Cached.reg(
      {
        row0: hlp.keysOf(def.S_NUM),
        row1: hlp.keysOf(def.S_Q_OP + def.C_MACAO),
        row2: hlp.keysOf(def.S_A_L + def.C_HK),
        row3: hlp.keysOf(def.S_Z_M + def.S_DEL_OK)
      },
      _LAYOUT_CIVIL,
      1
    );
    frm.Cached.reg(
      {
        row0: hlp.keysOf(def.S_NUM),
        row1: hlp.keysOf(def.S_Q_P + def.S_HK_MACAO),
        row2: hlp.keysOf(def.S_A_L + def.C_XUE),
        row3: hlp.keysOf(def.S_Z_M + def.S_DEL_OK)
      },
      _LAYOUT_CIVIL,
      [2, 3, 4, 5, 6, 7]
    );

    // 民用+特殊车牌布局：
    var _LAYOUT_SPEC = 'layout.s';
    var _LAYOUT_SPEC_FULL = 'layout.s.f';
    frm.Cached.reg(
      {
        row0: hlp.keysOf(def.S_CIVIL_PVS.substr(0, 9)), // "京津沪晋冀蒙辽吉黑"
        row1: hlp.keysOf(def.S_CIVIL_PVS.substr(9, 9)), // "苏浙皖闽赣鲁豫鄂湘"
        row2: hlp.keysOf(def.S_CIVIL_PVS.substr(18, 9)), // "粤桂琼渝川贵云藏"
        row3: hlp.keysOf(
          def.S_CIVIL_PVS.substr(25, 5) + def.C_SHI2007 + def.C_W + def.S_DEL_OK
        ) // 陕甘青宁新使W-+
      },
      _LAYOUT_SPEC,
      0
    );
    frm.Cached.reg(
      {
        row0: hlp.keysOf(def.S_NUM + def.S_CIVIL_PVS.substr(0, 1)),
        row1: hlp.keysOf(def.S_CIVIL_PVS.substr(1, 11)),
        row2: hlp.keysOf(def.S_CIVIL_PVS.substr(12, 11)),
        row3: hlp.keysOf(def.S_CIVIL_PVS.substr(22, 8) + def.S_DEL_OK)
      },
      _LAYOUT_SPEC,
      2
    );

    frm.Cached.reg(
      {
        row0: hlp.keysOf(def.S_NUM + def.S_CIVIL_PVS.substr(0, 1)),
        row1: hlp.keysOf(def.S_CIVIL_PVS.substr(1, 11)),
        row2: hlp.keysOf(def.S_CIVIL_PVS.substr(12, 11)),
        row3: hlp.keysOf(def.S_CIVIL_PVS.substr(23, 9) + def.S_DEL_OK)
      },
      _LAYOUT_SPEC_FULL,
      2
    );

    // 全键盘布局：
    var _LAYOUT_FULL = 'layout.f';
    frm.Cached.reg(
      {
        row0: hlp.keysOf(def.S_CIVIL_PVS.substr(0, 10)), // "京津晋冀蒙辽吉黑沪苏"
        row1: hlp.keysOf(def.S_CIVIL_PVS.substr(10, 10)), // "浙皖闽赣鲁豫鄂湘粤桂"
        row2: hlp.keysOf(def.S_CIVIL_PVS.substr(20, 10)), // "琼渝川贵云藏陕甘青宁"
        row3: hlp.keysOf(
          // 新民使123WQVKH
          def.S_CIVIL_PVS.substr(30, 1) +
            def.C_MIN +
            def.S_SHI2007_PVS +
            def.C_W +
            def.S_PLA2012_PVS.substr(0, 4)
        ),
        row4: hlp.keysOf(def.S_PLA2012_PVS.substr(4, 9) + def.S_DEL_OK)
      },
      _LAYOUT_FULL,
      0
    );
    frm.Cached.reg(
      options.align === def.KB_ALIGN.JUSTIFY ?
        {
          row0: hlp.keysOf(def.S_NUM),
          row1: hlp.keysOf(def.S_Q_IOP),
          row2: hlp.keysOf(def.S_A_L + def.S_Z_M.substr(0, 2)),
          row3: hlp.keysOf(
            def.S_Z_M.substr(2) +
                def.C_XUE +
                def.C_HANG +
                def.S_HK_MACAO +
                def.C_JING
          ),
          row4: hlp.keysOf(
            def.S_POSTFIX_ZH.substr(1) +
                def.C_SHI2007 +
                ' ' +
                ' ' +
                def.S_DEL_OK
          )
        } :
        {
          row0: hlp.keysOf(def.S_NUM),
          row1: hlp.keysOf(def.S_Q_IOP),
          row2: hlp.keysOf(def.S_A_L),
          row3: hlp.keysOf(def.S_Z_M + def.C_XUE + def.C_HANG),
          row4: hlp.keysOf(
            def.S_HK_MACAO + def.S_POSTFIX_ZH + def.C_SHI2007 + def.S_DEL_OK
          )
        },
      _LAYOUT_FULL,
      1
    );
    frm.Cached.reg(
      options.align === def.KB_ALIGN.JUSTIFY ?
        {
          row0: hlp.keysOf(def.S_NUM),
          row1: hlp.keysOf(def.S_Q_IOP),
          row2: hlp.keysOf(def.S_A_L + def.S_Z_M.substr(0, 2)),
          row3: hlp.keysOf(
            def.S_Z_M.substr(2) +
                def.C_XUE +
                def.S_HK_MACAO +
                def.S_POSTFIX_ZH.substr(0, 3)
          ),
          row4: hlp.keysOf(
            def.S_POSTFIX_ZH.substr(3) +
                def.C_SHI2007 +
                ' ' +
                ' ' +
                ' ' +
                ' ' +
                def.S_DEL_OK
          )
        } :
        {
          row0: hlp.keysOf(def.S_NUM),
          row1: hlp.keysOf(def.S_Q_IOP),
          row2: hlp.keysOf(def.S_A_L),
          row3: hlp.keysOf(def.S_Z_M + def.C_XUE),
          row4: hlp.keysOf(
            def.S_HK_MACAO + def.S_POSTFIX_ZH + def.C_SHI2007 + def.S_DEL_OK
          )
        },
      _LAYOUT_FULL,
      [2, 3, 4, 5, 6, 7]
    );

    // 处理“民用+武警”的特殊键位2种情况:
    // 1 - 第一位键盘布局中，显示带武警字符的特殊布局:
    _GlobalConf.layoutProvider.reg(function(chain, args) {
      if (0 === args.index && args.keyboardType === def.KB_TYPES.CIVIL_SPEC) {
        return frm.Cached.load(_LAYOUT_SPEC, 0);
      }
      return chain.next(args);
    });

    // 2 - 第二位键盘布局中，当输入的车牌为武警车牌时，才显示武警特殊布局:
    _GlobalConf.layoutProvider.reg(function(chain, args) {
      if (
        2 === args.index &&
        args.keyboardType !== def.KB_TYPES.CIVIL &&
        (def.NUM_TYPES.WJ2007 === args.numberType ||
          def.NUM_TYPES.WJ2012 === args.numberType)
      ) {
        if (args.keyboardType === def.KB_TYPES.FULL) {
          return frm.Cached.load(_LAYOUT_SPEC_FULL, 2);
        }
        return frm.Cached.load(_LAYOUT_SPEC, 2);
      }
      return chain.next(args);
    });

    // 其它注册布局提供器
    _GlobalConf.layoutProvider.reg(function(chain, args) {
      if (args.keyboardType === def.KB_TYPES.FULL) {
        return frm.Cached.load(_LAYOUT_FULL, args.index);
      }
      return frm.Cached.load(_LAYOUT_CIVIL, args.index);
    });

    ////// 注册布局提供器 END //////

    ////// 可用键位提供器 START //////

    var _KEY_ANY = 'keys.any';
    var _KEY_CIVIL = 'keys.civil';
    var _KEY_PLA2012 = 'keys.army';
    var _KEY_WJ = 'keys.wj';
    var _KEY_AVIATION = 'keys.aviation';
    var _KEY_SHI2007 = 'keys.embassy';
    var _KEY_SHI2007_ZH = 'keys.embassy.zh';
    var _KEY_NUMBRICS = 'keys.num';
    var _KEY_NUMBRICS_LETTERS = 'keys.num.letters';
    var _KEY_O_POLICE = 'keys.O.police';
    var _KEY_NUMERICS_DF = 'keys.num.df';
    var _KEY_HK_MACAO = 'keys.hk.macao';
    var _KEY_POSTFIX = 'keys.postfix';

    frm.Cached.reg(
      hlp.keysOf(
        def.S_CIVIL_PVS +
          def.S_SHI2007_PVS +
          def.C_W +
          def.S_PLA2012_PVS +
          def.C_MIN
      ),
      _KEY_ANY
    );
    frm.Cached.reg(hlp.keysOf(def.S_NUM), _KEY_NUMBRICS);
    frm.Cached.reg(hlp.keysOf(def.S_CHARS), _KEY_NUMBRICS_LETTERS);
    frm.Cached.reg(hlp.keysOf(def.S_CHARS + def.C_JING), _KEY_O_POLICE);

    frm.Cached.reg(hlp.keysOf(def.S_LETTERS + def.C_O), _KEY_CIVIL, 1);
    frm.Cached.reg(hlp.keysOf(def.S_PLA2012_AREA), _KEY_PLA2012, 1);
    frm.Cached.reg(hlp.keysOf(def.S_123), _KEY_SHI2007, 1);
    frm.Cached.reg(hlp.keysOf(def.C_J), _KEY_WJ, 1);
    frm.Cached.reg(hlp.keysOf(def.C_HANG), _KEY_AVIATION, 1);

    frm.Cached.reg(hlp.keysOf(def.S_NUM + def.S_CIVIL_PVS), _KEY_WJ, 2);

    frm.Cached.reg(hlp.keysOf(def.S_NUM + def.S_DF), _KEY_NUMERICS_DF);
    frm.Cached.reg(hlp.keysOf(def.S_HK_MACAO), _KEY_HK_MACAO);
    frm.Cached.reg(
      hlp.keysOf(def.S_CHARS + def.S_POSTFIX_ZH + def.C_XUE),
      _KEY_POSTFIX
    );
    frm.Cached.reg(hlp.keysOf(def.C_SHI2007), _KEY_SHI2007_ZH);

    // 键位提供器，Index：0
    _GlobalConf.keyProvider.reg(function(chain, args) {
      if (0 === args.index) {
        return frm.Cached.load(_KEY_ANY);
      }
      return chain.next(args);
    });

    // 键位提供器，Index：1
    _GlobalConf.keyProvider.reg(function(chain, args) {
      if (1 === args.index) {
        switch (args.numberType) {
          case def.NUM_TYPES.PLA2012:
            return frm.Cached.load(_KEY_PLA2012, 1);
          case def.NUM_TYPES.WJ2007:
          case def.NUM_TYPES.WJ2012:
            return frm.Cached.load(_KEY_WJ, 1);
          case def.NUM_TYPES.AVIATION:
            return frm.Cached.load(_KEY_AVIATION, 1);
          case def.NUM_TYPES.SHI2007:
            return frm.Cached.load(_KEY_SHI2007, 1);
          case def.NUM_TYPES.SHI2017:
            return frm.Cached.load(_KEY_NUMBRICS);
          default:
            return frm.Cached.load(_KEY_CIVIL, 1);
        }
      } else {
        return chain.next(args);
      }
    });

    // 键位提供器，Index：2
    _GlobalConf.keyProvider.reg(function(chain, args) {
      if (2 === args.index) {
        switch (args.numberType) {
          case def.NUM_TYPES.WJ2007:
          case def.NUM_TYPES.WJ2012:
            return frm.Cached.load(_KEY_WJ, 2);
          case def.NUM_TYPES.SHI2007:
          case def.NUM_TYPES.SHI2017:
            return frm.Cached.load(_KEY_NUMBRICS);
          case def.NUM_TYPES.NEW_ENERGY:
            return frm.Cached.load(_KEY_NUMERICS_DF);
          default:
            return frm.Cached.load(_KEY_NUMBRICS_LETTERS);
        }
      } else {
        return chain.next(args);
      }
    });

    // 键位提供器，Index：3
    _GlobalConf.keyProvider.reg(function(chain, args) {
      if (3 === args.index && def.NUM_TYPES.SHI2007 === args.numberType) {
        return frm.Cached.load(_KEY_NUMBRICS);
      }
      return chain.next(args);
    });

    // 键位提供器，Index：4
    _GlobalConf.keyProvider.reg(function(chain, args) {
      if (
        (4 === args.index || 5 === args.index) &&
        def.NUM_TYPES.NEW_ENERGY === args.numberType
      ) {
        return frm.Cached.load(_KEY_NUMBRICS);
      }
      return chain.next(args);
    });

    // 键位提供器，Index：6
    _GlobalConf.keyProvider.reg(function(chain, args) {
      if (6 === args.index) {
        var mode = args.numberType;
        switch (args.numberType) {
          case def.NUM_TYPES.NEW_ENERGY:
            return frm.Cached.load(_KEY_NUMBRICS);
          case def.NUM_TYPES.PLA2012:
          case def.NUM_TYPES.SHI2007:
          case def.NUM_TYPES.WJ2007:
          case def.NUM_TYPES.AVIATION:
          case def.NUM_TYPES.WJ2012:
            return frm.Cached.load(_KEY_NUMBRICS_LETTERS);
          case def.NUM_TYPES.SHI2017:
            return frm.Cached.load(_KEY_SHI2007_ZH);
          default:
            var cityCode = args.number.charAt(1);
            // “粤O” 之类的警车号牌
            if ('O' === cityCode) {
              return frm.Cached.load(_KEY_O_POLICE);
            }
            // “港澳”车牌
            var isHK_MACAO =
              def.NUM_TYPES.CIVIL === mode &&
              '粤' === args.number.charAt(0) &&
              'Z' === cityCode;
            if (isHK_MACAO) {
              return frm.Cached.load(_KEY_HK_MACAO);
            }
            return frm.Cached.load(_KEY_POSTFIX);
        }
      }
      return chain.next(args);
    });

    // 键位提供器，Index：7
    _GlobalConf.keyProvider.reg(function(chain, args) {
      if (7 === args.index && def.NUM_TYPES.NEW_ENERGY === args.numberType) {
        return frm.Cached.load(_KEY_NUMERICS_DF);
      }
      return chain.next(args);
    });

    // 注册键位提供器，默认
    _GlobalConf.keyProvider.reg(function() {
      return frm.Cached.load(_KEY_NUMBRICS_LETTERS);
    });

    ////// 可用键位提供器 END //////

    function _rowOf(obj, index) {
      var data = obj['row' + index];
      return data === undefined ? [] : data;
    }

    function _mapRow(obj, index, mapper) {
      obj['row' + index] = _rowOf(obj, index).map(mapper);
    }

    function _mapLayout(layout, mapper) {
      layout.numberType = layout.numberType;
      _mapRow(layout, 0, mapper);
      _mapRow(layout, 1, mapper);
      _mapRow(layout, 2, mapper);
      _mapRow(layout, 3, mapper);
      _mapRow(layout, 4, mapper);
      return layout;
    }

    // 注册键位可用性转换器
    _GlobalConf.mixiner.reg(function(layout, args) {
      var availables = args.keys.map(function(ele) {
        return ele.text;
      });
      return _mapLayout(layout, function(entity) {
        return hlp.keyOfEnabled(entity, hlp.contains(availables, entity.text));
      });
    });

    // 禁用键位: 处理新能源键盘模式下，首位不允许出现的字符
    _GlobalConf.mixiner.reg(function(layout, args) {
      return _mapLayout(layout, function(entity) {
        var enabled = entity.enabled;
        if (
          enabled &&
          args.index === 0 &&
          layout.numberType === def.NUM_TYPES.NEW_ENERGY
        ) {
          enabled = hlp.isProvince(entity.text);
        }
        return hlp.keyOfEnabled(entity, enabled);
      });
    });

    // 功能按钮“确定、删除、更多”等按键的转换处理
    _GlobalConf.mixiner.reg(function(layout) {
      return _mapLayout(layout, function(entity) {
        // 注意,KeyEntity的KeyCode还是原始状态,尚未更新,不能使用它来判断是否是功能键
        if ('-' === entity.text) {
          return hlp.keyOfCode(entity, '' /* ← */, def.KEY_TYPES.FUN_DEL);
        } else if ('+' === entity.text) {
          return hlp.keyOfCode(entity, '确定', def.KEY_TYPES.FUN_OK);
        }
        return entity;
      });
    });

    // 处理删除键逻辑
    _GlobalConf.mixiner.reg(function(layout) {
      // 当输入车牌不为空时可以点击
      return _mapLayout(layout, function(entity) {
        if (entity.keyCode === def.KEY_TYPES.FUN_DEL) {
          return hlp.keyOfEnabled(entity, layout.numberLength !== 0);
        }
        return entity;
      });
    });

    // 处理确定键位的逻辑
    _GlobalConf.mixiner.reg(function(layout) {
      // 当输入车牌达到最后一位时可以点击
      return _mapLayout(layout, function(entity) {
        if (entity.keyCode === def.KEY_TYPES.FUN_OK) {
          return hlp.keyOfEnabled(
            entity,
            layout.numberLength === layout.numberLimitLength
          );
        }
        return entity;
      });
    });

    // 合并生成keys字段
    _GlobalConf.mixiner.reg(function(layout) {
      layout.keys = _rowOf(layout, 0)
        .concat(_rowOf(layout, 1))
        .concat(_rowOf(layout, 2))
        .concat(_rowOf(layout, 3))
        .concat(_rowOf(layout, 4));
      return layout;
    });

    ////////
  }

  function __clone(srcObj) {
    var newCopy = srcObj.constructor();
    for (var prop in srcObj) {
      if (srcObj.hasOwnProperty(prop)) {
        newCopy[prop] = srcObj[prop];
      }
    }
    return newCopy;
  }

  /**
   * 键盘引擎的逻辑设计说明：
   * 1. 由“键盘布局管理器”根据传入的键盘类型，返回对应的键盘布局。例如：民用键盘只显示省份，特定位置没有“警、使”等特殊车辆；
   * 2. 由“可用键盘管理器”根据当前预设车牌号码，返回当前键位序号可用的键位。即，当前布局中哪些可点击，哪些不可点击。
   * 3. 由混合器，将“键盘布局”和“可用键位”混合，输出键盘列表，由界面根据这些键位数据来渲染界面。
   *
   * @param {* keyboardType} 键盘类型
   * @param {* currentIndex} 当前键位Index
   * @param {* presetNumber} 预设车牌号码
   * @param {* numberType} 车牌号码类型
   */
  function _update(options) {
    let { keyboardType, currentIndex, presetNumber, numberType } = options;
    // 检查参数
    if (
      keyboardType === undefined ||
      keyboardType < def.KB_TYPES.FULL ||
      keyboardType > def.KB_TYPES.CIVIL_SPEC
    ) {
      throw new RangeError(
        '参数(keyboardType)范围必须在[0, 2]之间，当前: ' + keyboardType
      );
    }
    if (
      currentIndex === undefined ||
      currentIndex !== parseInt(currentIndex, 10)
    ) {
      throw new TypeError('参数(currentIndex)必须为整数数值');
    }
    if (presetNumber === undefined || typeof presetNumber !== 'string') {
      throw new TypeError('参数(presetNumber)必须为字符串');
    }
    if (numberType === undefined || numberType !== parseInt(numberType, 10)) {
      throw new TypeError('参数(numberType)必须为整数数值');
    }
    var detectedNumberType = hlp.detectNumberTypeOf(presetNumber);
    // 如果预设车牌号码不为空，车牌类型为自动探测，则尝试
    var presetNumberType = numberType;
    if (presetNumber.length > 0 && numberType === def.NUM_TYPES.AUTO_DETECT) {
      presetNumberType = detectedNumberType;
    }
    var limitLength = def.NUM_TYPES.lenOf(presetNumberType);
    var presetLength = presetNumber.length;
    currentIndex = Math.min(currentIndex, limitLength - 1);
    if (presetLength > limitLength) {
      throw new RangeError(
        '参数(presetNumber)字符太长：' +
          presetNumber +
          '，车牌类型：' +
          numberType +
          '，此类型最大长度:' +
          limitLength
      );
    }
    var args = {
      index: currentIndex,
      number: presetNumber,
      keyboardType: keyboardType,
      numberType: presetNumberType
    };
    // 处理键位布局
    var output = __clone(_GlobalConf.layoutProvider.process(args));
    // 传递一些参数到外部
    output.index = args.index;
    output.presetNumber = args.number;
    output.keyboardType = args.keyboardType;
    output.numberType = args.numberType;
    output.presetNumberType = args.numberType;
    output.detectedNumberType = detectedNumberType;
    output.numberLength = presetLength;
    output.numberLimitLength = limitLength;
    // 处理键位
    args.keys = _GlobalConf.keyProvider.process(args);
    // 混合布局与键位
    return _GlobalConf.mixiner.process(output, args);
  }

  /**
   * 探测车牌类型
   * @param {String} presetNumber 车牌号
   * @param {Number} numberType 车牌类型
   */
  function _detectNumberType(presetNumber, numberType) {
    let detectedNumberType = hlp.detectNumberTypeOf(presetNumber);
    // 如果预设车牌号码不为空，车牌类型为自动探测，则尝试
    let presetNumberType = numberType;
    if (presetNumber.length > 0 && numberType === def.NUM_TYPES.AUTO_DETECT) {
      presetNumberType = detectedNumberType;
    }
    return presetNumberType;
  }

  // 导出的对象包括两个属性：update函数、全局配置
  var _export = {
    update: _update,
    init,
    config: _GlobalConf,
    detectNumberType: _detectNumberType
  };

  // 导出一些工具类函数
  _export.$newKey = hlp.keyOf;
  // 导出一些数据类型
  _export.NUM_TYPES = def.NUM_TYPES;
  _export.KEY_TYPES = def.KEY_TYPES;
  _export.KEYBOARD_TYPES = def.KB_TYPES;
  _export.VERSION = 'R1.2/2018.0509/iRain(SZ)';

  return _export;
});


/***/ }),
/* 421 */,
/* 422 */,
/* 423 */,
/* 424 */,
/* 425 */,
/* 426 */,
/* 427 */,
/* 428 */,
/* 429 */,
/* 430 */,
/* 431 */,
/* 432 */,
/* 433 */,
/* 434 */,
/* 435 */,
/* 436 */,
/* 437 */,
/* 438 */,
/* 439 */,
/* 440 */,
/* 441 */,
/* 442 */,
/* 443 */,
/* 444 */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),
/* 445 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__libs_router__ = __webpack_require__(10);




/* unused harmony default export */ var _unused_webpack_default_export = ({
  name: 'card',
  props: {
    header: Object,
    footer: Object
  },
  methods: {
    onClickFooter: function onClickFooter() {
      this.footer.link && Object(__WEBPACK_IMPORTED_MODULE_0__libs_router__["b" /* go */])(this.footer.link, this.$router);
      this.$emit('on-click-footer');
    }
  }
});

/***/ }),
/* 446 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__libs_router__ = __webpack_require__(10);




/* harmony default export */ __webpack_exports__["a"] = ({
  name: 'card',
  props: {
    header: Object,
    footer: Object
  },
  methods: {
    onClickFooter: function onClickFooter() {
      this.footer.link && Object(__WEBPACK_IMPORTED_MODULE_0__libs_router__["b" /* go */])(this.footer.link, this.$router);
      this.$emit('on-click-footer');
    }
  }
});

/***/ }),
/* 447 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{staticClass:"weui-panel weui-panel_access"},[(_vm.header && _vm.header.title)?_c('div',{staticClass:"weui-panel__hd",domProps:{"innerHTML":_vm._s(_vm.header.title)},on:{"click":function($event){_vm.$emit('on-click-header')}}}):_vm._e(),_vm._v(" "),_vm._t("header"),_vm._v(" "),_c('div',{staticClass:"weui-panel__bd"},[_c('div',{staticClass:"vux-card-content"},[_vm._t("content")],2)]),_vm._v(" "),_c('div',{staticClass:"weui-panel__ft"},[(_vm.footer && _vm.footer.title)?_c('a',{staticClass:"weui-cell weui-cell_access weui-cell_link",attrs:{"href":"javascript:"},on:{"click":_vm.onClickFooter}},[_c('div',{staticClass:"weui-cell__bd",domProps:{"innerHTML":_vm._s(_vm.footer.title)}})]):_vm._e()]),_vm._v(" "),_vm._t("footer")],2)}
var staticRenderFns = []
var esExports = { render: render, staticRenderFns: staticRenderFns }
/* harmony default export */ __webpack_exports__["a"] = (esExports);

/***/ }),
/* 448 */,
/* 449 */,
/* 450 */,
/* 451 */,
/* 452 */,
/* 453 */,
/* 454 */,
/* 455 */,
/* 456 */,
/* 457 */,
/* 458 */,
/* 459 */,
/* 460 */,
/* 461 */,
/* 462 */,
/* 463 */,
/* 464 */,
/* 465 */,
/* 466 */,
/* 467 */,
/* 468 */,
/* 469 */,
/* 470 */,
/* 471 */,
/* 472 */,
/* 473 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* unused harmony export install */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__number_view__ = __webpack_require__(498);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__single_keyboard__ = __webpack_require__(499);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__mixed_keyboard__ = __webpack_require__(965);
/* unused harmony reexport NumberView */
/* unused harmony reexport SingleKeyboard */
/* harmony reexport (binding) */ __webpack_require__.d(__webpack_exports__, "a", function() { return __WEBPACK_IMPORTED_MODULE_2__mixed_keyboard__["a"]; });




const components = [__WEBPACK_IMPORTED_MODULE_0__number_view__["a" /* default */], __WEBPACK_IMPORTED_MODULE_1__single_keyboard__["a" /* default */], __WEBPACK_IMPORTED_MODULE_2__mixed_keyboard__["a" /* default */]];

const install = function(Vue, opts = {}) {
  if (install.installed) return;
  components.map(component => {
    Vue.component(component.name, component);
  });
};

if (typeof window !== 'undefined' && window.Vue) {
  install(window.Vue);
}

/* unused harmony default export */ var _unused_webpack_default_export = (install);



/***/ }),
/* 474 */,
/* 475 */,
/* 476 */,
/* 477 */,
/* 478 */,
/* 479 */,
/* 480 */,
/* 481 */,
/* 482 */,
/* 483 */,
/* 484 */,
/* 485 */,
/* 486 */,
/* 487 */,
/* 488 */,
/* 489 */,
/* 490 */,
/* 491 */,
/* 492 */,
/* 493 */,
/* 494 */,
/* 495 */,
/* 496 */,
/* 497 */,
/* 498 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_vux_loader_src_script_loader_js_vux_loader_src_script_loader_js_vue_loader_lib_selector_type_script_index_0_number_view_vue__ = __webpack_require__(945);
/* unused harmony namespace reexport */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__babel_loader_vux_loader_src_script_loader_js_vue_loader_lib_selector_type_script_index_0_number_view_vue__ = __webpack_require__(946);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__vue_loader_lib_template_compiler_index_id_data_v_c6a3f1c6_hasScoped_false_buble_transforms_vux_loader_src_before_template_compiler_loader_js_vux_loader_src_template_loader_js_vue_loader_lib_selector_type_template_index_0_number_view_vue__ = __webpack_require__(947);
function injectStyle (ssrContext) {
  __webpack_require__(944)
}
var normalizeComponent = __webpack_require__(0)
/* script */


/* template */

/* template functional */
var __vue_template_functional__ = false
/* styles */
var __vue_styles__ = injectStyle
/* scopeId */
var __vue_scopeId__ = null
/* moduleIdentifier (server only) */
var __vue_module_identifier__ = null
var Component = normalizeComponent(
  __WEBPACK_IMPORTED_MODULE_1__babel_loader_vux_loader_src_script_loader_js_vue_loader_lib_selector_type_script_index_0_number_view_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_2__vue_loader_lib_template_compiler_index_id_data_v_c6a3f1c6_hasScoped_false_buble_transforms_vux_loader_src_before_template_compiler_loader_js_vux_loader_src_template_loader_js_vue_loader_lib_selector_type_template_index_0_number_view_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)

/* harmony default export */ __webpack_exports__["a"] = (Component.exports);


/***/ }),
/* 499 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_vux_loader_src_script_loader_js_vux_loader_src_script_loader_js_vue_loader_lib_selector_type_script_index_0_single_keyboard_vue__ = __webpack_require__(949);
/* unused harmony namespace reexport */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__babel_loader_vux_loader_src_script_loader_js_vue_loader_lib_selector_type_script_index_0_single_keyboard_vue__ = __webpack_require__(963);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__vue_loader_lib_template_compiler_index_id_data_v_7535d09c_hasScoped_false_buble_transforms_vux_loader_src_before_template_compiler_loader_js_vux_loader_src_template_loader_js_vue_loader_lib_selector_type_template_index_0_single_keyboard_vue__ = __webpack_require__(964);
function injectStyle (ssrContext) {
  __webpack_require__(948)
}
var normalizeComponent = __webpack_require__(0)
/* script */


/* template */

/* template functional */
var __vue_template_functional__ = false
/* styles */
var __vue_styles__ = injectStyle
/* scopeId */
var __vue_scopeId__ = null
/* moduleIdentifier (server only) */
var __vue_module_identifier__ = null
var Component = normalizeComponent(
  __WEBPACK_IMPORTED_MODULE_1__babel_loader_vux_loader_src_script_loader_js_vue_loader_lib_selector_type_script_index_0_single_keyboard_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_2__vue_loader_lib_template_compiler_index_id_data_v_7535d09c_hasScoped_false_buble_transforms_vux_loader_src_before_template_compiler_loader_js_vux_loader_src_template_loader_js_vue_loader_lib_selector_type_template_index_0_single_keyboard_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)

/* harmony default export */ __webpack_exports__["a"] = (Component.exports);


/***/ }),
/* 500 */,
/* 501 */,
/* 502 */,
/* 503 */,
/* 504 */,
/* 505 */,
/* 506 */,
/* 507 */,
/* 508 */,
/* 509 */,
/* 510 */,
/* 511 */,
/* 512 */,
/* 513 */,
/* 514 */,
/* 515 */,
/* 516 */,
/* 517 */,
/* 518 */,
/* 519 */,
/* 520 */,
/* 521 */,
/* 522 */,
/* 523 */,
/* 524 */,
/* 525 */,
/* 526 */,
/* 527 */,
/* 528 */,
/* 529 */,
/* 530 */,
/* 531 */,
/* 532 */,
/* 533 */,
/* 534 */,
/* 535 */,
/* 536 */,
/* 537 */,
/* 538 */,
/* 539 */,
/* 540 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_vux_loader_src_script_loader_js_vux_loader_src_script_loader_js_vue_loader_lib_selector_type_script_index_0_keyboard_view_vue__ = __webpack_require__(950);
/* unused harmony namespace reexport */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__babel_loader_vux_loader_src_script_loader_js_vue_loader_lib_selector_type_script_index_0_keyboard_view_vue__ = __webpack_require__(961);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__vue_loader_lib_template_compiler_index_id_data_v_25ecc905_hasScoped_false_buble_transforms_vux_loader_src_before_template_compiler_loader_js_vux_loader_src_template_loader_js_vue_loader_lib_selector_type_template_index_0_keyboard_view_vue__ = __webpack_require__(962);
var normalizeComponent = __webpack_require__(0)
/* script */


/* template */

/* template functional */
var __vue_template_functional__ = false
/* styles */
var __vue_styles__ = null
/* scopeId */
var __vue_scopeId__ = null
/* moduleIdentifier (server only) */
var __vue_module_identifier__ = null
var Component = normalizeComponent(
  __WEBPACK_IMPORTED_MODULE_1__babel_loader_vux_loader_src_script_loader_js_vue_loader_lib_selector_type_script_index_0_keyboard_view_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_2__vue_loader_lib_template_compiler_index_id_data_v_25ecc905_hasScoped_false_buble_transforms_vux_loader_src_before_template_compiler_loader_js_vux_loader_src_template_loader_js_vue_loader_lib_selector_type_template_index_0_keyboard_view_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)

/* harmony default export */ __webpack_exports__["a"] = (Component.exports);


/***/ }),
/* 541 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_vux_loader_src_script_loader_js_vux_loader_src_script_loader_js_vue_loader_lib_selector_type_script_index_0_key_row_view_vue__ = __webpack_require__(952);
/* unused harmony namespace reexport */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__babel_loader_vux_loader_src_script_loader_js_vue_loader_lib_selector_type_script_index_0_key_row_view_vue__ = __webpack_require__(955);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__vue_loader_lib_template_compiler_index_id_data_v_ec0ad338_hasScoped_false_buble_transforms_vux_loader_src_before_template_compiler_loader_js_vux_loader_src_template_loader_js_vue_loader_lib_selector_type_template_index_0_key_row_view_vue__ = __webpack_require__(956);
function injectStyle (ssrContext) {
  __webpack_require__(951)
}
var normalizeComponent = __webpack_require__(0)
/* script */


/* template */

/* template functional */
var __vue_template_functional__ = false
/* styles */
var __vue_styles__ = injectStyle
/* scopeId */
var __vue_scopeId__ = null
/* moduleIdentifier (server only) */
var __vue_module_identifier__ = null
var Component = normalizeComponent(
  __WEBPACK_IMPORTED_MODULE_1__babel_loader_vux_loader_src_script_loader_js_vue_loader_lib_selector_type_script_index_0_key_row_view_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_2__vue_loader_lib_template_compiler_index_id_data_v_ec0ad338_hasScoped_false_buble_transforms_vux_loader_src_before_template_compiler_loader_js_vux_loader_src_template_loader_js_vue_loader_lib_selector_type_template_index_0_key_row_view_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)

/* harmony default export */ __webpack_exports__["a"] = (Component.exports);


/***/ }),
/* 542 */
/***/ (function(module, exports) {

(function(global, factory) {
  module.exports = factory();
})(this, function() {
  'use strict';

  // 定义键盘引擎内部的一些参数和类型说明
  // Author: 陈哈哈 yoojiachen@gmail.com
  /**
   * 对齐类型
   */
  var KB_ALIGN = {
    // 居中对齐
    CENTER: 'center',
    // 两端对齐
    JUSTIFY: 'justify'
  };

  /**
   * 键盘类型
   */
  var KB_TYPES = {
    // 全键盘
    FULL: 0,
    // 民用
    CIVIL: 1,
    // 民用+特殊车辆
    CIVIL_SPEC: 2
  };

  /**
   * 键位功能类型
   */
  var KEY_TYPES = {
    // 普通按键
    GENERAL: 0,
    // 功能键：删除
    FUN_DEL: 1,
    // 功能键：确定
    FUN_OK: 2,
    // 功能键：更多
    FUN_MORE: 3
  };

  /**
   * 车牌号码类型
   */
  var NUM_TYPES = {
    // 未知类型
    UNKNOWN: -1,
    // 自动探测试
    AUTO_DETECT: 0,
    // 民用车牌
    CIVIL: 1,
    // 2007武警
    WJ2007: 2,
    // 2012武警
    WJ2012: 3,
    // 2012军队车牌
    PLA2012: 4,
    // 新能源车牌
    NEW_ENERGY: 5,
    // 2007大使馆车牌
    SHI2007: 6,
    // 2017新大使馆车牌
    SHI2017: 7,
    // 民航摆渡车
    AVIATION: 8,

    nameOf: function(mode) {
      switch (mode) {
        case -1:
          return 'UNKNOWN';
        case 0:
          return 'AUTO_DETECT';
        case 1:
          return 'CIVIL';
        case 2:
          return 'WJ2007';
        case 3:
          return 'WJ2012';
        case 4:
          return 'PLA2012';
        case 5:
          return 'NEW_ENERGY';
        case 6:
          return 'SHI2007';
        case 7:
          return 'SHI2017';
        case 8:
          return 'AVIATION';
        default:
          return 'UNKNOWN';
      }
    },

    lenOf: function(mode) {
      switch (mode) {
        case 3 /*2012武警*/:
        case 5 /*新能源*/:
          return 8;
        default:
          return 7;
      }
    }
  };

  var _NUM = '1234567890';
  var _LETTERS = 'QWERTYUPASDFGHJKLZXCVBNM';
  var _JING = '警';
  var _123 = '123';
  var _DF = 'DF';
  var _SHI2007 = '使';
  var _HK = '港';
  var _MACAO = '澳';
  var _DEL = '-';
  var _OK = '+';

  return {
    KB_TYPES: KB_TYPES,
    KEY_TYPES: KEY_TYPES,
    NUM_TYPES: NUM_TYPES,
    KB_ALIGN: KB_ALIGN,

    S_CIVIL_PVS:
      '京津沪晋冀蒙辽吉黑苏浙皖闽赣鲁豫鄂湘粤桂琼渝川贵云藏陕甘青宁新',
    S_PLA2012_PVS: 'QVKHBSLJNGCEZ',
    S_PLA2012_AREA: 'ABCDEFGHJKLMNOPRSTUVXY',
    S_NUM: _NUM,
    S_LETTERS: _LETTERS,
    S_CHARS: _NUM + _LETTERS,
    C_SHI2007: _SHI2007,
    C_HK: _HK,
    C_MACAO: _MACAO,
    C_XUE: '学',
    C_JING: _JING,
    C_MIN: '民',
    C_HANG: '航',
    S_POSTFIX_ZH: _JING + '挂领试超',
    C_W: 'W',
    C_J: 'J',
    C_O: 'O',
    S_DF: _DF,
    S_123: _123,
    S_NEW_ENERGY: _123 + _DF,
    S_Q_IOP: 'QWERTYUIOP',
    S_Q_OP: 'QWERTYUOP',
    S_Q_P: 'QWERTYUP',
    S_A_L: 'ASDFGHJKL',
    S_Z_M: 'ZXCVBNM',
    S_HK_MACAO: _HK + _MACAO,
    S_SHI2007_PVS: _SHI2007 + _123,
    C_DEL: _DEL,
    C_OK: _OK,
    C_MORE: '=',
    S_DEL_OK: _DEL + _OK
  };
});


/***/ }),
/* 543 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_vux_loader_src_script_loader_js_vux_loader_src_script_loader_js_vue_loader_lib_selector_type_script_index_0_shortcut_view_vue__ = __webpack_require__(958);
/* unused harmony namespace reexport */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__babel_loader_vux_loader_src_script_loader_js_vue_loader_lib_selector_type_script_index_0_shortcut_view_vue__ = __webpack_require__(959);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__vue_loader_lib_template_compiler_index_id_data_v_370b9bd2_hasScoped_true_buble_transforms_vux_loader_src_before_template_compiler_loader_js_vux_loader_src_template_loader_js_vue_loader_lib_selector_type_template_index_0_shortcut_view_vue__ = __webpack_require__(960);
function injectStyle (ssrContext) {
  __webpack_require__(957)
}
var normalizeComponent = __webpack_require__(0)
/* script */


/* template */

/* template functional */
var __vue_template_functional__ = false
/* styles */
var __vue_styles__ = injectStyle
/* scopeId */
var __vue_scopeId__ = "data-v-370b9bd2"
/* moduleIdentifier (server only) */
var __vue_module_identifier__ = null
var Component = normalizeComponent(
  __WEBPACK_IMPORTED_MODULE_1__babel_loader_vux_loader_src_script_loader_js_vue_loader_lib_selector_type_script_index_0_shortcut_view_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_2__vue_loader_lib_template_compiler_index_id_data_v_370b9bd2_hasScoped_true_buble_transforms_vux_loader_src_before_template_compiler_loader_js_vux_loader_src_template_loader_js_vue_loader_lib_selector_type_template_index_0_shortcut_view_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)

/* harmony default export */ __webpack_exports__["a"] = (Component.exports);


/***/ }),
/* 544 */
/***/ (function(module, exports) {

module.exports = (function() {
  'use strict';

  function Province(name, shortname) {
    this.name = name;
    this.shortname = shortname;
    this.periphery = new Array();

    this.lnk = function(item) {
      if (!this.contains(item)) {
        // 互相关联
        this.periphery.push(item);
        item.lnk(this);
      }
      return this;
    };

    this.contains = function(item) {
      return (
        this.periphery.filter(function(i) {
          return i === item;
        }).length !== 0
      );
    };

    this.peripheryShortnames = function() {
      var peripheries = this.periphery.map(function(i) {
        return i.shortname;
      });
      peripheries.unshift(this.shortname);
      return peripheries;
    };
  }

  var beijing = new Province('北京市', '京');
  var tianjin = new Province('天津市', '津');
  var shanxi = new Province('山西省', '晋');
  var hebei = new Province('河北省', '冀');
  var neimeng = new Province('内蒙古自治区', '蒙');
  var liaoning = new Province('辽宁省', '辽');
  var jilin = new Province('吉林省', '吉');
  var heilongjiang = new Province('黑龙江省', '黑');
  var shanghai = new Province('上海市', '沪');
  var jiangsu = new Province('江苏省', '苏');
  var zhejiang = new Province('浙江省', '浙');
  var anhui = new Province('安徽省', '皖');
  var fujian = new Province('福建省', '闽');
  var jiangxi = new Province('江西省', '赣');
  var shandong = new Province('山东省', '鲁');
  var henan = new Province('河南省', '豫');
  var hubei = new Province('湖北省', '鄂');
  var hunan = new Province('湖南省', '湘');
  var guangdong = new Province('广东省', '粤');
  var guangxi = new Province('广西壮族自治区', '桂');
  var hainan = new Province('海南省', '琼');
  var chongqing = new Province('重庆市', '渝');
  var sichuan = new Province('四川省', '川');
  var guizhou = new Province('贵州省', '贵');
  var yunnan = new Province('云南省', '云');
  var xizang = new Province('西藏自治区', '藏');
  var shannxi = new Province('陕西省', '陕');
  var gansu = new Province('甘肃省', '甘');
  var qinghai = new Province('青海省', '青');
  var ningxia = new Province('宁夏回族自治区', '宁');
  var xinjiang = new Province('新疆维吾尔自治区', '新');

  xinjiang
    .lnk(xizang)
    .lnk(qinghai)
    .lnk(gansu)
    .lnk(neimeng);

  xizang
    .lnk(qinghai)
    .lnk(sichuan)
    .lnk(yunnan);

  qinghai
    .lnk(gansu)
    .lnk(sichuan)
    .lnk(shanxi);

  gansu
    .lnk(neimeng)
    .lnk(shannxi)
    .lnk(sichuan)
    .lnk(chongqing)
    .lnk(ningxia);

  ningxia.lnk(shannxi).lnk(gansu);

  neimeng
    .lnk(heilongjiang)
    .lnk(jilin)
    .lnk(liaoning)
    .lnk(hebei)
    .lnk(beijing)
    .lnk(tianjin)
    .lnk(shanxi)
    .lnk(shannxi)
    .lnk(ningxia);

  shannxi
    .lnk(shanxi)
    .lnk(henan)
    .lnk(hubei)
    .lnk(chongqing)
    .lnk(sichuan);

  sichuan
    .lnk(yunnan)
    .lnk(guizhou)
    .lnk(chongqing);

  yunnan.lnk(guizhou).lnk(guangxi);

  guizhou
    .lnk(hunan)
    .lnk(guangxi)
    .lnk(chongqing)
    .lnk(hubei);

  chongqing.lnk(hubei).lnk(hunan);

  hubei
    .lnk(hunan)
    .lnk(henan)
    .lnk(anhui)
    .lnk(jiangxi);

  hunan
    .lnk(jiangxi)
    .lnk(guangxi)
    .lnk(guangdong);

  guangxi.lnk(guangdong).lnk(hainan);

  guangdong
    .lnk(hainan)
    .lnk(fujian)
    .lnk(jiangxi);

  jiangxi
    .lnk(fujian)
    .lnk(anhui)
    .lnk(zhejiang);

  fujian.lnk(zhejiang);

  zhejiang
    .lnk(shanghai)
    .lnk(anhui)
    .lnk(jiangsu);

  anhui
    .lnk(jiangsu)
    .lnk(shanghai)
    .lnk(shandong);

  jiangsu.lnk(shandong).lnk(shanghai);

  shandong
    .lnk(hebei)
    .lnk(beijing)
    .lnk(tianjin);

  shanxi.lnk(hebei).lnk(henan);

  hebei
    .lnk(beijing)
    .lnk(tianjin)
    .lnk(shandong)
    .lnk(liaoning);

  beijing
    .lnk(tianjin)
    .lnk(liaoning)
    .lnk(shandong);

  liaoning.lnk(jilin);

  jilin.lnk(liaoning).lnk(heilongjiang);

  var _binding = function() {
    this._provinces = [
      beijing,
      tianjin,
      shanxi,
      hebei,
      neimeng,
      liaoning,
      jilin,
      heilongjiang,
      shanghai,
      jiangsu,
      zhejiang,
      anhui,
      fujian,
      jiangxi,
      shandong,
      henan,
      hubei,
      hunan,
      guangdong,
      guangxi,
      hainan,
      chongqing,
      sichuan,
      guizhou,
      yunnan,
      xizang,
      shannxi,
      gansu,
      qinghai,
      ningxia,
      xinjiang
    ];
  };

  _binding.prototype.locationOf = function(name) {
    var found = this._provinces.filter(function(i) {
      return i.name.indexOf(name) >= 0;
    });
    if (found.length >= 1) {
      return found[0];
    }
    return new Province();
  };

  return new _binding();
})();


/***/ }),
/* 545 */,
/* 546 */,
/* 547 */,
/* 548 */,
/* 549 */,
/* 550 */,
/* 551 */,
/* 552 */,
/* 553 */,
/* 554 */,
/* 555 */,
/* 556 */,
/* 557 */,
/* 558 */,
/* 559 */,
/* 560 */,
/* 561 */,
/* 562 */,
/* 563 */,
/* 564 */,
/* 565 */,
/* 566 */,
/* 567 */,
/* 568 */,
/* 569 */,
/* 570 */,
/* 571 */,
/* 572 */,
/* 573 */,
/* 574 */,
/* 575 */,
/* 576 */,
/* 577 */,
/* 578 */,
/* 579 */,
/* 580 */,
/* 581 */,
/* 582 */,
/* 583 */,
/* 584 */,
/* 585 */,
/* 586 */,
/* 587 */,
/* 588 */,
/* 589 */,
/* 590 */,
/* 591 */,
/* 592 */,
/* 593 */,
/* 594 */,
/* 595 */,
/* 596 */,
/* 597 */,
/* 598 */,
/* 599 */,
/* 600 */,
/* 601 */,
/* 602 */,
/* 603 */,
/* 604 */,
/* 605 */,
/* 606 */,
/* 607 */,
/* 608 */,
/* 609 */,
/* 610 */,
/* 611 */,
/* 612 */,
/* 613 */,
/* 614 */,
/* 615 */,
/* 616 */,
/* 617 */,
/* 618 */,
/* 619 */,
/* 620 */,
/* 621 */,
/* 622 */,
/* 623 */,
/* 624 */,
/* 625 */,
/* 626 */,
/* 627 */,
/* 628 */,
/* 629 */,
/* 630 */,
/* 631 */,
/* 632 */,
/* 633 */,
/* 634 */,
/* 635 */,
/* 636 */,
/* 637 */,
/* 638 */,
/* 639 */,
/* 640 */,
/* 641 */,
/* 642 */,
/* 643 */,
/* 644 */,
/* 645 */,
/* 646 */,
/* 647 */,
/* 648 */,
/* 649 */,
/* 650 */,
/* 651 */,
/* 652 */,
/* 653 */,
/* 654 */,
/* 655 */,
/* 656 */,
/* 657 */,
/* 658 */,
/* 659 */,
/* 660 */,
/* 661 */,
/* 662 */,
/* 663 */,
/* 664 */,
/* 665 */,
/* 666 */,
/* 667 */,
/* 668 */,
/* 669 */,
/* 670 */,
/* 671 */,
/* 672 */,
/* 673 */,
/* 674 */,
/* 675 */,
/* 676 */,
/* 677 */,
/* 678 */,
/* 679 */,
/* 680 */,
/* 681 */,
/* 682 */,
/* 683 */,
/* 684 */,
/* 685 */,
/* 686 */,
/* 687 */,
/* 688 */,
/* 689 */,
/* 690 */,
/* 691 */,
/* 692 */,
/* 693 */,
/* 694 */,
/* 695 */,
/* 696 */,
/* 697 */,
/* 698 */,
/* 699 */,
/* 700 */,
/* 701 */,
/* 702 */,
/* 703 */,
/* 704 */,
/* 705 */,
/* 706 */,
/* 707 */,
/* 708 */,
/* 709 */,
/* 710 */,
/* 711 */,
/* 712 */,
/* 713 */,
/* 714 */,
/* 715 */,
/* 716 */,
/* 717 */,
/* 718 */,
/* 719 */,
/* 720 */,
/* 721 */,
/* 722 */,
/* 723 */,
/* 724 */,
/* 725 */,
/* 726 */,
/* 727 */,
/* 728 */,
/* 729 */,
/* 730 */,
/* 731 */,
/* 732 */,
/* 733 */,
/* 734 */,
/* 735 */,
/* 736 */,
/* 737 */,
/* 738 */,
/* 739 */,
/* 740 */,
/* 741 */,
/* 742 */,
/* 743 */,
/* 744 */,
/* 745 */,
/* 746 */,
/* 747 */,
/* 748 */,
/* 749 */,
/* 750 */,
/* 751 */,
/* 752 */,
/* 753 */,
/* 754 */,
/* 755 */,
/* 756 */,
/* 757 */,
/* 758 */,
/* 759 */,
/* 760 */,
/* 761 */,
/* 762 */,
/* 763 */,
/* 764 */,
/* 765 */,
/* 766 */,
/* 767 */,
/* 768 */,
/* 769 */,
/* 770 */,
/* 771 */,
/* 772 */,
/* 773 */,
/* 774 */,
/* 775 */,
/* 776 */,
/* 777 */,
/* 778 */,
/* 779 */,
/* 780 */,
/* 781 */,
/* 782 */,
/* 783 */,
/* 784 */,
/* 785 */,
/* 786 */,
/* 787 */,
/* 788 */,
/* 789 */,
/* 790 */,
/* 791 */,
/* 792 */,
/* 793 */,
/* 794 */,
/* 795 */,
/* 796 */,
/* 797 */,
/* 798 */,
/* 799 */,
/* 800 */,
/* 801 */,
/* 802 */,
/* 803 */,
/* 804 */,
/* 805 */,
/* 806 */,
/* 807 */,
/* 808 */,
/* 809 */,
/* 810 */,
/* 811 */,
/* 812 */,
/* 813 */,
/* 814 */,
/* 815 */,
/* 816 */,
/* 817 */,
/* 818 */,
/* 819 */,
/* 820 */,
/* 821 */,
/* 822 */,
/* 823 */,
/* 824 */,
/* 825 */,
/* 826 */,
/* 827 */,
/* 828 */,
/* 829 */,
/* 830 */,
/* 831 */,
/* 832 */,
/* 833 */,
/* 834 */,
/* 835 */,
/* 836 */,
/* 837 */,
/* 838 */,
/* 839 */,
/* 840 */,
/* 841 */,
/* 842 */,
/* 843 */,
/* 844 */,
/* 845 */,
/* 846 */,
/* 847 */,
/* 848 */,
/* 849 */,
/* 850 */,
/* 851 */,
/* 852 */,
/* 853 */,
/* 854 */,
/* 855 */,
/* 856 */,
/* 857 */,
/* 858 */,
/* 859 */,
/* 860 */,
/* 861 */,
/* 862 */,
/* 863 */,
/* 864 */,
/* 865 */,
/* 866 */,
/* 867 */,
/* 868 */,
/* 869 */,
/* 870 */,
/* 871 */,
/* 872 */,
/* 873 */,
/* 874 */,
/* 875 */,
/* 876 */,
/* 877 */,
/* 878 */,
/* 879 */,
/* 880 */,
/* 881 */,
/* 882 */,
/* 883 */,
/* 884 */,
/* 885 */,
/* 886 */,
/* 887 */,
/* 888 */,
/* 889 */,
/* 890 */,
/* 891 */,
/* 892 */,
/* 893 */,
/* 894 */,
/* 895 */,
/* 896 */,
/* 897 */,
/* 898 */,
/* 899 */,
/* 900 */,
/* 901 */,
/* 902 */,
/* 903 */,
/* 904 */,
/* 905 */,
/* 906 */,
/* 907 */,
/* 908 */,
/* 909 */,
/* 910 */,
/* 911 */,
/* 912 */,
/* 913 */,
/* 914 */,
/* 915 */,
/* 916 */,
/* 917 */,
/* 918 */,
/* 919 */,
/* 920 */,
/* 921 */,
/* 922 */,
/* 923 */,
/* 924 */,
/* 925 */,
/* 926 */,
/* 927 */,
/* 928 */,
/* 929 */,
/* 930 */,
/* 931 */,
/* 932 */,
/* 933 */,
/* 934 */,
/* 935 */,
/* 936 */,
/* 937 */,
/* 938 */,
/* 939 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_vue_router__ = __webpack_require__(357);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__indexApp__ = __webpack_require__(940);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__monthlyApp__ = __webpack_require__(972);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_3__historyApp__ = __webpack_require__(977);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_4__bindApp__ = __webpack_require__(982);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_5__timePayApp__ = __webpack_require__(988);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_6__monthPayApp__ = __webpack_require__(993);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_7__historyListApp__ = __webpack_require__(998);











var routes = [{
    path: '/',
    name: 'index',
    component: __WEBPACK_IMPORTED_MODULE_1__indexApp__["a" /* default */]
}, {
    path: '/monthly',
    name: 'monthly',
    component: __WEBPACK_IMPORTED_MODULE_2__monthlyApp__["a" /* default */]
}, {
    path: '/history',
    name: 'history',
    component: __WEBPACK_IMPORTED_MODULE_3__historyApp__["a" /* default */]
}, {
    path: '/bind',
    name: 'bind',
    component: __WEBPACK_IMPORTED_MODULE_4__bindApp__["a" /* default */]
}, {
    path: '/timepay/:carno',
    name: 'timepay',
    component: __WEBPACK_IMPORTED_MODULE_5__timePayApp__["a" /* default */]
}, {
    path: '/monthpay/:pid/:carno',
    name: 'monthpay',
    component: __WEBPACK_IMPORTED_MODULE_6__monthPayApp__["a" /* default */]
}, {
    path: '/monthpay/:pid/:carno',
    name: 'monthpay',
    component: __WEBPACK_IMPORTED_MODULE_6__monthPayApp__["a" /* default */]
}, {
    path: '/historylist/:category',
    name: 'historylist',
    component: __WEBPACK_IMPORTED_MODULE_7__historyListApp__["a" /* default */]
}];

Vue.use(__WEBPACK_IMPORTED_MODULE_0_vue_router__["a" /* default */]);

var router = new __WEBPACK_IMPORTED_MODULE_0_vue_router__["a" /* default */]({
    routes: routes
});

new Vue({
    router: router
}).$mount('#app');

/***/ }),
/* 940 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vux_loader_src_script_loader_js_node_modules_vux_loader_src_script_loader_js_node_modules_vue_loader_lib_selector_type_script_index_0_indexApp_vue__ = __webpack_require__(943);
/* unused harmony namespace reexport */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__babel_loader_node_modules_vux_loader_src_script_loader_js_node_modules_vue_loader_lib_selector_type_script_index_0_indexApp_vue__ = __webpack_require__(970);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__node_modules_vue_loader_lib_template_compiler_index_id_data_v_31fb3736_hasScoped_true_buble_transforms_node_modules_vux_loader_src_before_template_compiler_loader_js_node_modules_vux_loader_src_template_loader_js_node_modules_vue_loader_lib_selector_type_template_index_0_indexApp_vue__ = __webpack_require__(971);
function injectStyle (ssrContext) {
  __webpack_require__(941)
  __webpack_require__(942)
}
var normalizeComponent = __webpack_require__(0)
/* script */


/* template */

/* template functional */
var __vue_template_functional__ = false
/* styles */
var __vue_styles__ = injectStyle
/* scopeId */
var __vue_scopeId__ = "data-v-31fb3736"
/* moduleIdentifier (server only) */
var __vue_module_identifier__ = null
var Component = normalizeComponent(
  __WEBPACK_IMPORTED_MODULE_1__babel_loader_node_modules_vux_loader_src_script_loader_js_node_modules_vue_loader_lib_selector_type_script_index_0_indexApp_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_2__node_modules_vue_loader_lib_template_compiler_index_id_data_v_31fb3736_hasScoped_true_buble_transforms_node_modules_vux_loader_src_before_template_compiler_loader_js_node_modules_vux_loader_src_template_loader_js_node_modules_vue_loader_lib_selector_type_template_index_0_indexApp_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)

/* harmony default export */ __webpack_exports__["a"] = (Component.exports);


/***/ }),
/* 941 */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),
/* 942 */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),
/* 943 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_assets_js_Lib__ = __webpack_require__(3);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_assets_js_api__ = __webpack_require__(4);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2_assets_js_router__ = __webpack_require__(40);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_3_vehicle_keyboard__ = __webpack_require__(473);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_4_components__ = __webpack_require__(34);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_5_vux_src_components_view_box_index_vue__ = __webpack_require__(7);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_6_vux_src_components_msg_index_vue__ = __webpack_require__(116);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_7_vux_src_components_x_button_index_vue__ = __webpack_require__(12);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_8_vux_src_components_popup_index_vue__ = __webpack_require__(51);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_9_vux_src_directives_transfer_dom_index_js__ = __webpack_require__(6);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_10_vux_src_components_tabbar_tabbar_vue__ = __webpack_require__(19);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_11_vux_src_components_tabbar_tabbar_item_vue__ = __webpack_require__(25);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_12_vux_src_components_x_header_index_vue__ = __webpack_require__(9);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_13_vux_src_components_x_dialog_index_vue__ = __webpack_require__(68);
















/* unused harmony default export */ var _unused_webpack_default_export = ({
  directives: {
    TransferDom: __WEBPACK_IMPORTED_MODULE_9_vux_src_directives_transfer_dom_index_js__["a" /* default */]
  },
  data: function data() {
    var _this = this;

    return {
      keyboardPopupIng: false,
      presetNumber: '',
      placeholderShow: true,
      cursorShow: false,
      args: {

        presetNumber: '',
        keyboardType: 0,
        forceChangeMode: true,
        provinceName: '',
        autoComplete: true,
        showKeyTips: true,
        position: 'bottom'
      },
      callbacks: {

        onchanged: function onchanged(presetNumber, isCompleted) {
          _this.args.presetNumber = presetNumber;
          console.log('当前车牌[变更]：' + presetNumber + ', 已完成:' + isCompleted);
        },

        onkeypressed: function onkeypressed(key) {
          console.log('当前按键：' + key.text);
        },

        oncompleted: function oncompleted(presetNumber, isAutoCompleted) {
          _this.presetNumber = presetNumber;
          _this.keyboardPopupIng = false;
          _this.numberArray = presetNumber.split('');
          console.log('当前车牌[完成]：' + presetNumber + ', 自动完成:' + isAutoCompleted);
        },

        onmessage: function onmessage(message) {
          console.info(message);
        }
      },
      lpnList: [],
      defaultTit: '',
      loadIng: false,
      settingItems: [],
      px: 0,
      setDefaultDialog: false,
      delDefaultDialog: false,
      dialogName: '',
      dialogId: 0,
      dialogType: 1,
      delIndex: 0
    };
  },

  components: {
    ViewBox: __WEBPACK_IMPORTED_MODULE_5_vux_src_components_view_box_index_vue__["a" /* default */], Msg: __WEBPACK_IMPORTED_MODULE_6_vux_src_components_msg_index_vue__["a" /* default */], XButton: __WEBPACK_IMPORTED_MODULE_7_vux_src_components_x_button_index_vue__["a" /* default */], Popup: __WEBPACK_IMPORTED_MODULE_8_vux_src_components_popup_index_vue__["a" /* default */], Tabbar: __WEBPACK_IMPORTED_MODULE_10_vux_src_components_tabbar_tabbar_vue__["a" /* default */], TabbarItem: __WEBPACK_IMPORTED_MODULE_11_vux_src_components_tabbar_tabbar_item_vue__["a" /* default */], MixedKeyboard: __WEBPACK_IMPORTED_MODULE_3_vehicle_keyboard__["a" /* MixedKeyboard */], LnLoading: __WEBPACK_IMPORTED_MODULE_4_components__["d" /* LnLoading */], XHeader: __WEBPACK_IMPORTED_MODULE_12_vux_src_components_x_header_index_vue__["a" /* default */], XDialog: __WEBPACK_IMPORTED_MODULE_13_vux_src_components_x_dialog_index_vue__["a" /* default */]
  },

  beforeCreate: function beforeCreate() {
    __WEBPACK_IMPORTED_MODULE_0_assets_js_Lib__["a" /* default */].M.setTitle('停车');
  },
  beforeMount: function beforeMount() {
    this.getCatList();
    this.getSetting();
  },

  methods: {
    getSetting: function getSetting() {
      var _this2 = this;

      __WEBPACK_IMPORTED_MODULE_1_assets_js_api__["a" /* default */].setting().then(function (res) {
        _this2.settingItems = res.data;
        _this2.px = _this2.settingItems.hstatus ? '46px' : '0';
      });
    },
    getShortPayInfo: function getShortPayInfo(defaultTit) {
      var _this3 = this;

      if (defaultTit === '正在加载') {
        setTimeout(function () {
          _this3.loadIng = false;
        }, 500);
        return;
      }
      var data = {
        carno: defaultTit
      };
      __WEBPACK_IMPORTED_MODULE_1_assets_js_api__["a" /* default */].getShortPayInfo(data).then(function (res) {
        if (res.err_code === 0) {
          if (res.data.code === 10003) {
            _this3.$vux.toast.show({
              type: 'text',
              text: res.data.content,
              width: '17em'
            });
          } else {
            _this3.goUrl('/timepay/' + defaultTit);
          }
          setTimeout(function () {
            _this3.loadIng = false;
          }, 500);
        }
      });
    },
    onPost: function onPost() {
      var _this4 = this;

      var data = {
        carno: this.presetNumber,
        type: 2
      };
      __WEBPACK_IMPORTED_MODULE_1_assets_js_api__["a" /* default */].addCarInfo(data).then(function (res) {
        if (res.err_code === 0) {
          _this4.$vux.toast.show({
            text: '解除绑定成功',
            type: 'success'
          });
          _this4.defaultTit = _this4.presetNumber;
          _this4.placeholderShow = true;
          _this4.cursorShow = false;
          _this4.presetNumber = '';
          _this4.getCatList();
        }
      });
    },
    bindCancelHandler: function bindCancelHandler(carid, carname, index) {
      this.dialogName = carname;
      this.delDefaultDialog = true;
      this.dialogId = carid;
      this.delIndex = index;
    },
    setDefaultHandler: function setDefaultHandler(carid, carname, index) {
      this.dialogName = carname;
      this.setDefaultDialog = true;
      this.dialogId = carid;
    },
    confirmDialog: function confirmDialog(type) {
      var _this5 = this;

      var self = this;
      if (type === 1) {
        __WEBPACK_IMPORTED_MODULE_1_assets_js_api__["a" /* default */].changeUserCar(this.dialogId).then(function (res) {
          _this5.setDefaultDialog = false;
          if (res.err_code === 0) {
            _this5.defaultTit = _this5.presetNumber;
            _this5.getCatList();
            _this5.$vux.toast.show({
              text: '设置成功',
              type: 'success'
            });
          }
        }).catch(function (error) {
          self.showDialog = false;
          self.$vux.toast.show({
            type: 'warn',
            text: error.err_msg,
            width: '14em'
          });
        });
      } else {
        __WEBPACK_IMPORTED_MODULE_1_assets_js_api__["a" /* default */].delCarInfo(this.dialogId).then(function (res) {
          _this5.delDefaultDialog = false;
          if (res.err_code === 0) {
            _this5.$vux.toast.show({
              text: '解除绑定成功',
              type: 'success'
            });
            _this5.lpnList.splice(_this5.delIndex, 1);
          }
        }).catch(function (error) {
          self.delDefaultDialog = false;
          self.$vux.toast.show({
            type: 'warn',
            text: error.err_msg,
            width: '14em'
          });
        });
      }
    },
    hideDialog: function hideDialog(type) {
      if (type === 1) {
        this.setDefaultDialog = false;
      } else {
        this.delDefaultDialog = false;
      }
    },
    showOrHideOpHandler: function showOrHideOpHandler(index) {
      this.lpnList[index].showHandler = !this.lpnList[index].showHandler;
    },
    getCatList: function getCatList() {
      var _this6 = this;

      __WEBPACK_IMPORTED_MODULE_1_assets_js_api__["a" /* default */].getCarsList().then(function (res) {
        _this6.defaultTit = '正在加载';
        if (res.err_code === 0) {
          var list = res.data;
          var ret = [];
          list.forEach(function (item, index) {
            if (item.enable === 1 || item.enable === '1') {
              _this6.defaultTit = item.carno;
            }
            ret.push({
              id: item.carid,
              carno: item.carno,
              enable: item.enable,
              showHandler: false
            });
          });
          _this6.lpnList = ret;
        }
        if (_this6.defaultTit) {
          _this6.loadIng = true;
          _this6.getShortPayInfo(_this6.defaultTit);
        }
      });
    },
    inputFun: function inputFun() {
      this.placeholderShow = false;
      this.cursorShow = true;
      this.keyboardPopupIng = true;
    },
    goUrl: function goUrl(url) {
      url && Object(__WEBPACK_IMPORTED_MODULE_2_assets_js_router__["a" /* go */])(url, this.$router);
    },
    url: function url() {
      return __WEBPACK_IMPORTED_MODULE_0_assets_js_Lib__["a" /* default */].M.url('home');
    }
  }
});

/***/ }),
/* 944 */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),
/* 945 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__tap__ = __webpack_require__(419);
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//


/* unused harmony default export */ var _unused_webpack_default_export = ({
  directives: __WEBPACK_IMPORTED_MODULE_0__tap__["a" /* default */],
  name: 'number-view',
  props: {
    /**
     * 号码数组
     */
    numberArray: {
      type: Array,
      default: function _default() {
        return [];
      }
    },
    /**
     * 用于显示的车牌模式（0：自动探测，5：新能源）
     * @link{engine.NUM_TYPES}
     */
    numberType: Number,
    /**
     * 选中的号码索引
     */
    currentIndex: {
      type: Number,
      default: -1
    }
  },
  data: function data() {
    return {
      model: '1'
    };
  },
  mounted: function mounted() {
    try {
      document.querySelector('.mode--radio[value="' + this.model + '"]').checked = true;
    } catch (error) {
      console.error(error);
    }
  },

  methods: {
    showHolder: function showHolder(text, index, currentIndex) {
      return index === currentIndex && (!text || text === '' || text.length === 0);
    },
    handleHolder: function handleHolder(text, index, currentIndex) {
      return this.showHolder(text, index, currentIndex) ? '|' : text;
    },

    /**
     * 重置Mode
     */
    resetMode: function resetMode() {
      this.model = this.model === '1' ? '2' : '1';
    },

    /**
     * 车牌显示模式切换
     */
    onModeChanged: function onModeChanged() {
      this.$emit('modechanged');
    },

    /**
     * 车牌号单元格选中事件
     * @param {Number} index 选中单元格下标
     */
    onCellSelected: function onCellSelected(index) {
      this.$emit('cellselected', index, true);
    }
  }
});

/***/ }),
/* 946 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__tap__ = __webpack_require__(419);
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//


/* harmony default export */ __webpack_exports__["a"] = ({
  directives: __WEBPACK_IMPORTED_MODULE_0__tap__["a" /* default */],
  name: 'number-view',
  props: {
    /**
     * 号码数组
     */
    numberArray: {
      type: Array,
      default: function _default() {
        return [];
      }
    },
    /**
     * 用于显示的车牌模式（0：自动探测，5：新能源）
     * @link{engine.NUM_TYPES}
     */
    numberType: Number,
    /**
     * 选中的号码索引
     */
    currentIndex: {
      type: Number,
      default: -1
    }
  },
  data: function data() {
    return {
      model: '1'
    };
  },
  mounted: function mounted() {
    try {
      document.querySelector('.mode--radio[value="' + this.model + '"]').checked = true;
    } catch (error) {
      console.error(error);
    }
  },

  methods: {
    showHolder: function showHolder(text, index, currentIndex) {
      return index === currentIndex && (!text || text === '' || text.length === 0);
    },
    handleHolder: function handleHolder(text, index, currentIndex) {
      return this.showHolder(text, index, currentIndex) ? '|' : text;
    },

    /**
     * 重置Mode
     */
    resetMode: function resetMode() {
      this.model = this.model === '1' ? '2' : '1';
    },

    /**
     * 车牌显示模式切换
     */
    onModeChanged: function onModeChanged() {
      this.$emit('modechanged');
    },

    /**
     * 车牌号单元格选中事件
     * @param {Number} index 选中单元格下标
     */
    onCellSelected: function onCellSelected(index) {
      this.$emit('cellselected', index, true);
    }
  }
});

/***/ }),
/* 947 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{staticClass:"r-border vehicle-input-widget"},[_c('div',{staticClass:"input-area"},[_c('ul',{staticClass:"inputrow"},_vm._l((_vm.numberArray),function(text,index){return _c('li',{key:index,staticClass:"cell",class:['lengthof-' + _vm.numberArray.length, 
                  {'show-holder': _vm.showHolder(text, index,_vm.currentIndex)}],attrs:{"selected":(index === _vm.currentIndex)}},[_c('button',{directives:[{name:"tap",rawName:"v-tap",value:({method: _vm.onCellSelected, params: { index: index }}),expression:"{method: onCellSelected, params: { index }}"}],staticClass:"key",style:({'line-height': _vm.numberType !== undefined ? '1' : '180%'})},[_vm._v("\n          "+_vm._s(_vm.handleHolder(text, index, _vm.currentIndex))+"\n        ")])])}))]),_vm._v(" "),_c('div',{staticClass:"mode-switcher"},[_c('label',{staticClass:"mode--label"},[_c('input',{directives:[{name:"model",rawName:"v-model",value:(_vm.model),expression:"model"}],staticClass:"mode--radio",attrs:{"type":"radio","value":"1","name":"mode-switcher"},domProps:{"checked":_vm._q(_vm.model,"1")},on:{"change":[function($event){_vm.model="1"},_vm.onModeChanged]}}),_vm._v(" "),_c('span',{staticClass:"mode--radioInput"}),_vm._v(" "),_c('span',{staticClass:"mode--radioText"},[_vm._v("普通车牌")])]),_vm._v(" "),_c('label',{staticClass:"mode--label"},[_c('input',{directives:[{name:"model",rawName:"v-model",value:(_vm.model),expression:"model"}],staticClass:"mode--radio",attrs:{"type":"radio","value":"2","name":"mode-switcher"},domProps:{"checked":_vm._q(_vm.model,"2")},on:{"change":[function($event){_vm.model="2"},_vm.onModeChanged]}}),_vm._v(" "),_c('span',{staticClass:"mode--radioInput"}),_vm._v(" "),_c('span',{staticClass:"mode--radioText"},[_vm._v("新能源车牌")])])])])}
var staticRenderFns = []
var esExports = { render: render, staticRenderFns: staticRenderFns }
/* harmony default export */ __webpack_exports__["a"] = (esExports);

/***/ }),
/* 948 */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),
/* 949 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__keyboard_view_vue__ = __webpack_require__(540);
//
//
//
//
//
//
//
//
//
//


var engine = __webpack_require__(420);
var provinces = __webpack_require__(544);
/* unused harmony default export */ var _unused_webpack_default_export = ({
  name: 'vechicle-single-keyboard',
  props: {
    /**
     * 参数配置项
     * @param {String} presetNumber 预设车牌号
     * @param {Number} keyboardType 键盘类型[0:全键盘，1：民用, 2：民用+武警]
     * @param {Number} currentIndex 当前输入位置
     * @param {String} provinceName 默认省份
     * @param {Number} numberType 用户预设车牌输入类型 0：自动探测车牌类型，5:新能源车牌(engine.NUM_TYPES)
     * @param {Boolean} autoComplete 是否自动完成
     * @param {Boolean} showConfirm 是否显示确定按钮
     * @param {Boolean} showKeyTips 是否显示按键提示框(点击按键弹出当前按键内容提示，类似输入法)
     * @param {String} align //按键对齐方式，取值范围 [center: 居中对齐，经典键盘模式(默认), justify: 两端对齐，位数不够补充空白]
     */
    args: {
      type: Object,
      default: function _default() {}
    },
    /**
     * 回掉接口
     */
    callbacks: {
      type: Object,
      default: function _default() {
        return {
          /**按键监听*/
          onkeypressed: function onkeypressed(key) {},

          /**车牌改变监听(车牌号，是否已完成)*/
          onchanged: function onchanged(presetNumber, isCompleted) {},

          /**车牌完成监听(车牌号，是否自动完成)*/
          oncompleted: function oncompleted(presetNumber, isAutoCompleted) {},

          /**错误回调*/
          onmessage: function onmessage(message) {},

          /**键盘更新回调*/
          updateKeyboard: function updateKeyboard() {}
        };
      }
    }
  },
  data: function data() {
    return {
      options: {
        presetNumber: '', //预设车牌号码
        keyboardType: 1, //键盘类型[0:全键盘，1：民用, 2：民用+武警]
        currentIndex: 0, //当前输入位置
        numberType: 0, //用户预设车牌输入类型 0：自动探测车牌类型，5:新能源车牌(engine.NUM_TYPES)
        provinceName: '', //省份
        showShortCut: false, //是否显示快捷省份
        autoComplete: true, //是否自动完成
        showConfirm: true, //是否显示确定按钮
        showKeyTips: false, //是否显示按键提示框
        align: 'center' //按键对齐方式，取值范围 [center: 居中对齐，经典键盘模式(默认), justify: 两端对齐，位数不够补充空白]
      },
      prevNumber: '', //缓存上次车牌
      layout: {},
      currentKey: {} //缓存当前按键
    };
  },
  created: function created() {
    this.init(this.args);
    engine.init(this.options);
  },

  computed: {
    dyKeyCount: function dyKeyCount() {
      return (this.dyKeyboard['row0'] || []).length;
    },
    dyKeyboard: function dyKeyboard() {
      if (this.options.currentIndex === 0 && (this.options.provinceName || '').length >= 2 && this.options.showShortCut) {
        var kb = this.updateShortcut();
        if (kb.shortcuts.length > 1) {
          try {
            return kb;
          } finally {
            this.submitprovince(kb);
          }
        } else {
          return this.updateKeyboard();
        }
      } else {
        return this.updateKeyboard();
      }
    }
  },
  methods: {
    /**
     * 初始化
     */
    init: function init(args) {
      this.options = Object.assign({}, this.options, args);
      !args.hasOwnProperty('showConfirm') && (this.options.showConfirm = true);
      //showShortCut 没有传递进来时，默认通过 provinceName 来判断
      !args.hasOwnProperty('showShortCut') && this.$set(this.options, 'showShortCut', this.options.provinceName !== undefined && this.options.provinceName.length > 0);
    },

    /**
     * 更新配置项
     */
    updateArgs: function updateArgs(args) {
      this.init(args);
    },

    /**
     * 更新键盘布局
     */
    updateKeyboard: function updateKeyboard() {
      try {
        this.layout = engine.update(this.options);
      } catch (err) {
        this.callMethod(this.callbacks.onmessage, err.message);
        return this.layout;
      }
      // 判断输入车牌是否已完成
      try {
        if (this.prevNumber !== this.layout.presetNumber) {
          var isCompleted = this.layout.numberLength === this.layout.numberLimitLength;
          try {
            this.prevNumber = this.layout.presetNumber;
            this.callMethod(this.callbacks.onchanged, this.layout.presetNumber, isCompleted);
          } finally {
            if (isCompleted && this.options.autoComplete === true) {
              this.callMethod(this.callbacks.oncompleted, this.layout.presetNumber, true);
            }
          }
        }
      } catch (err) {
        console.error('SingleKeyboard:::', err);
      }
      this.callbacks && this.callbacks.updateKeyboard && this.callbacks.updateKeyboard(this.layout);
      this.autocommitsinglekey(this.layout);
      return this.layout;
    },

    /**
     * 更新键盘快捷键
     */
    updateShortcut: function updateShortcut() {
      return {
        shortcuts: provinces.locationOf(this.options.provinceName).peripheryShortnames().map(function (name) {
          return engine.$newKey(name);
        }).slice(0, 6) // 只返回6个快捷键
      };
    },

    /**
     * 当键盘数据只有一个键位可选择时,自动提交点击事件:(武警车牌第二位J和使馆车最后一位)
     */
    autocommitsinglekey: function autocommitsinglekey(layout) {
      var _this = this;

      if (!this.currentKey.isFunKey) {
        var availableKeys = layout.keys.filter(function (k) {
          return k.enabled && !k.isFunKey;
        });
        if (availableKeys.length === 1) {
          setTimeout(function () {
            _this.onClickKey(availableKeys[0], layout.index);
          }, 32);
        }
      }
    },

    /**
     * 如果当前为空车牌号码，自动提交第一位快捷省份汉字
     */
    submitprovince: function submitprovince(layout) {
      var _this2 = this;

      // 注意：如果是用户点击删除按钮，退回到第一位。则不自动提交第一位快捷省份汉字。
      // 注意：如果用户外部重新设置了空的车牌号码，则需要自动提交
      if (this.options.presetNumber.length === 0 && !this.currentKey.isFunKey) {
        setTimeout(function () {
          if (_this2.options.currentIndex === 0) {
            // 注意检查当自动提交省份时，输入框选中位置是否在第一位上
            _this2.onClickKey(layout.shortcuts[0]);
          }
        }, 32);
      }
    },

    /**
     * 键位点击回调
     */
    onClickKey: function onClickKey(key, index) {
      if (!(index ? this.options.presetNumber.substr(index, 1) !== key.text : true)) return;
      key.FUN_DEL = engine.KEY_TYPES.FUN_DEL === key.keyCode; //删除
      key.FUN_OK = engine.KEY_TYPES.FUN_OK === key.keyCode; //确认
      this.currentKey = key;
      try {
        var autoSlice = this.callbacks.onkeypressed ? this.callbacks.onkeypressed(key) : true; //是否自动处理车牌录入，false：交由调用者在 onkeypressed 中处理车牌信息
        if (!autoSlice) {
          console.log('当前车牌录入模式为手动，如需自动处理车牌录入，请将 onkeypressed 返回 true');
        }
        if (key.FUN_DEL) {
          if (autoSlice) {
            this.options.presetNumber = this.options.presetNumber.slice(0, this.options.presetNumber.length - 1);
          }
          this.layout.numberLength < this.layout.numberLimitLength && this.options.currentIndex > 0 && this.options.currentIndex--;
          this.options.currentIndex === 0 && this.callMethod(this.callbacks.onchanged, this.options.presetNumber, false);
        } else if (!key.FUN_OK) {
          if (autoSlice) {
            if (this.layout.numberLength === this.layout.numberLimitLength) {
              this.options.presetNumber = this.options.presetNumber.slice(0, this.options.presetNumber.length - 1) + key.text;
            } else {
              this.options.presetNumber += key.text;
            }
          }
          this.options.currentIndex === 0 ? this.options.currentIndex++ : this.options.currentIndex < this.layout.numberLimitLength - 1 && this.options.currentIndex++;
        }
      } finally {
        if (key.FUN_OK) {
          this.callMethod(this.callbacks.oncompleted, this.options.presetNumber, false);
        }
      }
    },

    /**
     * 显示更多回调
     */
    onClickShowAll: function onClickShowAll() {
      this.options.showShortCut = false;
    },

    /**
     * 执行回调
     */
    callMethod: function callMethod() {
      if (arguments[0] && Object.prototype.toString.call(arguments[0]) === '[object Function]') {
        arguments[0].apply(this, Array.prototype.slice.call(arguments).slice(1));
      }
    }
  },
  components: {
    'keyboard-view': __WEBPACK_IMPORTED_MODULE_0__keyboard_view_vue__["a" /* default */]
  }
});

/***/ }),
/* 950 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__key_row_view__ = __webpack_require__(541);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__shortcut_view__ = __webpack_require__(543);
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//



/* unused harmony default export */ var _unused_webpack_default_export = ({
  props: {
    /**
     * 键盘组件的数据对象，用于传递键盘每行的数据
     */
    keyboard: {
      type: Object,
      default: function _default() {}
    },
    /**
     * 指定每行的键位数量
     */
    keycount: {
      type: Number,
      default: 0
    },
    /**
     * 是否显示确认按钮
     */
    showConfirm: {
      type: Boolean,
      default: false
    },
    /**
     * 是否显示按键提示框
     */
    showKeyTips: {
      type: Boolean
    }
  },
  data: function data() {
    return { tipText: '', tipPosX: '0px', tipPosY: '0px' };
  },

  computed: {
    /**
     * 行数
     */
    rc: function rc() {
      return (this.keyboard['row4'] || []).length === 0 ? 4 : 5;
    },

    /**
     * 快捷键
     */
    shortcuts: function shortcuts() {
      return this.keyboard['shortcuts'] || [];
    },

    /**
     * 是否存在快捷键
     */
    hasShortcut: function hasShortcut() {
      return this.shortcuts.length > 0;
    }
  },
  methods: {
    /**
     * 触发显示键位提示框
     * @param {Object} key 键位
     * @param {Event} evt
     */
    onKeyEvent: function onKeyEvent(key, evt) {
      var self = this;
      var _reset = function _reset() {
        self.tipText = '';
      };
      if (key.enabled && !key.isFunKey) {
        this.tipText = key.text;
        var dom = evt.target;
        // 60px 是tooltip的固定宽度
        // 62px 是tooltip的固定高度 + 间隔
        this.tipPosX = dom.offsetLeft - Math.abs(60 - dom.clientWidth) / 4 + 'px';
        this.tipPosY = dom.offsetTop - 62 + 'px';
        setTimeout(_reset, 250);
      } else {
        _reset();
      }
    },

    /**
     * 键位点击事件
     * @param {Object} key 键位
     * @param {Event} evt
     */
    onKeyClick: function onKeyClick(key, evt) {
      this.$emit('keyclick', key);
      this.showKeyTips && evt && this.onKeyEvent(key, evt);
    },

    /**
     * 显示更多事件
     */
    onShowMoreClick: function onShowMoreClick() {
      this.$emit('moreclick');
    }
  },
  components: {
    'row-view': __WEBPACK_IMPORTED_MODULE_0__key_row_view__["a" /* default */],
    'shortcut-view': __WEBPACK_IMPORTED_MODULE_1__shortcut_view__["a" /* default */]
  }
});

/***/ }),
/* 951 */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),
/* 952 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__tap__ = __webpack_require__(419);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__engine__ = __webpack_require__(420);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__engine___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_1__engine__);
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//



/* unused harmony default export */ var _unused_webpack_default_export = ({
  directives: __WEBPACK_IMPORTED_MODULE_0__tap__["a" /* default */],
  props: {
    /**
     * 行数
     */
    rowcount: {
      type: Number,
      default: 0
    },
    /**
     * 键位
     */
    keys: {
      type: Array,
      default: function _default() {
        return [];
      }
    },
    /**
     * 是否当前行存在Function按键（确定按钮）
     */
    isfunc: {
      type: Boolean,
      default: false
    },
    /**
     * 是否显示确认按钮
     */
    showConfirm: {
      type: Boolean,
      default: false
    },
    /**
     * 当前行键位数量
     */
    keycount: {
      type: Number,
      default: 0
    }
  },
  data: function data() {
    return {
      KeyTypes: __WEBPACK_IMPORTED_MODULE_1__engine__["KEY_TYPES"]
    };
  },
  created: function created() {
    var _this = this;

    this.initKeys();
    this.$nextTick(function () {
      _this.$refs.buttonKey.forEach(function (button) {
        return button.addEventListener('contextmenu', function (e) {
          e.preventDefault();
        });
      });
    });
  },

  watch: {
    keys: function keys(val) {
      this.initKeys();
    }
  },
  computed: {
    keyLength: function keyLength() {
      var length = this.keys.length + (this.isfunc ? 1 : 0);
      return length > this.keycount ? length : this.keycount;
    },
    IEVersion: function IEVersion() {
      var userAgent = navigator.userAgent; //取得浏览器的userAgent字符串
      var isIE = userAgent.indexOf('compatible') > -1 && userAgent.indexOf('MSIE') > -1; //判断是否IE<11浏览器
      var isEdge = userAgent.indexOf('Edge') > -1 && !isIE; //判断是否IE的Edge浏览器
      var isIE11 = userAgent.indexOf('Trident') > -1 && userAgent.indexOf('rv:11.0') > -1;
      if (isIE) {
        var reIE = new RegExp('MSIE (\\d+\\.\\d+);');
        reIE.test(userAgent);
        var fIEVersion = parseFloat(RegExp['$1']);
        if (fIEVersion === 7) {
          return 7;
        } else if (fIEVersion === 8) {
          return 8;
        } else if (fIEVersion === 9) {
          return 9;
        } else if (fIEVersion === 10) {
          return 10;
        }
        return 6; //IE版本<=7
      } else if (isEdge) {
        return 'edge'; //edge
      } else if (isIE11) {
        return 11; //IE11
      }
      return -1; //不是ie浏览器
    }
  },
  methods: {
    initKeys: function initKeys() {
      if (this.isfunc && !this.showConfirm) {
        var confirmKey = this.keys[this.keys.length - 1],
            deleteKey = this.keys[this.keys.length - 2];
        if (confirmKey && this.isFunOk(confirmKey) && deleteKey && deleteKey.keyCode === this.KeyTypes.FUN_DEL) {
          this.keys.pop();
          this.keys.pop();
          this.keys.push({ text: '' });
          this.keys.push(deleteKey);
        }
      }
    },

    /**
     * 兼容IE9
     */
    liStyle: function liStyle(index) {
      if (index === 0 && !this.isfunc) {
        if (this.IEVersion > 0 && this.IEVersion <= 9 && this.keys.length < this.keycount) {
          //IE9及以下浏览器
          return {
            marginLeft: 'calc((100% - .3125rem * ' + (this.keycount - 1) + ') /' + this.keycount + '*' + (this.keycount - this.keys.length) / 2 + ')'
          };
        }
      }
      return {};
    },

    /**
     * 是否是确定键
     */
    isFunOk: function isFunOk(key) {
      return key.keyCode === this.KeyTypes.FUN_OK;
    },

    /**
     * 按钮文本过滤器
     * @param {Object} key 键位
     */
    buttonTextFilter: function buttonTextFilter(key) {
      if (!key.text || '←' === key.text || key.keyCode === this.KeyTypes.FUN_DEL) {
        return '';
      }
      if (this.isFunOk(key) && !this.showConfirm) {
        return '';
      }
      return key.text;
    },

    /**
     * 键位点击事件
     * @param {Object} key 键位对象
     */
    onButtonClick: function onButtonClick(key, e) {
      if (key.enabled) {
        this.$emit('keyrowclick', key, e);
      }
    }
  }
});

/***/ }),
/* 953 */
/***/ (function(module, exports) {

(function(global, factory) {
  module.exports = factory();
})(this, function() {
  'use strict';

  // 定义一些处理键盘内部逻辑的框架类
  // Author: 陈哈哈 yoojiachen@gmail.com

  var Cached = {
    _mcached: {},

    reg: function(layout, category, keys) {
      if (keys !== undefined && keys.constructor === Array) {
        var cached = this._mcached;
        keys.forEach(function(key) {
          cached[category + ':' + key] = layout;
        });
      } else {
        var keyIdx = keys === undefined ? 0 : keys;
        this._mcached[category + ':' + keyIdx] = layout;
      }
    },

    load: function(category, key) {
      return this._mcached[category + ':' + (key === undefined ? 0 : key)];
    }
  };

  var Chain = {
    create: function(defVal) {
      var chain = {};
      var _handlers = new Array();
      var _index = 0;

      chain.next = function(args) {
        if (_index <= _handlers.length) {
          return _handlers[_index++](chain, args);
        } 
        return defVal;
        
      };

      chain.process = function(args) {
        var ret = chain.next(args);
        _index = 0;
        return ret;
      };

      chain.reg = function(h) {
        _handlers.push(h);
        return chain;
      };

      return chain;
    }
  };

  var Each = {
    create: function() {
      var _convertor = {};
      var _workers = new Array();

      _convertor.process = function(defVal, args) {
        var ret = defVal;
        _workers.forEach(function(worker) {
          ret = worker(ret, args);
        });
        return ret;
      };

      _convertor.reg = function(p) {
        _workers.push(p);
        return _convertor;
      };

      return _convertor;
    }
  };

  return {
    Chain: Chain,
    Cached: Cached,
    Each: Each
  };
});


/***/ }),
/* 954 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
/* harmony export (immutable) */ __webpack_exports__["keyOf"] = keyOf;
/* harmony export (immutable) */ __webpack_exports__["keyOfEnabled"] = keyOfEnabled;
/* harmony export (immutable) */ __webpack_exports__["keysOf"] = keysOf;
/* harmony export (immutable) */ __webpack_exports__["keyOfCode"] = keyOfCode;
/* harmony export (immutable) */ __webpack_exports__["contains"] = contains;
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "isProvince", function() { return isProvince; });
/* harmony export (immutable) */ __webpack_exports__["detectNumberTypeOf"] = detectNumberTypeOf;
var def = __webpack_require__(542);

// 纯辅助函数
// Author: 陈哈哈 yoojiachen@gmail.com

/**
 * 构建一个KeyEntity
 * @param {*text} 键位文本
 * @param {*keyCode} 按键码
 * @param {*enabled} 是否启用状态
 */
function keyOf(text, keyCode, enabled) {
  return {
    text: text, // 键位文字
    keyCode: keyCode === undefined ? def.KEY_TYPES.GENERAL : keyCode, // 键位功能类型代码，默认：普通键位
    enabled: enabled === undefined ? true : enabled, // 是否可用，默认：启用
    isFunKey: keyCode === undefined ? false : keyCode !== def.KEY_TYPES.GENERAL // 是否为功能键
  };
}

/**
 * 修改和创建一个新的KeyEntity，指定是否启用状态。
 * @param {*entity} 原KeyEntity
 * @param {*enabled} 是否启用状态
 */
function keyOfEnabled(entity, enabled) {
  return keyOf(
    entity.text,
    entity.keyCode,
    enabled // 修改
  );
}

/** 将字符串转换成KeyEntity */
function keysOf(str) {
  var output = new Array();
  for (var i = 0; i < str.length; i++) {
    output.push(keyOf(str[i]));
  }
  return output;
}

/** 修改和创建一个新的KeyEntity，指定功能键盘参数 */
function keyOfCode(entity, text, keyCode) {
  return keyOf(
    text,
    keyCode, // 修改
    entity.enabled
  );
}

function contains(src, item) {
  return src.indexOf(item) >= 0;
}

function isProvince(str) {
  return contains(def.S_CIVIL_PVS, str);
}

/** 探测车牌号码的模式 */
function detectNumberTypeOf(presetNumber) {
  if (presetNumber.length === 0) {
    return def.NUM_TYPES.AUTO_DETECT;
  }
  var first = presetNumber.charAt(0);
  if (contains(def.S_PLA2012_PVS, first)) {
    return def.NUM_TYPES.PLA2012;
  } else if (def.C_SHI2007 === first) {
    return def.NUM_TYPES.SHI2007;
  } else if (def.C_MIN === first) {
    return def.NUM_TYPES.AVIATION;
  } else if (contains(def.S_123, first)) {
    return def.NUM_TYPES.SHI2017;
  } else if (def.C_W === first) {
    if (presetNumber.length >= 3 && isProvince(presetNumber.charAt(2))) {
      return def.NUM_TYPES.WJ2012;
    }
    return def.NUM_TYPES.WJ2007;
  } else if (isProvince(first)) {
    if (presetNumber.length === 8) {
      // 新能源车牌：
      if (/\W[A-Z][0-9DF][0-9A-Z]\d{3}[0-9DF]/.test(presetNumber)) {
        return def.NUM_TYPES.NEW_ENERGY;
      }
      return def.NUM_TYPES.UNKNOWN;
    }
    return def.NUM_TYPES.CIVIL;
  }
  return def.NUM_TYPES.UNKNOWN;
}


/***/ }),
/* 955 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__tap__ = __webpack_require__(419);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__engine__ = __webpack_require__(420);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__engine___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_1__engine__);
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//



/* harmony default export */ __webpack_exports__["a"] = ({
  directives: __WEBPACK_IMPORTED_MODULE_0__tap__["a" /* default */],
  props: {
    /**
     * 行数
     */
    rowcount: {
      type: Number,
      default: 0
    },
    /**
     * 键位
     */
    keys: {
      type: Array,
      default: function _default() {
        return [];
      }
    },
    /**
     * 是否当前行存在Function按键（确定按钮）
     */
    isfunc: {
      type: Boolean,
      default: false
    },
    /**
     * 是否显示确认按钮
     */
    showConfirm: {
      type: Boolean,
      default: false
    },
    /**
     * 当前行键位数量
     */
    keycount: {
      type: Number,
      default: 0
    }
  },
  data: function data() {
    return {
      KeyTypes: __WEBPACK_IMPORTED_MODULE_1__engine__["KEY_TYPES"]
    };
  },
  created: function created() {
    var _this = this;

    this.initKeys();
    this.$nextTick(function () {
      _this.$refs.buttonKey.forEach(function (button) {
        return button.addEventListener('contextmenu', function (e) {
          e.preventDefault();
        });
      });
    });
  },

  watch: {
    keys: function keys(val) {
      this.initKeys();
    }
  },
  computed: {
    keyLength: function keyLength() {
      var length = this.keys.length + (this.isfunc ? 1 : 0);
      return length > this.keycount ? length : this.keycount;
    },
    IEVersion: function IEVersion() {
      var userAgent = navigator.userAgent; //取得浏览器的userAgent字符串
      var isIE = userAgent.indexOf('compatible') > -1 && userAgent.indexOf('MSIE') > -1; //判断是否IE<11浏览器
      var isEdge = userAgent.indexOf('Edge') > -1 && !isIE; //判断是否IE的Edge浏览器
      var isIE11 = userAgent.indexOf('Trident') > -1 && userAgent.indexOf('rv:11.0') > -1;
      if (isIE) {
        var reIE = new RegExp('MSIE (\\d+\\.\\d+);');
        reIE.test(userAgent);
        var fIEVersion = parseFloat(RegExp['$1']);
        if (fIEVersion === 7) {
          return 7;
        } else if (fIEVersion === 8) {
          return 8;
        } else if (fIEVersion === 9) {
          return 9;
        } else if (fIEVersion === 10) {
          return 10;
        }
        return 6; //IE版本<=7
      } else if (isEdge) {
        return 'edge'; //edge
      } else if (isIE11) {
        return 11; //IE11
      }
      return -1; //不是ie浏览器
    }
  },
  methods: {
    initKeys: function initKeys() {
      if (this.isfunc && !this.showConfirm) {
        var confirmKey = this.keys[this.keys.length - 1],
            deleteKey = this.keys[this.keys.length - 2];
        if (confirmKey && this.isFunOk(confirmKey) && deleteKey && deleteKey.keyCode === this.KeyTypes.FUN_DEL) {
          this.keys.pop();
          this.keys.pop();
          this.keys.push({ text: '' });
          this.keys.push(deleteKey);
        }
      }
    },

    /**
     * 兼容IE9
     */
    liStyle: function liStyle(index) {
      if (index === 0 && !this.isfunc) {
        if (this.IEVersion > 0 && this.IEVersion <= 9 && this.keys.length < this.keycount) {
          //IE9及以下浏览器
          return {
            marginLeft: 'calc((100% - .3125rem * ' + (this.keycount - 1) + ') /' + this.keycount + '*' + (this.keycount - this.keys.length) / 2 + ')'
          };
        }
      }
      return {};
    },

    /**
     * 是否是确定键
     */
    isFunOk: function isFunOk(key) {
      return key.keyCode === this.KeyTypes.FUN_OK;
    },

    /**
     * 按钮文本过滤器
     * @param {Object} key 键位
     */
    buttonTextFilter: function buttonTextFilter(key) {
      if (!key.text || '←' === key.text || key.keyCode === this.KeyTypes.FUN_DEL) {
        return '';
      }
      if (this.isFunOk(key) && !this.showConfirm) {
        return '';
      }
      return key.text;
    },

    /**
     * 键位点击事件
     * @param {Object} key 键位对象
     */
    onButtonClick: function onButtonClick(key, e) {
      if (key.enabled) {
        this.$emit('keyrowclick', key, e);
      }
    }
  }
});

/***/ }),
/* 956 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('ul',{staticClass:"vehicle-keyrow",class:[ {'funcrow': (_vm.isfunc === true)}, ('rowsof-' + _vm.rowcount) ]},_vm._l((_vm.keys),function(key,i){return _c('li',{key:i,class:['keysof-' + _vm.keyLength,{'ie9':_vm.IEVersion > 0 && _vm.IEVersion <= 9}],style:(_vm.liStyle(i))},[_c('button',{directives:[{name:"tap",rawName:"v-tap",value:({method: _vm.onButtonClick, params: { key: key } }),expression:"{method: onButtonClick, params: { key } }"}],ref:"buttonKey",refInFor:true,staticClass:"key r-border txt-key",class:[ ('keycodeof-' + key.keyCode), {'disabled': (!key.enabled)}],attrs:{"tag":"button","disabled":(!key.enabled)}},[_vm._v("\n      "+_vm._s(_vm.buttonTextFilter(key))+"\n    ")])])}))}
var staticRenderFns = []
var esExports = { render: render, staticRenderFns: staticRenderFns }
/* harmony default export */ __webpack_exports__["a"] = (esExports);

/***/ }),
/* 957 */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),
/* 958 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__tap__ = __webpack_require__(419);
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//


/* unused harmony default export */ var _unused_webpack_default_export = ({
  directives: __WEBPACK_IMPORTED_MODULE_0__tap__["a" /* default */],
  props: {
    /**
     * 快捷显示
     */
    shortcuts: {
      type: Array,
      defalut: function defalut() {
        return [];
      }
    }
  },
  methods: {
    /**
     * 键位点击事件
     * @param {Object} key 键位对象
     */
    onButtonClick: function onButtonClick(key) {
      if (key.enabled) {
        this.$emit('keyrowclick', key);
      }
    },

    /**
     * 显示全部点击事件
     */
    onShowMoreClick: function onShowMoreClick() {
      this.$emit('showmoreclick');
    }
  }
});

/***/ }),
/* 959 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__tap__ = __webpack_require__(419);
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//


/* harmony default export */ __webpack_exports__["a"] = ({
  directives: __WEBPACK_IMPORTED_MODULE_0__tap__["a" /* default */],
  props: {
    /**
     * 快捷显示
     */
    shortcuts: {
      type: Array,
      defalut: function defalut() {
        return [];
      }
    }
  },
  methods: {
    /**
     * 键位点击事件
     * @param {Object} key 键位对象
     */
    onButtonClick: function onButtonClick(key) {
      if (key.enabled) {
        this.$emit('keyrowclick', key);
      }
    },

    /**
     * 显示全部点击事件
     */
    onShowMoreClick: function onShowMoreClick() {
      this.$emit('showmoreclick');
    }
  }
});

/***/ }),
/* 960 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{staticStyle:{"height":"100%"}},[_c('ul',{staticClass:"shortcut-row"},_vm._l((_vm.shortcuts),function(key,i){return _c('li',{key:i},[_c('button',{directives:[{name:"tap",rawName:"v-tap",value:({ method: _vm.onButtonClick, params: { key: key } }),expression:"{ method: onButtonClick, params: { key } }"}],staticClass:"key r-border shortcut",class:('keycodeof-' + key.keyCode),attrs:{"tag":"button","disabled":(!key.enabled)}},[_vm._v(_vm._s(key.text))])])})),_vm._v(" "),_c('div',{directives:[{name:"tap",rawName:"v-tap",value:({ method: _vm.onShowMoreClick }),expression:"{ method: onShowMoreClick }"}],staticClass:"showall needclicks"},[_vm._v("显示全部")])])}
var staticRenderFns = []
var esExports = { render: render, staticRenderFns: staticRenderFns }
/* harmony default export */ __webpack_exports__["a"] = (esExports);

/***/ }),
/* 961 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__key_row_view__ = __webpack_require__(541);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__shortcut_view__ = __webpack_require__(543);
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//



/* harmony default export */ __webpack_exports__["a"] = ({
  props: {
    /**
     * 键盘组件的数据对象，用于传递键盘每行的数据
     */
    keyboard: {
      type: Object,
      default: function _default() {}
    },
    /**
     * 指定每行的键位数量
     */
    keycount: {
      type: Number,
      default: 0
    },
    /**
     * 是否显示确认按钮
     */
    showConfirm: {
      type: Boolean,
      default: false
    },
    /**
     * 是否显示按键提示框
     */
    showKeyTips: {
      type: Boolean
    }
  },
  data: function data() {
    return { tipText: '', tipPosX: '0px', tipPosY: '0px' };
  },

  computed: {
    /**
     * 行数
     */
    rc: function rc() {
      return (this.keyboard['row4'] || []).length === 0 ? 4 : 5;
    },

    /**
     * 快捷键
     */
    shortcuts: function shortcuts() {
      return this.keyboard['shortcuts'] || [];
    },

    /**
     * 是否存在快捷键
     */
    hasShortcut: function hasShortcut() {
      return this.shortcuts.length > 0;
    }
  },
  methods: {
    /**
     * 触发显示键位提示框
     * @param {Object} key 键位
     * @param {Event} evt
     */
    onKeyEvent: function onKeyEvent(key, evt) {
      var self = this;
      var _reset = function _reset() {
        self.tipText = '';
      };
      if (key.enabled && !key.isFunKey) {
        this.tipText = key.text;
        var dom = evt.target;
        // 60px 是tooltip的固定宽度
        // 62px 是tooltip的固定高度 + 间隔
        this.tipPosX = dom.offsetLeft - Math.abs(60 - dom.clientWidth) / 4 + 'px';
        this.tipPosY = dom.offsetTop - 62 + 'px';
        setTimeout(_reset, 250);
      } else {
        _reset();
      }
    },

    /**
     * 键位点击事件
     * @param {Object} key 键位
     * @param {Event} evt
     */
    onKeyClick: function onKeyClick(key, evt) {
      this.$emit('keyclick', key);
      this.showKeyTips && evt && this.onKeyEvent(key, evt);
    },

    /**
     * 显示更多事件
     */
    onShowMoreClick: function onShowMoreClick() {
      this.$emit('moreclick');
    }
  },
  components: {
    'row-view': __WEBPACK_IMPORTED_MODULE_0__key_row_view__["a" /* default */],
    'shortcut-view': __WEBPACK_IMPORTED_MODULE_1__shortcut_view__["a" /* default */]
  }
});

/***/ }),
/* 962 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return (_vm.hasShortcut)?_c('div',{staticClass:"vehicle-keyboard"},[_c('shortcut-view',{attrs:{"shortcuts":_vm.shortcuts},on:{"keyrowclick":_vm.onKeyClick,"showmoreclick":_vm.onShowMoreClick}})],1):_c('div',{staticClass:"vehicle-keyboard"},[_vm._l(([0, 1, 2, 3, 4]),function(index){return ((_vm.keyboard[("row" + index)] || []).length > 0)?_c('row-view',{key:index,attrs:{"keys":_vm.keyboard[("row" + index)],"keycount":_vm.keycount,"rowcount":_vm.rc,"isfunc":(_vm.keyboard[("row" + (index + 1))] || []).length === 0,"show-confirm":_vm.showConfirm},on:{"keyrowclick":_vm.onKeyClick}}):_vm._e()}),_vm._v(" "),(_vm.tipText != '')?_c('div',{staticClass:"r-border keytip",style:({'left': _vm.tipPosX, 'top': _vm.tipPosY})},[_vm._v(_vm._s(_vm.tipText)+" ")]):_vm._e()],2)}
var staticRenderFns = []
var esExports = { render: render, staticRenderFns: staticRenderFns }
/* harmony default export */ __webpack_exports__["a"] = (esExports);

/***/ }),
/* 963 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__keyboard_view_vue__ = __webpack_require__(540);
//
//
//
//
//
//
//
//
//
//


var engine = __webpack_require__(420);
var provinces = __webpack_require__(544);
/* harmony default export */ __webpack_exports__["a"] = ({
  name: 'vechicle-single-keyboard',
  props: {
    /**
     * 参数配置项
     * @param {String} presetNumber 预设车牌号
     * @param {Number} keyboardType 键盘类型[0:全键盘，1：民用, 2：民用+武警]
     * @param {Number} currentIndex 当前输入位置
     * @param {String} provinceName 默认省份
     * @param {Number} numberType 用户预设车牌输入类型 0：自动探测车牌类型，5:新能源车牌(engine.NUM_TYPES)
     * @param {Boolean} autoComplete 是否自动完成
     * @param {Boolean} showConfirm 是否显示确定按钮
     * @param {Boolean} showKeyTips 是否显示按键提示框(点击按键弹出当前按键内容提示，类似输入法)
     * @param {String} align //按键对齐方式，取值范围 [center: 居中对齐，经典键盘模式(默认), justify: 两端对齐，位数不够补充空白]
     */
    args: {
      type: Object,
      default: function _default() {}
    },
    /**
     * 回掉接口
     */
    callbacks: {
      type: Object,
      default: function _default() {
        return {
          /**按键监听*/
          onkeypressed: function onkeypressed(key) {},

          /**车牌改变监听(车牌号，是否已完成)*/
          onchanged: function onchanged(presetNumber, isCompleted) {},

          /**车牌完成监听(车牌号，是否自动完成)*/
          oncompleted: function oncompleted(presetNumber, isAutoCompleted) {},

          /**错误回调*/
          onmessage: function onmessage(message) {},

          /**键盘更新回调*/
          updateKeyboard: function updateKeyboard() {}
        };
      }
    }
  },
  data: function data() {
    return {
      options: {
        presetNumber: '', //预设车牌号码
        keyboardType: 1, //键盘类型[0:全键盘，1：民用, 2：民用+武警]
        currentIndex: 0, //当前输入位置
        numberType: 0, //用户预设车牌输入类型 0：自动探测车牌类型，5:新能源车牌(engine.NUM_TYPES)
        provinceName: '', //省份
        showShortCut: false, //是否显示快捷省份
        autoComplete: true, //是否自动完成
        showConfirm: true, //是否显示确定按钮
        showKeyTips: false, //是否显示按键提示框
        align: 'center' //按键对齐方式，取值范围 [center: 居中对齐，经典键盘模式(默认), justify: 两端对齐，位数不够补充空白]
      },
      prevNumber: '', //缓存上次车牌
      layout: {},
      currentKey: {} //缓存当前按键
    };
  },
  created: function created() {
    this.init(this.args);
    engine.init(this.options);
  },

  computed: {
    dyKeyCount: function dyKeyCount() {
      return (this.dyKeyboard['row0'] || []).length;
    },
    dyKeyboard: function dyKeyboard() {
      if (this.options.currentIndex === 0 && (this.options.provinceName || '').length >= 2 && this.options.showShortCut) {
        var kb = this.updateShortcut();
        if (kb.shortcuts.length > 1) {
          try {
            return kb;
          } finally {
            this.submitprovince(kb);
          }
        } else {
          return this.updateKeyboard();
        }
      } else {
        return this.updateKeyboard();
      }
    }
  },
  methods: {
    /**
     * 初始化
     */
    init: function init(args) {
      this.options = Object.assign({}, this.options, args);
      !args.hasOwnProperty('showConfirm') && (this.options.showConfirm = true);
      //showShortCut 没有传递进来时，默认通过 provinceName 来判断
      !args.hasOwnProperty('showShortCut') && this.$set(this.options, 'showShortCut', this.options.provinceName !== undefined && this.options.provinceName.length > 0);
    },

    /**
     * 更新配置项
     */
    updateArgs: function updateArgs(args) {
      this.init(args);
    },

    /**
     * 更新键盘布局
     */
    updateKeyboard: function updateKeyboard() {
      try {
        this.layout = engine.update(this.options);
      } catch (err) {
        this.callMethod(this.callbacks.onmessage, err.message);
        return this.layout;
      }
      // 判断输入车牌是否已完成
      try {
        if (this.prevNumber !== this.layout.presetNumber) {
          var isCompleted = this.layout.numberLength === this.layout.numberLimitLength;
          try {
            this.prevNumber = this.layout.presetNumber;
            this.callMethod(this.callbacks.onchanged, this.layout.presetNumber, isCompleted);
          } finally {
            if (isCompleted && this.options.autoComplete === true) {
              this.callMethod(this.callbacks.oncompleted, this.layout.presetNumber, true);
            }
          }
        }
      } catch (err) {
        console.error('SingleKeyboard:::', err);
      }
      this.callbacks && this.callbacks.updateKeyboard && this.callbacks.updateKeyboard(this.layout);
      this.autocommitsinglekey(this.layout);
      return this.layout;
    },

    /**
     * 更新键盘快捷键
     */
    updateShortcut: function updateShortcut() {
      return {
        shortcuts: provinces.locationOf(this.options.provinceName).peripheryShortnames().map(function (name) {
          return engine.$newKey(name);
        }).slice(0, 6) // 只返回6个快捷键
      };
    },

    /**
     * 当键盘数据只有一个键位可选择时,自动提交点击事件:(武警车牌第二位J和使馆车最后一位)
     */
    autocommitsinglekey: function autocommitsinglekey(layout) {
      var _this = this;

      if (!this.currentKey.isFunKey) {
        var availableKeys = layout.keys.filter(function (k) {
          return k.enabled && !k.isFunKey;
        });
        if (availableKeys.length === 1) {
          setTimeout(function () {
            _this.onClickKey(availableKeys[0], layout.index);
          }, 32);
        }
      }
    },

    /**
     * 如果当前为空车牌号码，自动提交第一位快捷省份汉字
     */
    submitprovince: function submitprovince(layout) {
      var _this2 = this;

      // 注意：如果是用户点击删除按钮，退回到第一位。则不自动提交第一位快捷省份汉字。
      // 注意：如果用户外部重新设置了空的车牌号码，则需要自动提交
      if (this.options.presetNumber.length === 0 && !this.currentKey.isFunKey) {
        setTimeout(function () {
          if (_this2.options.currentIndex === 0) {
            // 注意检查当自动提交省份时，输入框选中位置是否在第一位上
            _this2.onClickKey(layout.shortcuts[0]);
          }
        }, 32);
      }
    },

    /**
     * 键位点击回调
     */
    onClickKey: function onClickKey(key, index) {
      if (!(index ? this.options.presetNumber.substr(index, 1) !== key.text : true)) return;
      key.FUN_DEL = engine.KEY_TYPES.FUN_DEL === key.keyCode; //删除
      key.FUN_OK = engine.KEY_TYPES.FUN_OK === key.keyCode; //确认
      this.currentKey = key;
      try {
        var autoSlice = this.callbacks.onkeypressed ? this.callbacks.onkeypressed(key) : true; //是否自动处理车牌录入，false：交由调用者在 onkeypressed 中处理车牌信息
        if (!autoSlice) {
          console.log('当前车牌录入模式为手动，如需自动处理车牌录入，请将 onkeypressed 返回 true');
        }
        if (key.FUN_DEL) {
          if (autoSlice) {
            this.options.presetNumber = this.options.presetNumber.slice(0, this.options.presetNumber.length - 1);
          }
          this.layout.numberLength < this.layout.numberLimitLength && this.options.currentIndex > 0 && this.options.currentIndex--;
          this.options.currentIndex === 0 && this.callMethod(this.callbacks.onchanged, this.options.presetNumber, false);
        } else if (!key.FUN_OK) {
          if (autoSlice) {
            if (this.layout.numberLength === this.layout.numberLimitLength) {
              this.options.presetNumber = this.options.presetNumber.slice(0, this.options.presetNumber.length - 1) + key.text;
            } else {
              this.options.presetNumber += key.text;
            }
          }
          this.options.currentIndex === 0 ? this.options.currentIndex++ : this.options.currentIndex < this.layout.numberLimitLength - 1 && this.options.currentIndex++;
        }
      } finally {
        if (key.FUN_OK) {
          this.callMethod(this.callbacks.oncompleted, this.options.presetNumber, false);
        }
      }
    },

    /**
     * 显示更多回调
     */
    onClickShowAll: function onClickShowAll() {
      this.options.showShortCut = false;
    },

    /**
     * 执行回调
     */
    callMethod: function callMethod() {
      if (arguments[0] && Object.prototype.toString.call(arguments[0]) === '[object Function]') {
        arguments[0].apply(this, Array.prototype.slice.call(arguments).slice(1));
      }
    }
  },
  components: {
    'keyboard-view': __WEBPACK_IMPORTED_MODULE_0__keyboard_view_vue__["a" /* default */]
  }
});

/***/ }),
/* 964 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{staticClass:"single-keyboard-box"},[_c('keyboard-view',{attrs:{"keyboard":_vm.dyKeyboard,"keycount":_vm.dyKeyCount,"show-confirm":_vm.options.showConfirm,"show-key-tips":_vm.options.showKeyTips},on:{"keyclick":_vm.onClickKey,"moreclick":_vm.onClickShowAll}})],1)}
var staticRenderFns = []
var esExports = { render: render, staticRenderFns: staticRenderFns }
/* harmony default export */ __webpack_exports__["a"] = (esExports);

/***/ }),
/* 965 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_vux_loader_src_script_loader_js_vux_loader_src_script_loader_js_vue_loader_lib_selector_type_script_index_0_mixed_keyboard_vue__ = __webpack_require__(967);
/* unused harmony namespace reexport */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__babel_loader_vux_loader_src_script_loader_js_vue_loader_lib_selector_type_script_index_0_mixed_keyboard_vue__ = __webpack_require__(968);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__vue_loader_lib_template_compiler_index_id_data_v_5ed55180_hasScoped_false_buble_transforms_vux_loader_src_before_template_compiler_loader_js_vux_loader_src_template_loader_js_vue_loader_lib_selector_type_template_index_0_mixed_keyboard_vue__ = __webpack_require__(969);
function injectStyle (ssrContext) {
  __webpack_require__(966)
}
var normalizeComponent = __webpack_require__(0)
/* script */


/* template */

/* template functional */
var __vue_template_functional__ = false
/* styles */
var __vue_styles__ = injectStyle
/* scopeId */
var __vue_scopeId__ = null
/* moduleIdentifier (server only) */
var __vue_module_identifier__ = null
var Component = normalizeComponent(
  __WEBPACK_IMPORTED_MODULE_1__babel_loader_vux_loader_src_script_loader_js_vue_loader_lib_selector_type_script_index_0_mixed_keyboard_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_2__vue_loader_lib_template_compiler_index_id_data_v_5ed55180_hasScoped_false_buble_transforms_vux_loader_src_before_template_compiler_loader_js_vux_loader_src_template_loader_js_vue_loader_lib_selector_type_template_index_0_mixed_keyboard_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)

/* harmony default export */ __webpack_exports__["a"] = (Component.exports);


/***/ }),
/* 966 */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),
/* 967 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__number_view__ = __webpack_require__(498);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__single_keyboard__ = __webpack_require__(499);
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//



var engine = __webpack_require__(420);
/* unused harmony default export */ var _unused_webpack_default_export = ({
  name: 'mixed-keyboard',
  props: {
    /**
     * 参数配置项
     * @param {String} presetNumber 预设车牌号
     * @param {Number} keyboardType 键盘类型[0:全键盘，1：民用, 2：民用+武警]
     * @param {String} provinceName 默认省份
     * @param {Boolean} forceChangeMode 是否强制切换键盘类型（忽略当前录入车牌有效性）
     * @param {Boolean} autoComplete 是否自动完成
     * @param {Boolean} showConfirm 是否显示确定按钮
     * @param {Boolean} showKeyTips 是否显示按键提示框(点击按键弹出当前按键内容提示，类似输入法)
     * @param {String} align //按键对齐方式，取值范围 [center: 居中对齐，经典键盘模式(默认), justify: 两端对齐，位数不够补充空白]
     * @param {String} position 键盘位置，取值范围 [static: 默认, bottom: 底部]
     */
    args: {
      type: Object,
      default: function _default() {}
    },
    /**
     * 回调函数
     */
    callbacks: {
      type: Object,
      default: function _default() {
        return {
          /**车牌改变监听(车牌号，是否已完成)*/
          onchanged: function onchanged(presetNumber, isCompleted) {},

          /**车牌完成监听(车牌号，是否自动完成)*/
          oncompleted: function oncompleted(presetNumber, isAutoCompleted) {},

          /**错误回调*/
          onmessage: function onmessage(message) {}
        };
      }
    }
  },
  data: function data() {
    var _this = this;

    return {
      options: {
        presetNumber: '', //预设车牌号码
        keyboardType: 1, //键盘类型[0:全键盘，1：民用, 2：民用+武警]
        provinceName: '', //省份
        currentIndex: 0, // 当前用户输入框已选中的序号
        showShortCut: true, // 需要显示省份简称
        forceChangeMode: false, //是否强制切换键盘类型
        numberType: engine.NUM_TYPES.AUTO_DETECT, // 车用户设定的车牌号码类型 0：自动探测车牌类型，5:新能源车牌
        autoComplete: true, //是否自动完成
        showConfirm: true, //是否显示确定按钮
        showKeyTips: false, //是否显示按键提示框
        align: 'center', //按键对齐方式，取值范围 [center: 居中对齐，经典键盘模式(默认), justify: 两端对齐，位数不够补充空白]
        position: ''
      },
      numberArray: ['', '', '', '', '', '', ''], // 用户输入的车牌数据
      userChanged: false, //用户是否外部修改了车牌号码
      singleCallbacks: {
        //// 回调函数
        onchanged: function onchanged(presetNumber, isCompleted) {
          _this.callMethod(_this.callbacks.onchanged, presetNumber, isCompleted);
        },
        oncompleted: function oncompleted(presetNumber, isAutoCompleted) {
          _this.callMethod(_this.callbacks.oncompleted, presetNumber, isAutoCompleted);
        },
        onkeypressed: function onkeypressed(key) {
          if (key.isFunKey) {
            _this.onFuncKeyClick(key);
          } else {
            _this.onTextKeyClick(key.text);
          }
          _this.callMethod(_this.callbacks.onkeypressed, key);
          return true;
        },
        onmessage: function onmessage(message) {
          _this.callMethod(_this.callbacks.onmessage, message);
        },
        updateKeyboard: function updateKeyboard(keyboard) {
          // 将识别结果的车牌模式同步到用户选择模式上
          if (keyboard.numberType === engine.NUM_TYPES.NEW_ENERGY) {
            _this.options.numberType = engine.NUM_TYPES.NEW_ENERGY;
          } else {
            _this.options.numberType = engine.NUM_TYPES.AUTO_DETECT;
          }
          _this.syncInputLength(keyboard.numberType, _this.options.numberType === engine.NUM_TYPES.NEW_ENERGY /*force to set NewEnergy mode*/
          );
        }
      }
    };
  },
  created: function created() {
    this.init();
  },

  watch: {
    options: {
      handler: function handler(val, oldVal) {
        this.$refs.singleKeyboard.updateArgs(val);
      },

      deep: true
    },
    detectNumberType: function detectNumberType(val) {
      this.options.numberType = val;
    }
  },
  computed: {
    /**
     * 当用户选择的车牌类型为非AUTO_DETECT类型时，使用用户强制设置类型：目前用户选择的类型有两个值：AUTO_DETECT / NEW_ENERGY
     */
    getNumberType: function getNumberType() {
      if (this.options.numberType === engine.NUM_TYPES.NEW_ENERGY) {
        return engine.NUM_TYPES.NEW_ENERGY;
      }
      return engine.NUM_TYPES.AUTO_DETECT;
    },
    dyDisplayMode: function dyDisplayMode() {
      // 用于显示的车牌类型
      if (this.options.numberType === engine.NUM_TYPES.NEW_ENERGY) {
        return engine.NUM_TYPES.NEW_ENERGY;
      }
      return this.detectNumberType === engine.NUM_TYPES.NEW_ENERGY ? engine.NUM_TYPES.NEW_ENERGY : engine.NUM_TYPES.AUTO_DETECT;
    },

    /**
     * 预测的车牌类型
     */
    detectNumberType: function detectNumberType() {
      return engine.detectNumberType(this.options.presetNumber, this.options.numberType);
    },
    keyboardStyle: function keyboardStyle() {
      return this.$slots && this.$slots.default > 0 ? {} : {
        'margin-top': '.78125rem'
      };
    },
    isCompleted: function isCompleted() {
      return this.numberArray.every(function (num) {
        return num && num !== '' && num !== null && num !== undefined;
      });
    }
  },
  methods: {
    /**
     * 初始化
     */
    init: function init() {
      this.options = Object.assign({}, this.options, this.args);
      this.$set(this.options, 'numberType', this.getNumberType);
      this.numberArray = this.rebuildNumberArray(this.options.presetNumber, this.numberArray.length /*要保证与原生长度一致*/
      );
      // 在用户更新车牌后，选中位置设置为当前车牌可输入的最后一位
      this.options.currentIndex = Math.max(0, Math.min(this.numberArray.length - 1, this.options.presetNumber.length));
      this.userChanged = true;
      this.options.showShortCut = true;
    },

    /**
     * 选中下一个输入序号的输入框
     */
    selectNextIndex: function selectNextIndex() {
      var next = this.options.currentIndex + 1;
      if (next <= this.numberArray.length - 1 /*限定在最大长度*/) {
          this.options.currentIndex = next;
        }
    },

    /**
     * 设置当前选择号码的位置
     */
    setNumberTxtAt: function setNumberTxtAt(index, text) {
      this.$set(this.numberArray, index, text);
      this.resetUserChanged();
    },

    /**
     * 切换新能源车牌（8位）
     */
    setLengthTo8: function setLengthTo8() {
      // 当前长度为7位长度时才允许切换至8位长度
      if (this.numberArray.length === 7) {
        // 扩展第8位：当前选中第7位，并且第7位已输入有效字符时，自动跳转到第8位
        if (6 === this.options.currentIndex && this.options.presetNumber.length === 7) {
          this.options.currentIndex = 7;
        }
        this.numberArray.push('');
        this.resetUserChanged();
      }
    },

    /**
     * 切换普通车牌（7位）
     */
    setLengthTo7: function setLengthTo7() {
      if (this.numberArray.length === 8) {
        if (7 === this.options.currentIndex) {
          // 处理最后一位的选中状态
          this.options.currentIndex = 6;
        }
        this.numberArray.pop();
        this.resetUserChanged();
      }
    },

    /**
     * 重置外部用户修改车牌标记位
     */
    resetUserChanged: function resetUserChanged() {
      this.options.presetNumber = this.numberArray.join('');
      this.userChanged = false;
    },

    /**
     * 切换用户强制车牌类型
     */
    onUserSetMode: function onUserSetMode() {
      // 如果当前车牌为武警车牌，不可切换：
      if (this.detectNumberType === engine.NUM_TYPES.WJ2007 || this.detectNumberType === engine.NUM_TYPES.WJ2012) {
        this.$refs.numberView.resetMode();
        this.callMethod(this.callbacks.onmessage, '武警车牌，请清空再切换');
        return;
      }
      if (this.options.numberType === engine.NUM_TYPES.NEW_ENERGY) {
        this.options.numberType = engine.NUM_TYPES.AUTO_DETECT;
      } else {
        // 已输入普通车牌如果不符合新能源车牌方式，不能切换为新能源车牌：
        var presetNumber = this.options.presetNumber;
        if (presetNumber.length > 2) {
          // 只输入前两个车牌号码，不参与校验
          var size = 8 - presetNumber.length;
          for (var i = 0; i < size; i++) {
            presetNumber += '0';
          } // 使用正则严格校验补全的车牌号码
          if (this.options.forceChangeMode === true || this.isEnergyNumber(presetNumber)) {
            this.options.numberType = engine.NUM_TYPES.NEW_ENERGY;
          } else {
            this.$refs.numberView.resetMode();
            this.callMethod(this.callbacks.onmessage, '非新能源车牌，请清空再切换');
            return;
          }
        } else {
          this.options.numberType = engine.NUM_TYPES.NEW_ENERGY;
        }
      }
      // 如果用户切换为新能源类型，则需要修改输入长度为8位：
      if (this.options.numberType === engine.NUM_TYPES.NEW_ENERGY) {
        this.setLengthTo8();
      } else {
        this.setLengthTo7();
      }
    },

    /**
     * 选中输入框
     */
    onSelectedInput: function onSelectedInput(index, shouldShowKeyboard) {
      var length = this.options.presetNumber.length;
      if (length > 0 && index <= length) {
        this.options.currentIndex = index;
      }
      if (true === shouldShowKeyboard) {
        /*强制显示键盘*/
        this.options.showShortCut = false;
      } else {
        this.options.showShortCut = this.options.currentIndex === 0;
      }
    },

    /**
     * 点击字符按键盘
     */
    onTextKeyClick: function onTextKeyClick(text, forceUpdate) {
      if (true === forceUpdate || text !== this.numberArray[this.options.currentIndex]) {
        this.setNumberTxtAt(this.options.currentIndex, text);
      }
      this.selectNextIndex(); // 选中下一个输入框
    },

    /**
     * 点击功能键
     */
    onFuncKeyClick: function onFuncKeyClick(key) {
      if (key.keyCode === engine.KEY_TYPES.FUN_DEL) {
        // 删除车辆号码的最后一位
        var maxIndex = this.numberArray.length - 1;
        var deleteIndex = Math.max(0, maxIndex);
        for (var i = maxIndex; i >= 0; i--) {
          if (this.numberArray[i].length !== 0) {
            deleteIndex = i;
            break;
          }
        }
        this.setNumberTxtAt(deleteIndex, '');
        // 更新删除时的选中状态
        this.options.currentIndex = deleteIndex;
      }
    },

    /**
     * 将车牌号码，生成一个车牌字符数组
     */
    rebuildNumberArray: function rebuildNumberArray(updateNumber, originLength) {
      var output = ['', '', '', '', '', '', '']; // 普通车牌长度为7位，最大长度为8位
      if (originLength > 7) {
        output.push('');
      }
      if (updateNumber !== undefined && updateNumber.length !== 0) {
        var size = Math.min(8, updateNumber.length);
        for (var i = 0; i < size; i++) {
          output[i] = updateNumber.charAt(i);
        }
      }
      return output;
    },

    /**
     * 同步输入长度
     */
    syncInputLength: function syncInputLength(mode, forceNewEnergyMode) {
      // 键盘引擎根据输入参数，会自动推测出当前车牌的类型。
      // 如果当前用户没有强制设置，更新键盘的输入框长度以适当当前的车牌类型,（指地方武警车牌，长度为8位）
      if (forceNewEnergyMode) {
        // 强制新能源类型，应当设置为：8位
        this.setLengthTo8();
      } else {
        if (engine.NUM_TYPES.WJ2012 === mode || engine.NUM_TYPES.NEW_ENERGY === mode) {
          // 地方武警，应当设置为：8位
          this.setLengthTo8();
        } else {
          // 其它车牌，应当设置为：7位
          this.setLengthTo7();
        }
      }
    },

    /**
     * 执行回调
     */
    callMethod: function callMethod() {
      if (arguments[0] && Object.prototype.toString.call(arguments[0]) === '[object Function]') {
        arguments[0].apply(this, Array.prototype.slice.call(arguments).slice(1));
      }
    },

    /**
     * 正则校验车牌号码
     */
    isEnergyNumber: function isEnergyNumber(presetNumber) {
      return (/\W[A-Z][0-9DF][0-9A-Z]\d{3}[0-9DF]/.test(presetNumber)
      );
    }
  },
  components: {
    'number-view': __WEBPACK_IMPORTED_MODULE_0__number_view__["a" /* default */],
    'single-keyboard': __WEBPACK_IMPORTED_MODULE_1__single_keyboard__["a" /* default */]
  }
});

/***/ }),
/* 968 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__number_view__ = __webpack_require__(498);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__single_keyboard__ = __webpack_require__(499);
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//



var engine = __webpack_require__(420);
/* harmony default export */ __webpack_exports__["a"] = ({
  name: 'mixed-keyboard',
  props: {
    /**
     * 参数配置项
     * @param {String} presetNumber 预设车牌号
     * @param {Number} keyboardType 键盘类型[0:全键盘，1：民用, 2：民用+武警]
     * @param {String} provinceName 默认省份
     * @param {Boolean} forceChangeMode 是否强制切换键盘类型（忽略当前录入车牌有效性）
     * @param {Boolean} autoComplete 是否自动完成
     * @param {Boolean} showConfirm 是否显示确定按钮
     * @param {Boolean} showKeyTips 是否显示按键提示框(点击按键弹出当前按键内容提示，类似输入法)
     * @param {String} align //按键对齐方式，取值范围 [center: 居中对齐，经典键盘模式(默认), justify: 两端对齐，位数不够补充空白]
     * @param {String} position 键盘位置，取值范围 [static: 默认, bottom: 底部]
     */
    args: {
      type: Object,
      default: function _default() {}
    },
    /**
     * 回调函数
     */
    callbacks: {
      type: Object,
      default: function _default() {
        return {
          /**车牌改变监听(车牌号，是否已完成)*/
          onchanged: function onchanged(presetNumber, isCompleted) {},

          /**车牌完成监听(车牌号，是否自动完成)*/
          oncompleted: function oncompleted(presetNumber, isAutoCompleted) {},

          /**错误回调*/
          onmessage: function onmessage(message) {}
        };
      }
    }
  },
  data: function data() {
    var _this = this;

    return {
      options: {
        presetNumber: '', //预设车牌号码
        keyboardType: 1, //键盘类型[0:全键盘，1：民用, 2：民用+武警]
        provinceName: '', //省份
        currentIndex: 0, // 当前用户输入框已选中的序号
        showShortCut: true, // 需要显示省份简称
        forceChangeMode: false, //是否强制切换键盘类型
        numberType: engine.NUM_TYPES.AUTO_DETECT, // 车用户设定的车牌号码类型 0：自动探测车牌类型，5:新能源车牌
        autoComplete: true, //是否自动完成
        showConfirm: true, //是否显示确定按钮
        showKeyTips: false, //是否显示按键提示框
        align: 'center', //按键对齐方式，取值范围 [center: 居中对齐，经典键盘模式(默认), justify: 两端对齐，位数不够补充空白]
        position: ''
      },
      numberArray: ['', '', '', '', '', '', ''], // 用户输入的车牌数据
      userChanged: false, //用户是否外部修改了车牌号码
      singleCallbacks: {
        //// 回调函数
        onchanged: function onchanged(presetNumber, isCompleted) {
          _this.callMethod(_this.callbacks.onchanged, presetNumber, isCompleted);
        },
        oncompleted: function oncompleted(presetNumber, isAutoCompleted) {
          _this.callMethod(_this.callbacks.oncompleted, presetNumber, isAutoCompleted);
        },
        onkeypressed: function onkeypressed(key) {
          if (key.isFunKey) {
            _this.onFuncKeyClick(key);
          } else {
            _this.onTextKeyClick(key.text);
          }
          _this.callMethod(_this.callbacks.onkeypressed, key);
          return true;
        },
        onmessage: function onmessage(message) {
          _this.callMethod(_this.callbacks.onmessage, message);
        },
        updateKeyboard: function updateKeyboard(keyboard) {
          // 将识别结果的车牌模式同步到用户选择模式上
          if (keyboard.numberType === engine.NUM_TYPES.NEW_ENERGY) {
            _this.options.numberType = engine.NUM_TYPES.NEW_ENERGY;
          } else {
            _this.options.numberType = engine.NUM_TYPES.AUTO_DETECT;
          }
          _this.syncInputLength(keyboard.numberType, _this.options.numberType === engine.NUM_TYPES.NEW_ENERGY /*force to set NewEnergy mode*/
          );
        }
      }
    };
  },
  created: function created() {
    this.init();
  },

  watch: {
    options: {
      handler: function handler(val, oldVal) {
        this.$refs.singleKeyboard.updateArgs(val);
      },

      deep: true
    },
    detectNumberType: function detectNumberType(val) {
      this.options.numberType = val;
    }
  },
  computed: {
    /**
     * 当用户选择的车牌类型为非AUTO_DETECT类型时，使用用户强制设置类型：目前用户选择的类型有两个值：AUTO_DETECT / NEW_ENERGY
     */
    getNumberType: function getNumberType() {
      if (this.options.numberType === engine.NUM_TYPES.NEW_ENERGY) {
        return engine.NUM_TYPES.NEW_ENERGY;
      }
      return engine.NUM_TYPES.AUTO_DETECT;
    },
    dyDisplayMode: function dyDisplayMode() {
      // 用于显示的车牌类型
      if (this.options.numberType === engine.NUM_TYPES.NEW_ENERGY) {
        return engine.NUM_TYPES.NEW_ENERGY;
      }
      return this.detectNumberType === engine.NUM_TYPES.NEW_ENERGY ? engine.NUM_TYPES.NEW_ENERGY : engine.NUM_TYPES.AUTO_DETECT;
    },

    /**
     * 预测的车牌类型
     */
    detectNumberType: function detectNumberType() {
      return engine.detectNumberType(this.options.presetNumber, this.options.numberType);
    },
    keyboardStyle: function keyboardStyle() {
      return this.$slots && this.$slots.default > 0 ? {} : {
        'margin-top': '.78125rem'
      };
    },
    isCompleted: function isCompleted() {
      return this.numberArray.every(function (num) {
        return num && num !== '' && num !== null && num !== undefined;
      });
    }
  },
  methods: {
    /**
     * 初始化
     */
    init: function init() {
      this.options = Object.assign({}, this.options, this.args);
      this.$set(this.options, 'numberType', this.getNumberType);
      this.numberArray = this.rebuildNumberArray(this.options.presetNumber, this.numberArray.length /*要保证与原生长度一致*/
      );
      // 在用户更新车牌后，选中位置设置为当前车牌可输入的最后一位
      this.options.currentIndex = Math.max(0, Math.min(this.numberArray.length - 1, this.options.presetNumber.length));
      this.userChanged = true;
      this.options.showShortCut = true;
    },

    /**
     * 选中下一个输入序号的输入框
     */
    selectNextIndex: function selectNextIndex() {
      var next = this.options.currentIndex + 1;
      if (next <= this.numberArray.length - 1 /*限定在最大长度*/) {
          this.options.currentIndex = next;
        }
    },

    /**
     * 设置当前选择号码的位置
     */
    setNumberTxtAt: function setNumberTxtAt(index, text) {
      this.$set(this.numberArray, index, text);
      this.resetUserChanged();
    },

    /**
     * 切换新能源车牌（8位）
     */
    setLengthTo8: function setLengthTo8() {
      // 当前长度为7位长度时才允许切换至8位长度
      if (this.numberArray.length === 7) {
        // 扩展第8位：当前选中第7位，并且第7位已输入有效字符时，自动跳转到第8位
        if (6 === this.options.currentIndex && this.options.presetNumber.length === 7) {
          this.options.currentIndex = 7;
        }
        this.numberArray.push('');
        this.resetUserChanged();
      }
    },

    /**
     * 切换普通车牌（7位）
     */
    setLengthTo7: function setLengthTo7() {
      if (this.numberArray.length === 8) {
        if (7 === this.options.currentIndex) {
          // 处理最后一位的选中状态
          this.options.currentIndex = 6;
        }
        this.numberArray.pop();
        this.resetUserChanged();
      }
    },

    /**
     * 重置外部用户修改车牌标记位
     */
    resetUserChanged: function resetUserChanged() {
      this.options.presetNumber = this.numberArray.join('');
      this.userChanged = false;
    },

    /**
     * 切换用户强制车牌类型
     */
    onUserSetMode: function onUserSetMode() {
      // 如果当前车牌为武警车牌，不可切换：
      if (this.detectNumberType === engine.NUM_TYPES.WJ2007 || this.detectNumberType === engine.NUM_TYPES.WJ2012) {
        this.$refs.numberView.resetMode();
        this.callMethod(this.callbacks.onmessage, '武警车牌，请清空再切换');
        return;
      }
      if (this.options.numberType === engine.NUM_TYPES.NEW_ENERGY) {
        this.options.numberType = engine.NUM_TYPES.AUTO_DETECT;
      } else {
        // 已输入普通车牌如果不符合新能源车牌方式，不能切换为新能源车牌：
        var presetNumber = this.options.presetNumber;
        if (presetNumber.length > 2) {
          // 只输入前两个车牌号码，不参与校验
          var size = 8 - presetNumber.length;
          for (var i = 0; i < size; i++) {
            presetNumber += '0';
          } // 使用正则严格校验补全的车牌号码
          if (this.options.forceChangeMode === true || this.isEnergyNumber(presetNumber)) {
            this.options.numberType = engine.NUM_TYPES.NEW_ENERGY;
          } else {
            this.$refs.numberView.resetMode();
            this.callMethod(this.callbacks.onmessage, '非新能源车牌，请清空再切换');
            return;
          }
        } else {
          this.options.numberType = engine.NUM_TYPES.NEW_ENERGY;
        }
      }
      // 如果用户切换为新能源类型，则需要修改输入长度为8位：
      if (this.options.numberType === engine.NUM_TYPES.NEW_ENERGY) {
        this.setLengthTo8();
      } else {
        this.setLengthTo7();
      }
    },

    /**
     * 选中输入框
     */
    onSelectedInput: function onSelectedInput(index, shouldShowKeyboard) {
      var length = this.options.presetNumber.length;
      if (length > 0 && index <= length) {
        this.options.currentIndex = index;
      }
      if (true === shouldShowKeyboard) {
        /*强制显示键盘*/
        this.options.showShortCut = false;
      } else {
        this.options.showShortCut = this.options.currentIndex === 0;
      }
    },

    /**
     * 点击字符按键盘
     */
    onTextKeyClick: function onTextKeyClick(text, forceUpdate) {
      if (true === forceUpdate || text !== this.numberArray[this.options.currentIndex]) {
        this.setNumberTxtAt(this.options.currentIndex, text);
      }
      this.selectNextIndex(); // 选中下一个输入框
    },

    /**
     * 点击功能键
     */
    onFuncKeyClick: function onFuncKeyClick(key) {
      if (key.keyCode === engine.KEY_TYPES.FUN_DEL) {
        // 删除车辆号码的最后一位
        var maxIndex = this.numberArray.length - 1;
        var deleteIndex = Math.max(0, maxIndex);
        for (var i = maxIndex; i >= 0; i--) {
          if (this.numberArray[i].length !== 0) {
            deleteIndex = i;
            break;
          }
        }
        this.setNumberTxtAt(deleteIndex, '');
        // 更新删除时的选中状态
        this.options.currentIndex = deleteIndex;
      }
    },

    /**
     * 将车牌号码，生成一个车牌字符数组
     */
    rebuildNumberArray: function rebuildNumberArray(updateNumber, originLength) {
      var output = ['', '', '', '', '', '', '']; // 普通车牌长度为7位，最大长度为8位
      if (originLength > 7) {
        output.push('');
      }
      if (updateNumber !== undefined && updateNumber.length !== 0) {
        var size = Math.min(8, updateNumber.length);
        for (var i = 0; i < size; i++) {
          output[i] = updateNumber.charAt(i);
        }
      }
      return output;
    },

    /**
     * 同步输入长度
     */
    syncInputLength: function syncInputLength(mode, forceNewEnergyMode) {
      // 键盘引擎根据输入参数，会自动推测出当前车牌的类型。
      // 如果当前用户没有强制设置，更新键盘的输入框长度以适当当前的车牌类型,（指地方武警车牌，长度为8位）
      if (forceNewEnergyMode) {
        // 强制新能源类型，应当设置为：8位
        this.setLengthTo8();
      } else {
        if (engine.NUM_TYPES.WJ2012 === mode || engine.NUM_TYPES.NEW_ENERGY === mode) {
          // 地方武警，应当设置为：8位
          this.setLengthTo8();
        } else {
          // 其它车牌，应当设置为：7位
          this.setLengthTo7();
        }
      }
    },

    /**
     * 执行回调
     */
    callMethod: function callMethod() {
      if (arguments[0] && Object.prototype.toString.call(arguments[0]) === '[object Function]') {
        arguments[0].apply(this, Array.prototype.slice.call(arguments).slice(1));
      }
    },

    /**
     * 正则校验车牌号码
     */
    isEnergyNumber: function isEnergyNumber(presetNumber) {
      return (/\W[A-Z][0-9DF][0-9A-Z]\d{3}[0-9DF]/.test(presetNumber)
      );
    }
  },
  components: {
    'number-view': __WEBPACK_IMPORTED_MODULE_0__number_view__["a" /* default */],
    'single-keyboard': __WEBPACK_IMPORTED_MODULE_1__single_keyboard__["a" /* default */]
  }
});

/***/ }),
/* 969 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('div',{staticClass:"mixed-keyboard-box"},[_c('number-view',{ref:"numberView",attrs:{"number-array":_vm.numberArray,"number-type":_vm.dyDisplayMode,"current-index":_vm.options.currentIndex},on:{"modechanged":_vm.onUserSetMode,"cellselected":_vm.onSelectedInput}}),_vm._v(" "),_vm._t("default"),_vm._v(" "),_c('single-keyboard',{ref:"singleKeyboard",class:_vm.args.position,style:(_vm.keyboardStyle),attrs:{"args":_vm.options,"callbacks":_vm.singleCallbacks}})],2)}
var staticRenderFns = []
var esExports = { render: render, staticRenderFns: staticRenderFns }
/* harmony default export */ __webpack_exports__["a"] = (esExports);

/***/ }),
/* 970 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_assets_js_Lib__ = __webpack_require__(3);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_assets_js_api__ = __webpack_require__(4);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2_assets_js_router__ = __webpack_require__(40);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_3_vehicle_keyboard__ = __webpack_require__(473);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_4_components__ = __webpack_require__(34);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_5_vux_src_components_view_box_index_vue__ = __webpack_require__(7);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_6_vux_src_components_msg_index_vue__ = __webpack_require__(116);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_7_vux_src_components_x_button_index_vue__ = __webpack_require__(12);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_8_vux_src_components_popup_index_vue__ = __webpack_require__(51);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_9_vux_src_directives_transfer_dom_index_js__ = __webpack_require__(6);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_10_vux_src_components_tabbar_tabbar_vue__ = __webpack_require__(19);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_11_vux_src_components_tabbar_tabbar_item_vue__ = __webpack_require__(25);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_12_vux_src_components_x_header_index_vue__ = __webpack_require__(9);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_13_vux_src_components_x_dialog_index_vue__ = __webpack_require__(68);
















/* harmony default export */ __webpack_exports__["a"] = ({
  directives: {
    TransferDom: __WEBPACK_IMPORTED_MODULE_9_vux_src_directives_transfer_dom_index_js__["a" /* default */]
  },
  data: function data() {
    var _this = this;

    return {
      keyboardPopupIng: false,
      presetNumber: '',
      placeholderShow: true,
      cursorShow: false,
      args: {

        presetNumber: '',
        keyboardType: 0,
        forceChangeMode: true,
        provinceName: '',
        autoComplete: true,
        showKeyTips: true,
        position: 'bottom'
      },
      callbacks: {

        onchanged: function onchanged(presetNumber, isCompleted) {
          _this.args.presetNumber = presetNumber;
          console.log('当前车牌[变更]：' + presetNumber + ', 已完成:' + isCompleted);
        },

        onkeypressed: function onkeypressed(key) {
          console.log('当前按键：' + key.text);
        },

        oncompleted: function oncompleted(presetNumber, isAutoCompleted) {
          _this.presetNumber = presetNumber;
          _this.keyboardPopupIng = false;
          _this.numberArray = presetNumber.split('');
          console.log('当前车牌[完成]：' + presetNumber + ', 自动完成:' + isAutoCompleted);
        },

        onmessage: function onmessage(message) {
          console.info(message);
        }
      },
      lpnList: [],
      defaultTit: '',
      loadIng: false,
      settingItems: [],
      px: 0,
      setDefaultDialog: false,
      delDefaultDialog: false,
      dialogName: '',
      dialogId: 0,
      dialogType: 1,
      delIndex: 0
    };
  },

  components: {
    ViewBox: __WEBPACK_IMPORTED_MODULE_5_vux_src_components_view_box_index_vue__["a" /* default */], Msg: __WEBPACK_IMPORTED_MODULE_6_vux_src_components_msg_index_vue__["a" /* default */], XButton: __WEBPACK_IMPORTED_MODULE_7_vux_src_components_x_button_index_vue__["a" /* default */], Popup: __WEBPACK_IMPORTED_MODULE_8_vux_src_components_popup_index_vue__["a" /* default */], Tabbar: __WEBPACK_IMPORTED_MODULE_10_vux_src_components_tabbar_tabbar_vue__["a" /* default */], TabbarItem: __WEBPACK_IMPORTED_MODULE_11_vux_src_components_tabbar_tabbar_item_vue__["a" /* default */], MixedKeyboard: __WEBPACK_IMPORTED_MODULE_3_vehicle_keyboard__["a" /* MixedKeyboard */], LnLoading: __WEBPACK_IMPORTED_MODULE_4_components__["d" /* LnLoading */], XHeader: __WEBPACK_IMPORTED_MODULE_12_vux_src_components_x_header_index_vue__["a" /* default */], XDialog: __WEBPACK_IMPORTED_MODULE_13_vux_src_components_x_dialog_index_vue__["a" /* default */]
  },

  beforeCreate: function beforeCreate() {
    __WEBPACK_IMPORTED_MODULE_0_assets_js_Lib__["a" /* default */].M.setTitle('停车');
  },
  beforeMount: function beforeMount() {
    this.getCatList();
    this.getSetting();
  },

  methods: {
    getSetting: function getSetting() {
      var _this2 = this;

      __WEBPACK_IMPORTED_MODULE_1_assets_js_api__["a" /* default */].setting().then(function (res) {
        _this2.settingItems = res.data;
        _this2.px = _this2.settingItems.hstatus ? '46px' : '0';
      });
    },
    getShortPayInfo: function getShortPayInfo(defaultTit) {
      var _this3 = this;

      if (defaultTit === '正在加载') {
        setTimeout(function () {
          _this3.loadIng = false;
        }, 500);
        return;
      }
      var data = {
        carno: defaultTit
      };
      __WEBPACK_IMPORTED_MODULE_1_assets_js_api__["a" /* default */].getShortPayInfo(data).then(function (res) {
        if (res.err_code === 0) {
          if (res.data.code === 10003) {
            _this3.$vux.toast.show({
              type: 'text',
              text: res.data.content,
              width: '17em'
            });
          } else {
            _this3.goUrl('/timepay/' + defaultTit);
          }
          setTimeout(function () {
            _this3.loadIng = false;
          }, 500);
        }
      });
    },
    onPost: function onPost() {
      var _this4 = this;

      var data = {
        carno: this.presetNumber,
        type: 2
      };
      __WEBPACK_IMPORTED_MODULE_1_assets_js_api__["a" /* default */].addCarInfo(data).then(function (res) {
        if (res.err_code === 0) {
          _this4.$vux.toast.show({
            text: '解除绑定成功',
            type: 'success'
          });
          _this4.defaultTit = _this4.presetNumber;
          _this4.placeholderShow = true;
          _this4.cursorShow = false;
          _this4.presetNumber = '';
          _this4.getCatList();
        }
      });
    },
    bindCancelHandler: function bindCancelHandler(carid, carname, index) {
      this.dialogName = carname;
      this.delDefaultDialog = true;
      this.dialogId = carid;
      this.delIndex = index;
    },
    setDefaultHandler: function setDefaultHandler(carid, carname, index) {
      this.dialogName = carname;
      this.setDefaultDialog = true;
      this.dialogId = carid;
    },
    confirmDialog: function confirmDialog(type) {
      var _this5 = this;

      var self = this;
      if (type === 1) {
        __WEBPACK_IMPORTED_MODULE_1_assets_js_api__["a" /* default */].changeUserCar(this.dialogId).then(function (res) {
          _this5.setDefaultDialog = false;
          if (res.err_code === 0) {
            _this5.defaultTit = _this5.presetNumber;
            _this5.getCatList();
            _this5.$vux.toast.show({
              text: '设置成功',
              type: 'success'
            });
          }
        }).catch(function (error) {
          self.showDialog = false;
          self.$vux.toast.show({
            type: 'warn',
            text: error.err_msg,
            width: '14em'
          });
        });
      } else {
        __WEBPACK_IMPORTED_MODULE_1_assets_js_api__["a" /* default */].delCarInfo(this.dialogId).then(function (res) {
          _this5.delDefaultDialog = false;
          if (res.err_code === 0) {
            _this5.$vux.toast.show({
              text: '解除绑定成功',
              type: 'success'
            });
            _this5.lpnList.splice(_this5.delIndex, 1);
          }
        }).catch(function (error) {
          self.delDefaultDialog = false;
          self.$vux.toast.show({
            type: 'warn',
            text: error.err_msg,
            width: '14em'
          });
        });
      }
    },
    hideDialog: function hideDialog(type) {
      if (type === 1) {
        this.setDefaultDialog = false;
      } else {
        this.delDefaultDialog = false;
      }
    },
    showOrHideOpHandler: function showOrHideOpHandler(index) {
      this.lpnList[index].showHandler = !this.lpnList[index].showHandler;
    },
    getCatList: function getCatList() {
      var _this6 = this;

      __WEBPACK_IMPORTED_MODULE_1_assets_js_api__["a" /* default */].getCarsList().then(function (res) {
        _this6.defaultTit = '正在加载';
        if (res.err_code === 0) {
          var list = res.data;
          var ret = [];
          list.forEach(function (item, index) {
            if (item.enable === 1 || item.enable === '1') {
              _this6.defaultTit = item.carno;
            }
            ret.push({
              id: item.carid,
              carno: item.carno,
              enable: item.enable,
              showHandler: false
            });
          });
          _this6.lpnList = ret;
        }
        if (_this6.defaultTit) {
          _this6.loadIng = true;
          _this6.getShortPayInfo(_this6.defaultTit);
        }
      });
    },
    inputFun: function inputFun() {
      this.placeholderShow = false;
      this.cursorShow = true;
      this.keyboardPopupIng = true;
    },
    goUrl: function goUrl(url) {
      url && Object(__WEBPACK_IMPORTED_MODULE_2_assets_js_router__["a" /* go */])(url, this.$router);
    },
    url: function url() {
      return __WEBPACK_IMPORTED_MODULE_0_assets_js_Lib__["a" /* default */].M.url('home');
    }
  }
});

/***/ }),
/* 971 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('view-box',{ref:"viewBox",staticClass:"container",attrs:{"body-padding-top":_vm.px,"body-padding-bottom":"0px"}},[(_vm.settingItems.hstatus)?_c('x-header',{staticStyle:{"position":"absolute","width":"100%","left":"0","top":"0","z-index":"100"},attrs:{"slot":"header","title":"停车"},slot:"header"},[_c('a',{staticStyle:{"color":"#FFFFFF"},attrs:{"slot":"right","href":_vm.url()},slot:"right"},[_vm._v("首页")])]):_vm._e(),_vm._v(" "),_c('div',{staticClass:"bind-container"},[_c('div',{staticClass:"car-bg"},[_c('div',{staticClass:"input-box"},[_c('div',{staticClass:"input-bar",on:{"click":function($event){_vm.inputFun()}}},[_c('span',{directives:[{name:"show",rawName:"v-show",value:(_vm.placeholderShow),expression:"placeholderShow"}],attrs:{"id":"placeholder"}},[_vm._v("请输入完整车牌号")]),_vm._v(" "),_c('span',{directives:[{name:"show",rawName:"v-show",value:(_vm.cursorShow),expression:"cursorShow"}],class:_vm.cursorShow? 'cursor':'',attrs:{"id":"carplatnum"}},[_vm._v(_vm._s(_vm.presetNumber))])]),_vm._v(" "),(_vm.presetNumber)?_c('div',{staticClass:"add-btn",on:{"click":_vm.onPost}},[_vm._v("保存")]):_c('div',{staticClass:"add-btn",on:{"click":function($event){_vm.keyboardPopupIng=true}}},[_vm._v("新增")])]),_vm._v(" "),(_vm.lpnList && _vm.lpnList.length > 0)?_c('div',{staticClass:"history-box"},[_c('div',{staticClass:"tip"},[_vm._v("您绑定的车牌")]),_vm._v(" "),_c('div',{staticClass:"item-list"},_vm._l((_vm.lpnList),function(item,index){return (_vm.lpnList && _vm.lpnList.length > 0)?_c('div',[_c('div',{staticClass:"item",on:{"click":function($event){_vm.showOrHideOpHandler(index)}}},[_c('div',{staticClass:"content row"},[_c('div',{staticClass:"label col"},[_c('span',{staticClass:"lpn",class:item.enable === 1||item.enable==='1'?'default-lpn':'',domProps:{"textContent":_vm._s(item.carno)}}),_vm._v(" "),(item.enable === 1||item.enable==='1')?_c('span',{staticClass:"default"},[_vm._v("默认")]):_vm._e()]),_vm._v(" "),_c('div',{staticClass:"label-info more",class:item.showHandler?'up':'down'})])]),_vm._v(" "),_c('div',{staticClass:"op-item row",class:item.showHandler?'move-down':'move-up'},[(item.enable === 0||item.enable==='0')?_c('div',{staticClass:"col",on:{"click":function($event){_vm.setDefaultHandler(item.id,item.carno,index)}}},[_vm._v("设为默认")]):_vm._e(),_vm._v(" "),_c('div',{staticClass:"col",on:{"click":function($event){_vm.bindCancelHandler(item.id,item.carno,index)}}},[_vm._v("解除绑定")])])]):_vm._e()}))]):_vm._e()])]),_vm._v(" "),_c('div',{directives:[{name:"transfer-dom",rawName:"v-transfer-dom"}]},[_c('popup',{attrs:{"height":"60%"},model:{value:(_vm.keyboardPopupIng),callback:function ($$v) {_vm.keyboardPopupIng=$$v},expression:"keyboardPopupIng"}},[_c('mixed-keyboard',{attrs:{"args":_vm.args,"callbacks":_vm.callbacks}})],1)],1),_vm._v(" "),_c('div',{directives:[{name:"transfer-dom",rawName:"v-transfer-dom"}]},[_c('x-dialog',{staticClass:"ln-dialog",attrs:{"dialog-transition":"vux-dialogX","mask-z-index":"1000"},model:{value:(_vm.setDefaultDialog),callback:function ($$v) {_vm.setDefaultDialog=$$v},expression:"setDefaultDialog"}},[_c('div',{staticClass:"weui-dialog__hd"},[_c('strong',{staticClass:"weui-dialog__title"},[_vm._v(_vm._s(_vm.dialogName))])]),_vm._v(" "),_c('div',{staticClass:"weui-dialog__bd"},[_c('div',[_vm._v("确定设为默认车牌吗？")])]),_vm._v(" "),_c('div',{staticClass:"weui-dialog__ft"},[_c('a',{staticClass:"weui-dialog__btn weui-dialog__btn_default",attrs:{"href":"javascript:;"},on:{"click":function($event){_vm.hideDialog(1)}}},[_vm._v("取消")]),_vm._v(" "),_c('a',{staticClass:"weui-dialog__btn weui-dialog__btn_primary",attrs:{"href":"javascript:;"},on:{"click":function($event){_vm.confirmDialog(1)}}},[_vm._v("确定")])])])],1),_vm._v(" "),_c('div',{directives:[{name:"transfer-dom",rawName:"v-transfer-dom"}]},[_c('x-dialog',{staticClass:"ln-dialog",attrs:{"dialog-transition":"vux-dialogX","mask-z-index":"1000"},model:{value:(_vm.delDefaultDialog),callback:function ($$v) {_vm.delDefaultDialog=$$v},expression:"delDefaultDialog"}},[_c('div',{staticClass:"weui-dialog__hd"},[_c('strong',{staticClass:"weui-dialog__title"},[_vm._v(_vm._s(_vm.dialogName))])]),_vm._v(" "),_c('div',{staticClass:"weui-dialog__bd"},[_c('div',[_vm._v("确定解除绑定吗？")])]),_vm._v(" "),_c('div',{staticClass:"weui-dialog__ft"},[_c('a',{staticClass:"weui-dialog__btn weui-dialog__btn_default",attrs:{"href":"javascript:;"},on:{"click":function($event){_vm.hideDialog(2)}}},[_vm._v("取消")]),_vm._v(" "),_c('a',{staticClass:"weui-dialog__btn weui-dialog__btn_primary",attrs:{"href":"javascript:;"},on:{"click":function($event){_vm.confirmDialog(2)}}},[_vm._v("确定")])])])],1),_vm._v(" "),_c('div',{staticClass:"ln-tabbar"},[_c('tabbar',[_c('tabbar-item',{attrs:{"selected":"","link":"/"}},[_c('span',{staticClass:"iconfont icon-linshitingche",attrs:{"slot":"icon"},slot:"icon"}),_vm._v(" "),_c('span',{attrs:{"slot":"label"},slot:"label"},[_vm._v("临时缴费")])]),_vm._v(" "),_c('tabbar-item',{attrs:{"link":"/monthly"}},[_c('span',{staticClass:"iconfont icon-yue",attrs:{"slot":"icon"},slot:"icon"}),_vm._v(" "),_c('span',{attrs:{"slot":"label"},slot:"label"},[_vm._v("月租缴费")])]),_vm._v(" "),_c('tabbar-item',{attrs:{"link":"/history"}},[_c('span',{staticClass:"iconfont icon-icon_history",attrs:{"slot":"icon"},slot:"icon"}),_vm._v(" "),_c('span',{attrs:{"slot":"label"},slot:"label"},[_vm._v("缴费记录")])])],1)],1),_vm._v(" "),_c('ln-loading',{attrs:{"title":_vm.defaultTit,"loadIng":_vm.loadIng}})],1)}
var staticRenderFns = []
var esExports = { render: render, staticRenderFns: staticRenderFns }
/* harmony default export */ __webpack_exports__["a"] = (esExports);

/***/ }),
/* 972 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vux_loader_src_script_loader_js_node_modules_vux_loader_src_script_loader_js_node_modules_vue_loader_lib_selector_type_script_index_0_monthlyApp_vue__ = __webpack_require__(974);
/* unused harmony namespace reexport */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__babel_loader_node_modules_vux_loader_src_script_loader_js_node_modules_vue_loader_lib_selector_type_script_index_0_monthlyApp_vue__ = __webpack_require__(975);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__node_modules_vue_loader_lib_template_compiler_index_id_data_v_6c9b3e8d_hasScoped_true_buble_transforms_node_modules_vux_loader_src_before_template_compiler_loader_js_node_modules_vux_loader_src_template_loader_js_node_modules_vue_loader_lib_selector_type_template_index_0_monthlyApp_vue__ = __webpack_require__(976);
function injectStyle (ssrContext) {
  __webpack_require__(973)
}
var normalizeComponent = __webpack_require__(0)
/* script */


/* template */

/* template functional */
var __vue_template_functional__ = false
/* styles */
var __vue_styles__ = injectStyle
/* scopeId */
var __vue_scopeId__ = "data-v-6c9b3e8d"
/* moduleIdentifier (server only) */
var __vue_module_identifier__ = null
var Component = normalizeComponent(
  __WEBPACK_IMPORTED_MODULE_1__babel_loader_node_modules_vux_loader_src_script_loader_js_node_modules_vue_loader_lib_selector_type_script_index_0_monthlyApp_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_2__node_modules_vue_loader_lib_template_compiler_index_id_data_v_6c9b3e8d_hasScoped_true_buble_transforms_node_modules_vux_loader_src_before_template_compiler_loader_js_node_modules_vux_loader_src_template_loader_js_node_modules_vue_loader_lib_selector_type_template_index_0_monthlyApp_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)

/* harmony default export */ __webpack_exports__["a"] = (Component.exports);


/***/ }),
/* 973 */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),
/* 974 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_assets_js_Lib__ = __webpack_require__(3);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_assets_js_api__ = __webpack_require__(4);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2_assets_js_router__ = __webpack_require__(40);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_3_vux_src_components_view_box_index_vue__ = __webpack_require__(7);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_4_vux_src_components_tabbar_tabbar_vue__ = __webpack_require__(19);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_5_vux_src_components_tabbar_tabbar_item_vue__ = __webpack_require__(25);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_6_vux_src_components_x_button_index_vue__ = __webpack_require__(12);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_7_vux_src_components_box_index_vue__ = __webpack_require__(30);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_8_vux_src_components_x_header_index_vue__ = __webpack_require__(9);











/* unused harmony default export */ var _unused_webpack_default_export = ({
  data: function data() {
    return {
      cardList: [],
      settingItems: [],
      px: 0
    };
  },

  components: {
    ViewBox: __WEBPACK_IMPORTED_MODULE_3_vux_src_components_view_box_index_vue__["a" /* default */], Tabbar: __WEBPACK_IMPORTED_MODULE_4_vux_src_components_tabbar_tabbar_vue__["a" /* default */], TabbarItem: __WEBPACK_IMPORTED_MODULE_5_vux_src_components_tabbar_tabbar_item_vue__["a" /* default */], XButton: __WEBPACK_IMPORTED_MODULE_6_vux_src_components_x_button_index_vue__["a" /* default */], Box: __WEBPACK_IMPORTED_MODULE_7_vux_src_components_box_index_vue__["a" /* default */], XHeader: __WEBPACK_IMPORTED_MODULE_8_vux_src_components_x_header_index_vue__["a" /* default */]
  },

  beforeCreate: function beforeCreate() {
    __WEBPACK_IMPORTED_MODULE_0_assets_js_Lib__["a" /* default */].M.setTitle('月租车管理');
  },
  beforeMount: function beforeMount() {
    this.$vux.loading.show();
    this.getCarsList();
    this.getSetting();
  },
  mounted: function mounted() {
    var _this = this;

    setTimeout(function () {
      _this.$vux.loading.hide();
    }, 500);
  },


  methods: {
    url: function url() {
      return __WEBPACK_IMPORTED_MODULE_0_assets_js_Lib__["a" /* default */].M.url('home');
    },
    getSetting: function getSetting() {
      var _this2 = this;

      __WEBPACK_IMPORTED_MODULE_1_assets_js_api__["a" /* default */].setting().then(function (res) {
        _this2.settingItems = res.data;
        _this2.px = _this2.settingItems.hstatus ? '46px' : '0';
      });
    },
    delFun: function delFun(id, carname, index) {
      var that = this;
      that.$vux.confirm.show({
        title: carname,
        content: '移除后，月卡到期不再发送续费提醒！',
        onConfirm: function onConfirm() {
          __WEBPACK_IMPORTED_MODULE_1_assets_js_api__["a" /* default */].delCarInfo(id).then(function (res) {
            if (res.err_code === 0) {
              that.$vux.toast.text('删除成功');
              that.cardList.splice(index, 1);
            }
          });
        }
      });
    },
    getCarsList: function getCarsList() {
      var _this3 = this;

      __WEBPACK_IMPORTED_MODULE_1_assets_js_api__["a" /* default */].getMonthCarsList().then(function (res) {
        if (res.err_code === 0) {
          _this3.cardList = res.data;
          console.log(_this3.cardList);
        }
      });
    },
    goUrl: function goUrl(url) {
      url && Object(__WEBPACK_IMPORTED_MODULE_2_assets_js_router__["a" /* go */])(url, this.$router);
    }
  },

  computed: {}
});

/***/ }),
/* 975 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_assets_js_Lib__ = __webpack_require__(3);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_assets_js_api__ = __webpack_require__(4);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2_assets_js_router__ = __webpack_require__(40);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_3_vux_src_components_view_box_index_vue__ = __webpack_require__(7);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_4_vux_src_components_tabbar_tabbar_vue__ = __webpack_require__(19);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_5_vux_src_components_tabbar_tabbar_item_vue__ = __webpack_require__(25);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_6_vux_src_components_x_button_index_vue__ = __webpack_require__(12);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_7_vux_src_components_box_index_vue__ = __webpack_require__(30);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_8_vux_src_components_x_header_index_vue__ = __webpack_require__(9);











/* harmony default export */ __webpack_exports__["a"] = ({
  data: function data() {
    return {
      cardList: [],
      settingItems: [],
      px: 0
    };
  },

  components: {
    ViewBox: __WEBPACK_IMPORTED_MODULE_3_vux_src_components_view_box_index_vue__["a" /* default */], Tabbar: __WEBPACK_IMPORTED_MODULE_4_vux_src_components_tabbar_tabbar_vue__["a" /* default */], TabbarItem: __WEBPACK_IMPORTED_MODULE_5_vux_src_components_tabbar_tabbar_item_vue__["a" /* default */], XButton: __WEBPACK_IMPORTED_MODULE_6_vux_src_components_x_button_index_vue__["a" /* default */], Box: __WEBPACK_IMPORTED_MODULE_7_vux_src_components_box_index_vue__["a" /* default */], XHeader: __WEBPACK_IMPORTED_MODULE_8_vux_src_components_x_header_index_vue__["a" /* default */]
  },

  beforeCreate: function beforeCreate() {
    __WEBPACK_IMPORTED_MODULE_0_assets_js_Lib__["a" /* default */].M.setTitle('月租车管理');
  },
  beforeMount: function beforeMount() {
    this.$vux.loading.show();
    this.getCarsList();
    this.getSetting();
  },
  mounted: function mounted() {
    var _this = this;

    setTimeout(function () {
      _this.$vux.loading.hide();
    }, 500);
  },


  methods: {
    url: function url() {
      return __WEBPACK_IMPORTED_MODULE_0_assets_js_Lib__["a" /* default */].M.url('home');
    },
    getSetting: function getSetting() {
      var _this2 = this;

      __WEBPACK_IMPORTED_MODULE_1_assets_js_api__["a" /* default */].setting().then(function (res) {
        _this2.settingItems = res.data;
        _this2.px = _this2.settingItems.hstatus ? '46px' : '0';
      });
    },
    delFun: function delFun(id, carname, index) {
      var that = this;
      that.$vux.confirm.show({
        title: carname,
        content: '移除后，月卡到期不再发送续费提醒！',
        onConfirm: function onConfirm() {
          __WEBPACK_IMPORTED_MODULE_1_assets_js_api__["a" /* default */].delCarInfo(id).then(function (res) {
            if (res.err_code === 0) {
              that.$vux.toast.text('删除成功');
              that.cardList.splice(index, 1);
            }
          });
        }
      });
    },
    getCarsList: function getCarsList() {
      var _this3 = this;

      __WEBPACK_IMPORTED_MODULE_1_assets_js_api__["a" /* default */].getMonthCarsList().then(function (res) {
        if (res.err_code === 0) {
          _this3.cardList = res.data;
          console.log(_this3.cardList);
        }
      });
    },
    goUrl: function goUrl(url) {
      url && Object(__WEBPACK_IMPORTED_MODULE_2_assets_js_router__["a" /* go */])(url, this.$router);
    }
  },

  computed: {}
});

/***/ }),
/* 976 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('view-box',{ref:"viewBox",staticClass:"container",attrs:{"body-padding-top":_vm.px,"body-padding-bottom":"53px"}},[(_vm.settingItems.hstatus)?_c('x-header',{staticStyle:{"position":"absolute","width":"100%","left":"0","top":"0","z-index":"100"},attrs:{"slot":"header","title":"月租车管理"},slot:"header"},[_c('a',{staticStyle:{"color":"#FFFFFF"},attrs:{"slot":"right","href":_vm.url()},slot:"right"},[_vm._v("首页")])]):_vm._e(),_vm._v(" "),(_vm.cardList&&_vm.cardList.length>0)?[_c('div',{attrs:{"id":"tabBox"}},[_c('section',{staticClass:"card-side"},[_c('ul',_vm._l((_vm.cardList),function(card,index){return _c('li',{key:index,staticClass:"card-main"},[_c('div',{staticClass:"car-card-head"},[_c('h3',{domProps:{"textContent":_vm._s(card.carno)}}),_vm._v(" "),_c('p',{domProps:{"textContent":_vm._s(card.title)}},[_vm._v("观音山1号楼车场")]),_vm._v(" "),_c('p',[_vm._v("剩余额度： "),_c('span',{domProps:{"textContent":_vm._s(card.days)}})]),_vm._v(" "),_c('div',{staticClass:"car-card-super"},[_vm._v("月租车")])]),_vm._v(" "),_c('div',{staticClass:"car-card-btn vux-1px-t row"},[_c('div',{staticClass:"col vux-1px-r",on:{"click":function($event){_vm.delFun(card.carid,card.carno,index)}}},[_vm._v("删除")]),_vm._v(" "),(card.verity == 1)?_c('div',{staticClass:"col",on:{"click":function($event){_vm.goUrl('/monthpay/'+card.parkid+'/'+card.carno)}}},[_vm._v("充值")]):_vm._e()])])}))]),_vm._v(" "),_c('box',{attrs:{"gap":"10px 20px 10px"}},[_c('x-button',{attrs:{"type":"primary"},nativeOn:{"click":function($event){_vm.goUrl('/bind')}}},[_vm._v("绑定月租车辆")])],1)],1)]:[_c('div',{staticClass:"no-card"},[_vm._v("\n            您还未绑定车辆，暂不可查询和充值\n        ")]),_vm._v(" "),_c('box',{attrs:{"gap":"100px 20px 10px"}},[_c('x-button',{attrs:{"type":"primary"},nativeOn:{"click":function($event){_vm.goUrl('/bind')}}},[_vm._v("绑定月租车辆")])],1)],_vm._v(" "),_c('div',{staticClass:"ln-tabbar"},[_c('tabbar',[_c('tabbar-item',{attrs:{"link":"/"}},[_c('span',{staticClass:"iconfont icon-linshitingche",attrs:{"slot":"icon"},slot:"icon"}),_vm._v(" "),_c('span',{attrs:{"slot":"label"},slot:"label"},[_vm._v("临时缴费")])]),_vm._v(" "),_c('tabbar-item',{attrs:{"selected":"","link":"/monthly"}},[_c('span',{staticClass:"iconfont icon-yue",attrs:{"slot":"icon"},slot:"icon"}),_vm._v(" "),_c('span',{attrs:{"slot":"label"},slot:"label"},[_vm._v("月租缴费")])]),_vm._v(" "),_c('tabbar-item',{attrs:{"link":"/history"}},[_c('span',{staticClass:"iconfont icon-icon_history",attrs:{"slot":"icon"},slot:"icon"}),_vm._v(" "),_c('span',{attrs:{"slot":"label"},slot:"label"},[_vm._v("缴费记录")])])],1)],1)],2)}
var staticRenderFns = []
var esExports = { render: render, staticRenderFns: staticRenderFns }
/* harmony default export */ __webpack_exports__["a"] = (esExports);

/***/ }),
/* 977 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vux_loader_src_script_loader_js_node_modules_vux_loader_src_script_loader_js_node_modules_vue_loader_lib_selector_type_script_index_0_historyApp_vue__ = __webpack_require__(979);
/* unused harmony namespace reexport */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__babel_loader_node_modules_vux_loader_src_script_loader_js_node_modules_vue_loader_lib_selector_type_script_index_0_historyApp_vue__ = __webpack_require__(980);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__node_modules_vue_loader_lib_template_compiler_index_id_data_v_201a0d95_hasScoped_true_buble_transforms_node_modules_vux_loader_src_before_template_compiler_loader_js_node_modules_vux_loader_src_template_loader_js_node_modules_vue_loader_lib_selector_type_template_index_0_historyApp_vue__ = __webpack_require__(981);
function injectStyle (ssrContext) {
  __webpack_require__(978)
}
var normalizeComponent = __webpack_require__(0)
/* script */


/* template */

/* template functional */
var __vue_template_functional__ = false
/* styles */
var __vue_styles__ = injectStyle
/* scopeId */
var __vue_scopeId__ = "data-v-201a0d95"
/* moduleIdentifier (server only) */
var __vue_module_identifier__ = null
var Component = normalizeComponent(
  __WEBPACK_IMPORTED_MODULE_1__babel_loader_node_modules_vux_loader_src_script_loader_js_node_modules_vue_loader_lib_selector_type_script_index_0_historyApp_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_2__node_modules_vue_loader_lib_template_compiler_index_id_data_v_201a0d95_hasScoped_true_buble_transforms_node_modules_vux_loader_src_before_template_compiler_loader_js_node_modules_vux_loader_src_template_loader_js_node_modules_vue_loader_lib_selector_type_template_index_0_historyApp_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)

/* harmony default export */ __webpack_exports__["a"] = (Component.exports);


/***/ }),
/* 978 */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),
/* 979 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_assets_js_Lib__ = __webpack_require__(3);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_assets_js_api__ = __webpack_require__(4);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2_assets_js_router__ = __webpack_require__(40);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_3_vux_src_components_view_box_index_vue__ = __webpack_require__(7);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_4_vux_src_directives_transfer_dom_index_js__ = __webpack_require__(6);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_5_vux_src_components_tabbar_tabbar_vue__ = __webpack_require__(19);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_6_vux_src_components_tabbar_tabbar_item_vue__ = __webpack_require__(25);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_7_vux_src_components_card_index_vue__ = __webpack_require__(390);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_8_vux_src_components_x_header_index_vue__ = __webpack_require__(9);











/* unused harmony default export */ var _unused_webpack_default_export = ({
  directives: {
    TransferDom: __WEBPACK_IMPORTED_MODULE_4_vux_src_directives_transfer_dom_index_js__["a" /* default */]
  },
  data: function data() {
    return {
      shortNum: 0,
      monthNum: 0,
      settingItems: [],
      px: 0
    };
  },

  components: {
    ViewBox: __WEBPACK_IMPORTED_MODULE_3_vux_src_components_view_box_index_vue__["a" /* default */], Tabbar: __WEBPACK_IMPORTED_MODULE_5_vux_src_components_tabbar_tabbar_vue__["a" /* default */], TabbarItem: __WEBPACK_IMPORTED_MODULE_6_vux_src_components_tabbar_tabbar_item_vue__["a" /* default */], Card: __WEBPACK_IMPORTED_MODULE_7_vux_src_components_card_index_vue__["a" /* default */], XHeader: __WEBPACK_IMPORTED_MODULE_8_vux_src_components_x_header_index_vue__["a" /* default */]
  },

  beforeCreate: function beforeCreate() {
    __WEBPACK_IMPORTED_MODULE_0_assets_js_Lib__["a" /* default */].M.setTitle('缴费记录');
  },
  beforeMount: function beforeMount() {
    this.$vux.loading.show();
    this.getNum();
    this.getSetting();
  },
  mounted: function mounted() {
    var _this = this;

    setTimeout(function () {
      _this.$vux.loading.hide();
    }, 500);
  },

  methods: {
    url: function url() {
      return __WEBPACK_IMPORTED_MODULE_0_assets_js_Lib__["a" /* default */].M.url('home');
    },
    getSetting: function getSetting() {
      var _this2 = this;

      __WEBPACK_IMPORTED_MODULE_1_assets_js_api__["a" /* default */].setting().then(function (res) {
        _this2.settingItems = res.data;
        _this2.px = _this2.settingItems.hstatus ? '46px' : '0';
      });
    },
    getNum: function getNum() {
      var _this3 = this;

      __WEBPACK_IMPORTED_MODULE_1_assets_js_api__["a" /* default */].getHistoryList().then(function (res) {
        _this3.shortNum = res.data.shortOrder;
        _this3.monthNum = res.data.monthOrder;
      });
    },
    goUrl: function goUrl(url) {
      url && Object(__WEBPACK_IMPORTED_MODULE_2_assets_js_router__["a" /* go */])(url, this.$router);
    }
  }
});

/***/ }),
/* 980 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_assets_js_Lib__ = __webpack_require__(3);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_assets_js_api__ = __webpack_require__(4);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2_assets_js_router__ = __webpack_require__(40);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_3_vux_src_components_view_box_index_vue__ = __webpack_require__(7);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_4_vux_src_directives_transfer_dom_index_js__ = __webpack_require__(6);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_5_vux_src_components_tabbar_tabbar_vue__ = __webpack_require__(19);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_6_vux_src_components_tabbar_tabbar_item_vue__ = __webpack_require__(25);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_7_vux_src_components_card_index_vue__ = __webpack_require__(390);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_8_vux_src_components_x_header_index_vue__ = __webpack_require__(9);











/* harmony default export */ __webpack_exports__["a"] = ({
  directives: {
    TransferDom: __WEBPACK_IMPORTED_MODULE_4_vux_src_directives_transfer_dom_index_js__["a" /* default */]
  },
  data: function data() {
    return {
      shortNum: 0,
      monthNum: 0,
      settingItems: [],
      px: 0
    };
  },

  components: {
    ViewBox: __WEBPACK_IMPORTED_MODULE_3_vux_src_components_view_box_index_vue__["a" /* default */], Tabbar: __WEBPACK_IMPORTED_MODULE_5_vux_src_components_tabbar_tabbar_vue__["a" /* default */], TabbarItem: __WEBPACK_IMPORTED_MODULE_6_vux_src_components_tabbar_tabbar_item_vue__["a" /* default */], Card: __WEBPACK_IMPORTED_MODULE_7_vux_src_components_card_index_vue__["a" /* default */], XHeader: __WEBPACK_IMPORTED_MODULE_8_vux_src_components_x_header_index_vue__["a" /* default */]
  },

  beforeCreate: function beforeCreate() {
    __WEBPACK_IMPORTED_MODULE_0_assets_js_Lib__["a" /* default */].M.setTitle('缴费记录');
  },
  beforeMount: function beforeMount() {
    this.$vux.loading.show();
    this.getNum();
    this.getSetting();
  },
  mounted: function mounted() {
    var _this = this;

    setTimeout(function () {
      _this.$vux.loading.hide();
    }, 500);
  },

  methods: {
    url: function url() {
      return __WEBPACK_IMPORTED_MODULE_0_assets_js_Lib__["a" /* default */].M.url('home');
    },
    getSetting: function getSetting() {
      var _this2 = this;

      __WEBPACK_IMPORTED_MODULE_1_assets_js_api__["a" /* default */].setting().then(function (res) {
        _this2.settingItems = res.data;
        _this2.px = _this2.settingItems.hstatus ? '46px' : '0';
      });
    },
    getNum: function getNum() {
      var _this3 = this;

      __WEBPACK_IMPORTED_MODULE_1_assets_js_api__["a" /* default */].getHistoryList().then(function (res) {
        _this3.shortNum = res.data.shortOrder;
        _this3.monthNum = res.data.monthOrder;
      });
    },
    goUrl: function goUrl(url) {
      url && Object(__WEBPACK_IMPORTED_MODULE_2_assets_js_router__["a" /* go */])(url, this.$router);
    }
  }
});

/***/ }),
/* 981 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('view-box',{ref:"viewBox",staticClass:"container",attrs:{"body-padding-top":_vm.px,"body-padding-bottom":"0px"}},[(_vm.settingItems.hstatus)?_c('x-header',{staticStyle:{"position":"absolute","width":"100%","left":"0","top":"0","z-index":"100"},attrs:{"slot":"header","title":"停车缴费记录"},slot:"header"},[_c('a',{staticStyle:{"color":"#FFFFFF"},attrs:{"slot":"right","href":_vm.url()},slot:"right"},[_vm._v("首页")])]):_vm._e(),_vm._v(" "),_c('card',{attrs:{"header":"缴费记录"}},[_c('div',{staticClass:"row",attrs:{"slot":"content"},slot:"content"},[_c('div',{staticClass:"vux-1px-r col",on:{"click":function($event){_vm.goUrl('/historylist/2')}}},[_c('span',[_vm._v(_vm._s(_vm.shortNum))]),_vm._v(" "),_c('br'),_vm._v("\n                停车缴费记录\n            ")]),_vm._v(" "),_c('div',{staticClass:"col",on:{"click":function($event){_vm.goUrl('/historylist/1')}}},[_c('span',[_vm._v(_vm._s(_vm.monthNum))]),_vm._v(" "),_c('br'),_vm._v("\n                月卡充值记录\n            ")])])]),_vm._v(" "),_c('div',{staticClass:"ln-tabbar"},[_c('tabbar',[_c('tabbar-item',{attrs:{"link":"/"}},[_c('span',{staticClass:"iconfont icon-linshitingche",attrs:{"slot":"icon"},slot:"icon"}),_vm._v(" "),_c('span',{attrs:{"slot":"label"},slot:"label"},[_vm._v("临时缴费")])]),_vm._v(" "),_c('tabbar-item',{attrs:{"link":"/monthly"}},[_c('span',{staticClass:"iconfont icon-yue",attrs:{"slot":"icon"},slot:"icon"}),_vm._v(" "),_c('span',{attrs:{"slot":"label"},slot:"label"},[_vm._v("月租缴费")])]),_vm._v(" "),_c('tabbar-item',{attrs:{"selected":"","link":"/history"}},[_c('span',{staticClass:"iconfont icon-icon_history",attrs:{"slot":"icon"},slot:"icon"}),_vm._v(" "),_c('span',{attrs:{"slot":"label"},slot:"label"},[_vm._v("缴费记录")])])],1)],1)],1)}
var staticRenderFns = []
var esExports = { render: render, staticRenderFns: staticRenderFns }
/* harmony default export */ __webpack_exports__["a"] = (esExports);

/***/ }),
/* 982 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vux_loader_src_script_loader_js_node_modules_vux_loader_src_script_loader_js_node_modules_vue_loader_lib_selector_type_script_index_0_bindApp_vue__ = __webpack_require__(985);
/* unused harmony namespace reexport */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__babel_loader_node_modules_vux_loader_src_script_loader_js_node_modules_vue_loader_lib_selector_type_script_index_0_bindApp_vue__ = __webpack_require__(986);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__node_modules_vue_loader_lib_template_compiler_index_id_data_v_24b9ac5b_hasScoped_true_buble_transforms_node_modules_vux_loader_src_before_template_compiler_loader_js_node_modules_vux_loader_src_template_loader_js_node_modules_vue_loader_lib_selector_type_template_index_0_bindApp_vue__ = __webpack_require__(987);
function injectStyle (ssrContext) {
  __webpack_require__(983)
  __webpack_require__(984)
}
var normalizeComponent = __webpack_require__(0)
/* script */


/* template */

/* template functional */
var __vue_template_functional__ = false
/* styles */
var __vue_styles__ = injectStyle
/* scopeId */
var __vue_scopeId__ = "data-v-24b9ac5b"
/* moduleIdentifier (server only) */
var __vue_module_identifier__ = null
var Component = normalizeComponent(
  __WEBPACK_IMPORTED_MODULE_1__babel_loader_node_modules_vux_loader_src_script_loader_js_node_modules_vue_loader_lib_selector_type_script_index_0_bindApp_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_2__node_modules_vue_loader_lib_template_compiler_index_id_data_v_24b9ac5b_hasScoped_true_buble_transforms_node_modules_vux_loader_src_before_template_compiler_loader_js_node_modules_vux_loader_src_template_loader_js_node_modules_vue_loader_lib_selector_type_template_index_0_bindApp_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)

/* harmony default export */ __webpack_exports__["a"] = (Component.exports);


/***/ }),
/* 983 */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),
/* 984 */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),
/* 985 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_assets_js_Lib__ = __webpack_require__(3);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_assets_js_api__ = __webpack_require__(4);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2_assets_js_router__ = __webpack_require__(40);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_3_vux_src_components_view_box_index_vue__ = __webpack_require__(7);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_4_vux_src_components_group_index_vue__ = __webpack_require__(14);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_5_vux_src_components_selector_index_vue__ = __webpack_require__(101);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_6_vux_src_components_cell_index_vue__ = __webpack_require__(16);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_7_vux_src_components_popup_index_vue__ = __webpack_require__(51);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_8_vux_src_directives_transfer_dom_index_js__ = __webpack_require__(6);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_9_vux_src_components_x_button_index_vue__ = __webpack_require__(12);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_10_vux_src_components_x_header_index_vue__ = __webpack_require__(9);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_11_vehicle_keyboard__ = __webpack_require__(473);














/* unused harmony default export */ var _unused_webpack_default_export = ({
  data: function data() {
    var _this = this;

    return {
      keyboardPopupIng: false,
      presetNumber: '',
      args: {

        presetNumber: '',
        keyboardType: 0,
        forceChangeMode: true,
        provinceName: '',
        autoComplete: true,
        showKeyTips: true,
        position: 'bottom'
      },
      callbacks: {

        onchanged: function onchanged(presetNumber, isCompleted) {

          console.log('当前车牌[变更]：' + presetNumber + ', 已完成:' + isCompleted);
        },

        onkeypressed: function onkeypressed(key) {
          console.log('当前按键：' + key.text);
        },

        oncompleted: function oncompleted(presetNumber, isAutoCompleted) {
          _this.presetNumber = presetNumber;
          _this.keyboardPopupIng = false;
          _this.numberArray = presetNumber.split('');
          console.log('当前车牌[完成]：' + presetNumber + ', 自动完成:' + isAutoCompleted);
        },

        onmessage: function onmessage(message) {
          console.info(message);
          alert(message);
        }
      },
      placeholderShow: true,
      settingItems: [],
      px: 0,
      selList: [],
      selVal: ''
    };
  },

  directives: {
    TransferDom: __WEBPACK_IMPORTED_MODULE_8_vux_src_directives_transfer_dom_index_js__["a" /* default */]
  },
  components: {
    ViewBox: __WEBPACK_IMPORTED_MODULE_3_vux_src_components_view_box_index_vue__["a" /* default */], Group: __WEBPACK_IMPORTED_MODULE_4_vux_src_components_group_index_vue__["a" /* default */], Selector: __WEBPACK_IMPORTED_MODULE_5_vux_src_components_selector_index_vue__["a" /* default */], Cell: __WEBPACK_IMPORTED_MODULE_6_vux_src_components_cell_index_vue__["a" /* default */], MixedKeyboard: __WEBPACK_IMPORTED_MODULE_11_vehicle_keyboard__["a" /* MixedKeyboard */], Popup: __WEBPACK_IMPORTED_MODULE_7_vux_src_components_popup_index_vue__["a" /* default */], XButton: __WEBPACK_IMPORTED_MODULE_9_vux_src_components_x_button_index_vue__["a" /* default */], XHeader: __WEBPACK_IMPORTED_MODULE_10_vux_src_components_x_header_index_vue__["a" /* default */]
  },

  beforeCreate: function beforeCreate() {
    __WEBPACK_IMPORTED_MODULE_0_assets_js_Lib__["a" /* default */].M.setTitle('月租车管理');
  },
  beforeMount: function beforeMount() {
    this.$vux.loading.show();
    this.getSetting();
    this.getSelList();
  },
  mounted: function mounted() {
    var _this2 = this;

    setTimeout(function () {
      _this2.$vux.loading.hide();
    }, 500);
  },


  methods: {
    getSelList: function getSelList() {
      var _this3 = this;

      __WEBPACK_IMPORTED_MODULE_1_assets_js_api__["a" /* default */].getParksList().then(function (res) {
        if (res.err_code === 0) {
          _this3.selList = _this3.normalList(res.data);
        }
      });
    },
    normalList: function normalList(list) {
      var ret = [];
      list.forEach(function (item, index) {
        ret.push({
          key: item.id,
          value: item.title
        });
      });
      return ret;
    },
    getSetting: function getSetting() {
      var _this4 = this;

      __WEBPACK_IMPORTED_MODULE_1_assets_js_api__["a" /* default */].setting().then(function (res) {
        _this4.settingItems = res.data;
        _this4.px = _this4.settingItems.hstatus ? '46px' : '0';
      });
    },
    addFun: function addFun() {
      var _this5 = this;

      if (!this.presetNumber) {
        this.$vux.toast.show({
          type: 'warn',
          text: '请输入车牌号'
        });
        return;
      }
      var data = {
        carno: this.presetNumber,
        type: 1,
        parkid: this.selVal
      };
      __WEBPACK_IMPORTED_MODULE_1_assets_js_api__["a" /* default */].addCarInfo(data).then(function (res) {
        if (res.err_code === 0) {
          if (res.data.code === 10003) {
            _this5.$vux.toast.show({
              type: 'warn',
              text: res.data.content,
              width: '12em'
            });
            return;
          }
          _this5.$vux.toast.show({
            text: '添加成功'
          });
          setTimeout(function () {
            _this5.goUrl('/monthly');
          }, 500);
        }
      });
    },
    initFun: function initFun() {
      this.placeholderShow = false;
      this.keyboardPopupIng = true;
    },
    goUrl: function goUrl(url) {
      url && Object(__WEBPACK_IMPORTED_MODULE_2_assets_js_router__["a" /* go */])(url, this.$router);
    },
    url: function url() {
      return __WEBPACK_IMPORTED_MODULE_0_assets_js_Lib__["a" /* default */].M.url('home');
    }
  },

  computed: {}
});

/***/ }),
/* 986 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_assets_js_Lib__ = __webpack_require__(3);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_assets_js_api__ = __webpack_require__(4);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2_assets_js_router__ = __webpack_require__(40);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_3_vux_src_components_view_box_index_vue__ = __webpack_require__(7);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_4_vux_src_components_group_index_vue__ = __webpack_require__(14);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_5_vux_src_components_selector_index_vue__ = __webpack_require__(101);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_6_vux_src_components_cell_index_vue__ = __webpack_require__(16);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_7_vux_src_components_popup_index_vue__ = __webpack_require__(51);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_8_vux_src_directives_transfer_dom_index_js__ = __webpack_require__(6);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_9_vux_src_components_x_button_index_vue__ = __webpack_require__(12);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_10_vux_src_components_x_header_index_vue__ = __webpack_require__(9);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_11_vehicle_keyboard__ = __webpack_require__(473);














/* harmony default export */ __webpack_exports__["a"] = ({
  data: function data() {
    var _this = this;

    return {
      keyboardPopupIng: false,
      presetNumber: '',
      args: {

        presetNumber: '',
        keyboardType: 0,
        forceChangeMode: true,
        provinceName: '',
        autoComplete: true,
        showKeyTips: true,
        position: 'bottom'
      },
      callbacks: {

        onchanged: function onchanged(presetNumber, isCompleted) {

          console.log('当前车牌[变更]：' + presetNumber + ', 已完成:' + isCompleted);
        },

        onkeypressed: function onkeypressed(key) {
          console.log('当前按键：' + key.text);
        },

        oncompleted: function oncompleted(presetNumber, isAutoCompleted) {
          _this.presetNumber = presetNumber;
          _this.keyboardPopupIng = false;
          _this.numberArray = presetNumber.split('');
          console.log('当前车牌[完成]：' + presetNumber + ', 自动完成:' + isAutoCompleted);
        },

        onmessage: function onmessage(message) {
          console.info(message);
          alert(message);
        }
      },
      placeholderShow: true,
      settingItems: [],
      px: 0,
      selList: [],
      selVal: ''
    };
  },

  directives: {
    TransferDom: __WEBPACK_IMPORTED_MODULE_8_vux_src_directives_transfer_dom_index_js__["a" /* default */]
  },
  components: {
    ViewBox: __WEBPACK_IMPORTED_MODULE_3_vux_src_components_view_box_index_vue__["a" /* default */], Group: __WEBPACK_IMPORTED_MODULE_4_vux_src_components_group_index_vue__["a" /* default */], Selector: __WEBPACK_IMPORTED_MODULE_5_vux_src_components_selector_index_vue__["a" /* default */], Cell: __WEBPACK_IMPORTED_MODULE_6_vux_src_components_cell_index_vue__["a" /* default */], MixedKeyboard: __WEBPACK_IMPORTED_MODULE_11_vehicle_keyboard__["a" /* MixedKeyboard */], Popup: __WEBPACK_IMPORTED_MODULE_7_vux_src_components_popup_index_vue__["a" /* default */], XButton: __WEBPACK_IMPORTED_MODULE_9_vux_src_components_x_button_index_vue__["a" /* default */], XHeader: __WEBPACK_IMPORTED_MODULE_10_vux_src_components_x_header_index_vue__["a" /* default */]
  },

  beforeCreate: function beforeCreate() {
    __WEBPACK_IMPORTED_MODULE_0_assets_js_Lib__["a" /* default */].M.setTitle('月租车管理');
  },
  beforeMount: function beforeMount() {
    this.$vux.loading.show();
    this.getSetting();
    this.getSelList();
  },
  mounted: function mounted() {
    var _this2 = this;

    setTimeout(function () {
      _this2.$vux.loading.hide();
    }, 500);
  },


  methods: {
    getSelList: function getSelList() {
      var _this3 = this;

      __WEBPACK_IMPORTED_MODULE_1_assets_js_api__["a" /* default */].getParksList().then(function (res) {
        if (res.err_code === 0) {
          _this3.selList = _this3.normalList(res.data);
        }
      });
    },
    normalList: function normalList(list) {
      var ret = [];
      list.forEach(function (item, index) {
        ret.push({
          key: item.id,
          value: item.title
        });
      });
      return ret;
    },
    getSetting: function getSetting() {
      var _this4 = this;

      __WEBPACK_IMPORTED_MODULE_1_assets_js_api__["a" /* default */].setting().then(function (res) {
        _this4.settingItems = res.data;
        _this4.px = _this4.settingItems.hstatus ? '46px' : '0';
      });
    },
    addFun: function addFun() {
      var _this5 = this;

      if (!this.presetNumber) {
        this.$vux.toast.show({
          type: 'warn',
          text: '请输入车牌号'
        });
        return;
      }
      var data = {
        carno: this.presetNumber,
        type: 1,
        parkid: this.selVal
      };
      __WEBPACK_IMPORTED_MODULE_1_assets_js_api__["a" /* default */].addCarInfo(data).then(function (res) {
        if (res.err_code === 0) {
          if (res.data.code === 10003) {
            _this5.$vux.toast.show({
              type: 'warn',
              text: res.data.content,
              width: '12em'
            });
            return;
          }
          _this5.$vux.toast.show({
            text: '添加成功'
          });
          setTimeout(function () {
            _this5.goUrl('/monthly');
          }, 500);
        }
      });
    },
    initFun: function initFun() {
      this.placeholderShow = false;
      this.keyboardPopupIng = true;
    },
    goUrl: function goUrl(url) {
      url && Object(__WEBPACK_IMPORTED_MODULE_2_assets_js_router__["a" /* go */])(url, this.$router);
    },
    url: function url() {
      return __WEBPACK_IMPORTED_MODULE_0_assets_js_Lib__["a" /* default */].M.url('home');
    }
  },

  computed: {}
});

/***/ }),
/* 987 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('view-box',{ref:"viewBox",staticClass:"container",attrs:{"body-padding-top":_vm.px,"body-padding-bottom":"0px"}},[(_vm.settingItems.hstatus)?_c('x-header',{staticStyle:{"position":"absolute","width":"100%","left":"0","top":"0","z-index":"100"},attrs:{"slot":"header","title":"添加月租车辆"},slot:"header"},[_c('a',{staticStyle:{"color":"#FFFFFF"},attrs:{"slot":"right","href":_vm.url()},slot:"right"},[_vm._v("首页")])]):_vm._e(),_vm._v(" "),_c('group',{attrs:{"gutter":10}},[_c('selector',{staticClass:"bind-cell",attrs:{"title":"车场","placeholder":"请选择车场","options":_vm.selList},on:{"on-change":_vm.onChange},model:{value:(_vm.selVal),callback:function ($$v) {_vm.selVal=$$v},expression:"selVal"}}),_vm._v(" "),_c('cell',{staticClass:"bind-cell",attrs:{"is-link":"","value-align":"left"},nativeOn:{"click":function($event){_vm.initFun()}}},[_c('div',{staticStyle:{"width":"105px"},attrs:{"slot":"title"},slot:"title"},[_vm._v("车牌号")]),_vm._v(" "),(_vm.placeholderShow === true)?_c('div',[_vm._v("点此输入车牌号")]):_c('div',[_vm._v(_vm._s(_vm.presetNumber))])])],1),_vm._v(" "),_c('div',{staticClass:"bnt-box"},[_c('x-button',{attrs:{"type":"primary"},nativeOn:{"click":function($event){return _vm.addFun($event)}}},[_vm._v("添加月租车辆")])],1),_vm._v(" "),_c('div',{directives:[{name:"transfer-dom",rawName:"v-transfer-dom"}]},[_c('popup',{attrs:{"height":"60%"},model:{value:(_vm.keyboardPopupIng),callback:function ($$v) {_vm.keyboardPopupIng=$$v},expression:"keyboardPopupIng"}},[_c('mixed-keyboard',{attrs:{"args":_vm.args,"callbacks":_vm.callbacks}})],1)],1)],1)}
var staticRenderFns = []
var esExports = { render: render, staticRenderFns: staticRenderFns }
/* harmony default export */ __webpack_exports__["a"] = (esExports);

/***/ }),
/* 988 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vux_loader_src_script_loader_js_node_modules_vux_loader_src_script_loader_js_node_modules_vue_loader_lib_selector_type_script_index_0_timePayApp_vue__ = __webpack_require__(990);
/* unused harmony namespace reexport */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__babel_loader_node_modules_vux_loader_src_script_loader_js_node_modules_vue_loader_lib_selector_type_script_index_0_timePayApp_vue__ = __webpack_require__(991);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__node_modules_vue_loader_lib_template_compiler_index_id_data_v_2c1b6dda_hasScoped_true_buble_transforms_node_modules_vux_loader_src_before_template_compiler_loader_js_node_modules_vux_loader_src_template_loader_js_node_modules_vue_loader_lib_selector_type_template_index_0_timePayApp_vue__ = __webpack_require__(992);
function injectStyle (ssrContext) {
  __webpack_require__(989)
}
var normalizeComponent = __webpack_require__(0)
/* script */


/* template */

/* template functional */
var __vue_template_functional__ = false
/* styles */
var __vue_styles__ = injectStyle
/* scopeId */
var __vue_scopeId__ = "data-v-2c1b6dda"
/* moduleIdentifier (server only) */
var __vue_module_identifier__ = null
var Component = normalizeComponent(
  __WEBPACK_IMPORTED_MODULE_1__babel_loader_node_modules_vux_loader_src_script_loader_js_node_modules_vue_loader_lib_selector_type_script_index_0_timePayApp_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_2__node_modules_vue_loader_lib_template_compiler_index_id_data_v_2c1b6dda_hasScoped_true_buble_transforms_node_modules_vux_loader_src_before_template_compiler_loader_js_node_modules_vux_loader_src_template_loader_js_node_modules_vue_loader_lib_selector_type_template_index_0_timePayApp_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)

/* harmony default export */ __webpack_exports__["a"] = (Component.exports);


/***/ }),
/* 989 */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),
/* 990 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_assets_js_Lib__ = __webpack_require__(3);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_assets_js_api__ = __webpack_require__(4);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2_assets_js_router__ = __webpack_require__(40);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_3_vux_src_components_view_box_index_vue__ = __webpack_require__(7);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_4_vux_src_components_x_button_index_vue__ = __webpack_require__(12);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_5_vux_src_components_x_header_index_vue__ = __webpack_require__(9);








/* unused harmony default export */ var _unused_webpack_default_export = ({
  data: function data() {
    return {
      list: [],
      duration: '',
      settingItems: [],
      px: 0
    };
  },

  components: {
    ViewBox: __WEBPACK_IMPORTED_MODULE_3_vux_src_components_view_box_index_vue__["a" /* default */], XButton: __WEBPACK_IMPORTED_MODULE_4_vux_src_components_x_button_index_vue__["a" /* default */], XHeader: __WEBPACK_IMPORTED_MODULE_5_vux_src_components_x_header_index_vue__["a" /* default */]
  },

  beforeCreate: function beforeCreate() {
    __WEBPACK_IMPORTED_MODULE_0_assets_js_Lib__["a" /* default */].M.setTitle('临时停车信息');
    this.carNum = this.$route.params.carno;
  },
  beforeMount: function beforeMount() {
    this.$vux.loading.show();
    this.getShortPayInfo(this.carNum);
    this.getSetting();
  },
  mounted: function mounted() {
    var _this = this;

    setTimeout(function () {
      _this.$vux.loading.hide();
    }, 500);
  },


  methods: {
    url: function url() {
      return __WEBPACK_IMPORTED_MODULE_0_assets_js_Lib__["a" /* default */].M.url('home');
    },
    getSetting: function getSetting() {
      var _this2 = this;

      __WEBPACK_IMPORTED_MODULE_1_assets_js_api__["a" /* default */].setting().then(function (res) {
        _this2.settingItems = res.data;
        _this2.px = _this2.settingItems.hstatus ? '46px' : '0';
      });
    },
    payFun: function payFun() {
      var data = {
        category: 2,
        orderid: this.list.orderid,
        carno: this.list.carno,
        price: this.list.price
      };
      __WEBPACK_IMPORTED_MODULE_1_assets_js_api__["a" /* default */].addOrderInfo(data).then(function (res) {
        if (res.err_code === 0) {
          var url = res.data.url;
          window.location.href = url;
        }
      });
    },
    getBuration: function getBuration(time) {
      var nowTime = new Date().valueOf();
      var startTime = new Date(time).valueOf();
      var hour = Math.floor((nowTime - startTime) / 3600000);
      var min = Math.floor((nowTime - startTime) % 3600000 / 60000);
      this.duration = hour + '时' + min + '分';
    },
    getShortPayInfo: function getShortPayInfo(defaultTit) {
      var _this3 = this;

      var data = {
        carno: defaultTit
      };
      __WEBPACK_IMPORTED_MODULE_1_assets_js_api__["a" /* default */].getShortPayInfo(data).then(function (res) {
        if (res.err_code === 0) {
          _this3.list = res.data;
        }
        if (_this3.list.intoTime) {
          _this3.getBuration(_this3.list.intoTime);
        }
      });
    },
    goUrl: function goUrl(url) {
      url && Object(__WEBPACK_IMPORTED_MODULE_2_assets_js_router__["a" /* go */])(url, this.$router);
    }
  },

  computed: {}
});

/***/ }),
/* 991 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_assets_js_Lib__ = __webpack_require__(3);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_assets_js_api__ = __webpack_require__(4);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2_assets_js_router__ = __webpack_require__(40);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_3_vux_src_components_view_box_index_vue__ = __webpack_require__(7);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_4_vux_src_components_x_button_index_vue__ = __webpack_require__(12);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_5_vux_src_components_x_header_index_vue__ = __webpack_require__(9);








/* harmony default export */ __webpack_exports__["a"] = ({
  data: function data() {
    return {
      list: [],
      duration: '',
      settingItems: [],
      px: 0
    };
  },

  components: {
    ViewBox: __WEBPACK_IMPORTED_MODULE_3_vux_src_components_view_box_index_vue__["a" /* default */], XButton: __WEBPACK_IMPORTED_MODULE_4_vux_src_components_x_button_index_vue__["a" /* default */], XHeader: __WEBPACK_IMPORTED_MODULE_5_vux_src_components_x_header_index_vue__["a" /* default */]
  },

  beforeCreate: function beforeCreate() {
    __WEBPACK_IMPORTED_MODULE_0_assets_js_Lib__["a" /* default */].M.setTitle('临时停车信息');
    this.carNum = this.$route.params.carno;
  },
  beforeMount: function beforeMount() {
    this.$vux.loading.show();
    this.getShortPayInfo(this.carNum);
    this.getSetting();
  },
  mounted: function mounted() {
    var _this = this;

    setTimeout(function () {
      _this.$vux.loading.hide();
    }, 500);
  },


  methods: {
    url: function url() {
      return __WEBPACK_IMPORTED_MODULE_0_assets_js_Lib__["a" /* default */].M.url('home');
    },
    getSetting: function getSetting() {
      var _this2 = this;

      __WEBPACK_IMPORTED_MODULE_1_assets_js_api__["a" /* default */].setting().then(function (res) {
        _this2.settingItems = res.data;
        _this2.px = _this2.settingItems.hstatus ? '46px' : '0';
      });
    },
    payFun: function payFun() {
      var data = {
        category: 2,
        orderid: this.list.orderid,
        carno: this.list.carno,
        price: this.list.price
      };
      __WEBPACK_IMPORTED_MODULE_1_assets_js_api__["a" /* default */].addOrderInfo(data).then(function (res) {
        if (res.err_code === 0) {
          var url = res.data.url;
          window.location.href = url;
        }
      });
    },
    getBuration: function getBuration(time) {
      var nowTime = new Date().valueOf();
      var startTime = new Date(time).valueOf();
      var hour = Math.floor((nowTime - startTime) / 3600000);
      var min = Math.floor((nowTime - startTime) % 3600000 / 60000);
      this.duration = hour + '时' + min + '分';
    },
    getShortPayInfo: function getShortPayInfo(defaultTit) {
      var _this3 = this;

      var data = {
        carno: defaultTit
      };
      __WEBPACK_IMPORTED_MODULE_1_assets_js_api__["a" /* default */].getShortPayInfo(data).then(function (res) {
        if (res.err_code === 0) {
          _this3.list = res.data;
        }
        if (_this3.list.intoTime) {
          _this3.getBuration(_this3.list.intoTime);
        }
      });
    },
    goUrl: function goUrl(url) {
      url && Object(__WEBPACK_IMPORTED_MODULE_2_assets_js_router__["a" /* go */])(url, this.$router);
    }
  },

  computed: {}
});

/***/ }),
/* 992 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('view-box',{ref:"viewBox",staticClass:"container",attrs:{"body-padding-top":_vm.px,"body-padding-bottom":"0px"}},[(_vm.settingItems.hstatus)?_c('x-header',{staticStyle:{"position":"absolute","width":"100%","left":"0","top":"0","z-index":"100"},attrs:{"slot":"header","title":"临时停车信息"},slot:"header"},[_c('a',{staticStyle:{"color":"#FFFFFF"},attrs:{"slot":"right","href":_vm.url()},slot:"right"},[_vm._v("首页")])]):_vm._e(),_vm._v(" "),_c('div',{staticClass:"ln-confirm"},[_c('div',{staticClass:"confirm-hd vux-1px-b"},[_c('h3',{staticClass:"title"},[_vm._v("结算")]),_vm._v(" "),_c('div',{staticClass:"car-name"},[_vm._v(_vm._s(_vm.list.carno))]),_vm._v(" "),_c('div',{staticClass:"car-time"},[_vm._v("入场时间："+_vm._s(_vm.list.intoTime))]),_vm._v(" "),_c('div',{staticClass:"car-duration"},[_vm._v("停车 "+_vm._s(this.duration))])]),_vm._v(" "),_c('div',{staticClass:"confirm-bd"},[_c('p',{staticClass:"title"},[_vm._v("应付停车费")]),_vm._v(" "),_c('h1',{staticClass:"car-price"},[_c('span',[_vm._v("￥")]),_vm._v(_vm._s(_vm.list.price))]),_vm._v(" "),_c('div',{staticClass:"btn-box"},[_c('x-button',{staticClass:"balance-btn",attrs:{"type":"primary"},nativeOn:{"click":function($event){return _vm.payFun($event)}}},[_c('span',{staticClass:"iconfont icon-yuezhifu-copy"}),_vm._v("停车费支付")])],1),_vm._v(" "),_c('p',{staticClass:"confirm-bd-bottom",on:{"click":function($event){_vm.goUrl('/history')}}},[_vm._v("停车记录")])])])],1)}
var staticRenderFns = []
var esExports = { render: render, staticRenderFns: staticRenderFns }
/* harmony default export */ __webpack_exports__["a"] = (esExports);

/***/ }),
/* 993 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vux_loader_src_script_loader_js_node_modules_vux_loader_src_script_loader_js_node_modules_vue_loader_lib_selector_type_script_index_0_monthPayApp_vue__ = __webpack_require__(995);
/* unused harmony namespace reexport */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__babel_loader_node_modules_vux_loader_src_script_loader_js_node_modules_vue_loader_lib_selector_type_script_index_0_monthPayApp_vue__ = __webpack_require__(996);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__node_modules_vue_loader_lib_template_compiler_index_id_data_v_2f4757f4_hasScoped_true_buble_transforms_node_modules_vux_loader_src_before_template_compiler_loader_js_node_modules_vux_loader_src_template_loader_js_node_modules_vue_loader_lib_selector_type_template_index_0_monthPayApp_vue__ = __webpack_require__(997);
function injectStyle (ssrContext) {
  __webpack_require__(994)
}
var normalizeComponent = __webpack_require__(0)
/* script */


/* template */

/* template functional */
var __vue_template_functional__ = false
/* styles */
var __vue_styles__ = injectStyle
/* scopeId */
var __vue_scopeId__ = "data-v-2f4757f4"
/* moduleIdentifier (server only) */
var __vue_module_identifier__ = null
var Component = normalizeComponent(
  __WEBPACK_IMPORTED_MODULE_1__babel_loader_node_modules_vux_loader_src_script_loader_js_node_modules_vue_loader_lib_selector_type_script_index_0_monthPayApp_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_2__node_modules_vue_loader_lib_template_compiler_index_id_data_v_2f4757f4_hasScoped_true_buble_transforms_node_modules_vux_loader_src_before_template_compiler_loader_js_node_modules_vux_loader_src_template_loader_js_node_modules_vue_loader_lib_selector_type_template_index_0_monthPayApp_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)

/* harmony default export */ __webpack_exports__["a"] = (Component.exports);


/***/ }),
/* 994 */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),
/* 995 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_assets_js_Lib__ = __webpack_require__(3);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_assets_js_api__ = __webpack_require__(4);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2_assets_js_router__ = __webpack_require__(40);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_3_vux_src_components_view_box_index_vue__ = __webpack_require__(7);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_4_vux_src_components_tabbar_tabbar_vue__ = __webpack_require__(19);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_5_vux_src_components_checker_checker_vue__ = __webpack_require__(340);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_6_vux_src_components_checker_checker_item_vue__ = __webpack_require__(341);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_7_vux_src_components_x_header_index_vue__ = __webpack_require__(9);










/* unused harmony default export */ var _unused_webpack_default_export = ({
  data: function data() {
    return {
      total: 0.00,
      checkerModel: '',
      items: [],
      parkName: '',
      months: '',
      settingItems: [],
      px: 0
    };
  },

  components: {
    ViewBox: __WEBPACK_IMPORTED_MODULE_3_vux_src_components_view_box_index_vue__["a" /* default */], Tabbar: __WEBPACK_IMPORTED_MODULE_4_vux_src_components_tabbar_tabbar_vue__["a" /* default */], Checker: __WEBPACK_IMPORTED_MODULE_5_vux_src_components_checker_checker_vue__["a" /* default */], CheckerItem: __WEBPACK_IMPORTED_MODULE_6_vux_src_components_checker_checker_item_vue__["a" /* default */], XHeader: __WEBPACK_IMPORTED_MODULE_7_vux_src_components_x_header_index_vue__["a" /* default */]
  },

  beforeCreate: function beforeCreate() {
    __WEBPACK_IMPORTED_MODULE_0_assets_js_Lib__["a" /* default */].M.setTitle('月卡充值');
    this.parkId = this.$route.params.pid;
    this.carName = this.$route.params.carno;
    console.log(this.parkId, this.carName);
  },
  beforeMount: function beforeMount() {
    this.$vux.loading.show();
    this.getMonthCarsInfo(this.parkId, this.carName);
    this.getSetting();
  },
  mounted: function mounted() {
    var _this = this;

    setTimeout(function () {
      _this.$vux.loading.hide();
    }, 500);
  },


  methods: {
    url: function url() {
      return __WEBPACK_IMPORTED_MODULE_0_assets_js_Lib__["a" /* default */].M.url('home');
    },
    getSetting: function getSetting() {
      var _this2 = this;

      __WEBPACK_IMPORTED_MODULE_1_assets_js_api__["a" /* default */].setting().then(function (res) {
        _this2.settingItems = res.data;
        _this2.px = _this2.settingItems.hstatus ? '46px' : '0';
      });
    },
    payFun: function payFun() {
      if (this.months === null || this.months === undefined || this.months === '') {
        this.$vux.toast.text('请选择优惠套餐');
        return;
      }
      var data = {
        category: 1,
        carno: this.carName,
        price: this.total,
        num: this.months,
        parkid: this.parkId
      };
      __WEBPACK_IMPORTED_MODULE_1_assets_js_api__["a" /* default */].addOrderInfo(data).then(function (res) {
        if (res.err_code === 0) {
          var url = res.data.url;
          window.location.href = url;
        }
      });
    },
    onItemClick: function onItemClick(item) {
      this.total = item.value;
      this.months = item.key;
    },
    getMonthCarsInfo: function getMonthCarsInfo(parkId, carName) {
      var _this3 = this;

      var data = {
        parkid: parkId,
        carno: carName
      };
      __WEBPACK_IMPORTED_MODULE_1_assets_js_api__["a" /* default */].getMonthCarsInfo(data).then(function (res) {
        if (res.err_code === 0) {
          _this3.items = _this3.normalArr(res.data.monthPrice);
          _this3.parkName = res.data.title;
        }
      });
    },
    normalArr: function normalArr(price) {
      var ret = [];
      ret.push({
        key: '1',
        value: price
      }, {
        key: '3',
        value: 3 * price
      }, {
        key: '12',
        value: 12 * price
      });
      return ret;
    },
    goUrl: function goUrl(url) {
      url && Object(__WEBPACK_IMPORTED_MODULE_2_assets_js_router__["a" /* go */])(url, this.$router);
    }
  },

  computed: {},
  filters: {
    keepTwoNum: function keepTwoNum(value) {
      value = Number(value);
      return value.toFixed(2);
    }
  }
});

/***/ }),
/* 996 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_assets_js_Lib__ = __webpack_require__(3);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_assets_js_api__ = __webpack_require__(4);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2_assets_js_router__ = __webpack_require__(40);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_3_vux_src_components_view_box_index_vue__ = __webpack_require__(7);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_4_vux_src_components_tabbar_tabbar_vue__ = __webpack_require__(19);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_5_vux_src_components_checker_checker_vue__ = __webpack_require__(340);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_6_vux_src_components_checker_checker_item_vue__ = __webpack_require__(341);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_7_vux_src_components_x_header_index_vue__ = __webpack_require__(9);










/* harmony default export */ __webpack_exports__["a"] = ({
  data: function data() {
    return {
      total: 0.00,
      checkerModel: '',
      items: [],
      parkName: '',
      months: '',
      settingItems: [],
      px: 0
    };
  },

  components: {
    ViewBox: __WEBPACK_IMPORTED_MODULE_3_vux_src_components_view_box_index_vue__["a" /* default */], Tabbar: __WEBPACK_IMPORTED_MODULE_4_vux_src_components_tabbar_tabbar_vue__["a" /* default */], Checker: __WEBPACK_IMPORTED_MODULE_5_vux_src_components_checker_checker_vue__["a" /* default */], CheckerItem: __WEBPACK_IMPORTED_MODULE_6_vux_src_components_checker_checker_item_vue__["a" /* default */], XHeader: __WEBPACK_IMPORTED_MODULE_7_vux_src_components_x_header_index_vue__["a" /* default */]
  },

  beforeCreate: function beforeCreate() {
    __WEBPACK_IMPORTED_MODULE_0_assets_js_Lib__["a" /* default */].M.setTitle('月卡充值');
    this.parkId = this.$route.params.pid;
    this.carName = this.$route.params.carno;
    console.log(this.parkId, this.carName);
  },
  beforeMount: function beforeMount() {
    this.$vux.loading.show();
    this.getMonthCarsInfo(this.parkId, this.carName);
    this.getSetting();
  },
  mounted: function mounted() {
    var _this = this;

    setTimeout(function () {
      _this.$vux.loading.hide();
    }, 500);
  },


  methods: {
    url: function url() {
      return __WEBPACK_IMPORTED_MODULE_0_assets_js_Lib__["a" /* default */].M.url('home');
    },
    getSetting: function getSetting() {
      var _this2 = this;

      __WEBPACK_IMPORTED_MODULE_1_assets_js_api__["a" /* default */].setting().then(function (res) {
        _this2.settingItems = res.data;
        _this2.px = _this2.settingItems.hstatus ? '46px' : '0';
      });
    },
    payFun: function payFun() {
      if (this.months === null || this.months === undefined || this.months === '') {
        this.$vux.toast.text('请选择优惠套餐');
        return;
      }
      var data = {
        category: 1,
        carno: this.carName,
        price: this.total,
        num: this.months,
        parkid: this.parkId
      };
      __WEBPACK_IMPORTED_MODULE_1_assets_js_api__["a" /* default */].addOrderInfo(data).then(function (res) {
        if (res.err_code === 0) {
          var url = res.data.url;
          window.location.href = url;
        }
      });
    },
    onItemClick: function onItemClick(item) {
      this.total = item.value;
      this.months = item.key;
    },
    getMonthCarsInfo: function getMonthCarsInfo(parkId, carName) {
      var _this3 = this;

      var data = {
        parkid: parkId,
        carno: carName
      };
      __WEBPACK_IMPORTED_MODULE_1_assets_js_api__["a" /* default */].getMonthCarsInfo(data).then(function (res) {
        if (res.err_code === 0) {
          _this3.items = _this3.normalArr(res.data.monthPrice);
          _this3.parkName = res.data.title;
        }
      });
    },
    normalArr: function normalArr(price) {
      var ret = [];
      ret.push({
        key: '1',
        value: price
      }, {
        key: '3',
        value: 3 * price
      }, {
        key: '12',
        value: 12 * price
      });
      return ret;
    },
    goUrl: function goUrl(url) {
      url && Object(__WEBPACK_IMPORTED_MODULE_2_assets_js_router__["a" /* go */])(url, this.$router);
    }
  },

  computed: {},
  filters: {
    keepTwoNum: function keepTwoNum(value) {
      value = Number(value);
      return value.toFixed(2);
    }
  }
});

/***/ }),
/* 997 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('view-box',{ref:"viewBox",staticClass:"container",attrs:{"body-padding-top":_vm.px,"body-padding-bottom":"0px"}},[(_vm.settingItems.hstatus)?_c('x-header',{staticStyle:{"position":"absolute","width":"100%","left":"0","top":"0","z-index":"100"},attrs:{"slot":"header","title":"月卡充值"},slot:"header"},[_c('a',{staticStyle:{"color":"#FFFFFF"},attrs:{"slot":"right","href":_vm.url()},slot:"right"},[_vm._v("首页")])]):_vm._e(),_vm._v(" "),_c('div',{staticClass:"ln-bd"},[_c('div',{staticClass:"car-cell"},[_vm._v("\n            "+_vm._s(_vm.carName)+" "),_c('span',[_vm._v(_vm._s(_vm.parkName))])]),_vm._v(" "),_c('h1',{staticClass:"title"},[_vm._v("选择优惠套餐")]),_vm._v(" "),_c('checker',{attrs:{"default-item-class":"checker-item","selected-item-class":"checker-item-selected"},model:{value:(_vm.checkerModel),callback:function ($$v) {_vm.checkerModel=$$v},expression:"checkerModel"}},_vm._l((_vm.items),function(item,index){return _c('checker-item',{key:index,attrs:{"value":item},on:{"on-item-click":_vm.onItemClick}},[_vm._v(_vm._s(item.key)+"个月"),_c('span',[_vm._v("￥"+_vm._s(_vm._f("keepTwoNum")(item.value)))])])}))],1),_vm._v(" "),_c('tabbar',{staticClass:"pay-tabbar"},[_c('div',{staticClass:"row"},[_c('div',{staticClass:"col"},[_vm._v("总价:"),_c('span',[_vm._v("￥"+_vm._s(_vm.total))])]),_vm._v(" "),_c('div',{staticClass:"pay-btn",on:{"click":function($event){_vm.payFun()}}},[_vm._v("去支付")])])])],1)}
var staticRenderFns = []
var esExports = { render: render, staticRenderFns: staticRenderFns }
/* harmony default export */ __webpack_exports__["a"] = (esExports);

/***/ }),
/* 998 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__babel_loader_node_modules_vux_loader_src_script_loader_js_node_modules_vux_loader_src_script_loader_js_node_modules_vue_loader_lib_selector_type_script_index_0_historyListApp_vue__ = __webpack_require__(1000);
/* unused harmony namespace reexport */
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__babel_loader_node_modules_vux_loader_src_script_loader_js_node_modules_vue_loader_lib_selector_type_script_index_0_historyListApp_vue__ = __webpack_require__(1001);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__node_modules_vue_loader_lib_template_compiler_index_id_data_v_1cd92499_hasScoped_true_buble_transforms_node_modules_vux_loader_src_before_template_compiler_loader_js_node_modules_vux_loader_src_template_loader_js_node_modules_vue_loader_lib_selector_type_template_index_0_historyListApp_vue__ = __webpack_require__(1002);
function injectStyle (ssrContext) {
  __webpack_require__(999)
}
var normalizeComponent = __webpack_require__(0)
/* script */


/* template */

/* template functional */
var __vue_template_functional__ = false
/* styles */
var __vue_styles__ = injectStyle
/* scopeId */
var __vue_scopeId__ = "data-v-1cd92499"
/* moduleIdentifier (server only) */
var __vue_module_identifier__ = null
var Component = normalizeComponent(
  __WEBPACK_IMPORTED_MODULE_1__babel_loader_node_modules_vux_loader_src_script_loader_js_node_modules_vue_loader_lib_selector_type_script_index_0_historyListApp_vue__["a" /* default */],
  __WEBPACK_IMPORTED_MODULE_2__node_modules_vue_loader_lib_template_compiler_index_id_data_v_1cd92499_hasScoped_true_buble_transforms_node_modules_vux_loader_src_before_template_compiler_loader_js_node_modules_vux_loader_src_template_loader_js_node_modules_vue_loader_lib_selector_type_template_index_0_historyListApp_vue__["a" /* default */],
  __vue_template_functional__,
  __vue_styles__,
  __vue_scopeId__,
  __vue_module_identifier__
)

/* harmony default export */ __webpack_exports__["a"] = (Component.exports);


/***/ }),
/* 999 */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),
/* 1000 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_assets_js_Lib__ = __webpack_require__(3);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_assets_js_api__ = __webpack_require__(4);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2_assets_js_router__ = __webpack_require__(40);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_3_vue_infinite_loading__ = __webpack_require__(29);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_3_vue_infinite_loading___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_3_vue_infinite_loading__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_4_components__ = __webpack_require__(34);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_5_vux_src_components_view_box_index_vue__ = __webpack_require__(7);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_6_vux_src_components_tab_tab_vue__ = __webpack_require__(21);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_7_vux_src_components_tab_tab_item_vue__ = __webpack_require__(22);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_8_vux_src_components_sticky_index_vue__ = __webpack_require__(35);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_9_vux_src_components_x_header_index_vue__ = __webpack_require__(9);












/* unused harmony default export */ var _unused_webpack_default_export = ({
  data: function data() {
    return {
      historyTit: '',
      pageIng: false,
      currentLoading: false,
      pIndex: 1,
      list: [],
      tabIndex: 0,
      infiniteId: +new Date(),
      settingItems: [],
      px: 0
    };
  },

  components: {
    ViewBox: __WEBPACK_IMPORTED_MODULE_5_vux_src_components_view_box_index_vue__["a" /* default */], InfiniteLoading: __WEBPACK_IMPORTED_MODULE_3_vue_infinite_loading___default.a, YmAbnor: __WEBPACK_IMPORTED_MODULE_4_components__["i" /* YmAbnor */], Tab: __WEBPACK_IMPORTED_MODULE_6_vux_src_components_tab_tab_vue__["a" /* default */], TabItem: __WEBPACK_IMPORTED_MODULE_7_vux_src_components_tab_tab_item_vue__["a" /* default */], Sticky: __WEBPACK_IMPORTED_MODULE_8_vux_src_components_sticky_index_vue__["a" /* default */], XHeader: __WEBPACK_IMPORTED_MODULE_9_vux_src_components_x_header_index_vue__["a" /* default */]
  },

  beforeCreate: function beforeCreate() {},
  beforeMount: function beforeMount() {
    this.$vux.loading.show();
    this.cate = this.$route.params.category;
    this.cate === 1 ? this.historyTit = '月卡充值记录' : this.historyTit = '停车缴费记录';
    __WEBPACK_IMPORTED_MODULE_0_assets_js_Lib__["a" /* default */].M.setTitle(this.historyTit);
    var tabs = [{ id: 1, name: '7天' }, { id: 2, name: '1个月' }, { id: 3, name: '3个月' }];
    this.tabs = tabs;
    this.getSetting();
  },
  mounted: function mounted() {
    var _this = this;

    setTimeout(function () {
      _this.pageIng = true;
      _this.$vux.loading.hide();
    }, 500);
  },


  methods: {
    url: function url() {
      return __WEBPACK_IMPORTED_MODULE_0_assets_js_Lib__["a" /* default */].M.url('home');
    },
    getSetting: function getSetting() {
      var _this2 = this;

      __WEBPACK_IMPORTED_MODULE_1_assets_js_api__["a" /* default */].setting().then(function (res) {
        _this2.settingItems = res.data;
        _this2.px = _this2.settingItems.hstatus ? '46px' : '0';
      });
    },
    onInfinite: function onInfinite($state) {
      var _this3 = this;

      if (this.currentLoading) {
        return false;
      } else {
        this.currentLoading = true;
      }
      var data = {
        category: this.cate,
        page: this.pIndex,
        type: this.tabIndex
      };
      __WEBPACK_IMPORTED_MODULE_1_assets_js_api__["a" /* default */].getHistoryList(data).then(function (_ref) {
        var data = _ref.data;

        _this3.pIndex++;
        _this3.list = _this3.list.concat(data.list);
        console.log(_this3.list);
        _this3.currentLoading = false;
        if (data.list.length) {
          $state.loaded();
        } else {
          $state.complete();
        }
      });
    },
    goUrl: function goUrl(url) {
      url && Object(__WEBPACK_IMPORTED_MODULE_2_assets_js_router__["a" /* go */])(url, this.$router);
    }
  },

  computed: {},
  watch: {
    tabIndex: function tabIndex() {
      this.list = [];
      this.pIndex = 1;
      this.infiniteId += 1;
    }
  }
});

/***/ }),
/* 1001 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_assets_js_Lib__ = __webpack_require__(3);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_assets_js_api__ = __webpack_require__(4);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2_assets_js_router__ = __webpack_require__(40);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_3_vue_infinite_loading__ = __webpack_require__(29);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_3_vue_infinite_loading___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_3_vue_infinite_loading__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_4_components__ = __webpack_require__(34);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_5_vux_src_components_view_box_index_vue__ = __webpack_require__(7);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_6_vux_src_components_tab_tab_vue__ = __webpack_require__(21);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_7_vux_src_components_tab_tab_item_vue__ = __webpack_require__(22);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_8_vux_src_components_sticky_index_vue__ = __webpack_require__(35);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_9_vux_src_components_x_header_index_vue__ = __webpack_require__(9);












/* harmony default export */ __webpack_exports__["a"] = ({
  data: function data() {
    return {
      historyTit: '',
      pageIng: false,
      currentLoading: false,
      pIndex: 1,
      list: [],
      tabIndex: 0,
      infiniteId: +new Date(),
      settingItems: [],
      px: 0
    };
  },

  components: {
    ViewBox: __WEBPACK_IMPORTED_MODULE_5_vux_src_components_view_box_index_vue__["a" /* default */], InfiniteLoading: __WEBPACK_IMPORTED_MODULE_3_vue_infinite_loading___default.a, YmAbnor: __WEBPACK_IMPORTED_MODULE_4_components__["i" /* YmAbnor */], Tab: __WEBPACK_IMPORTED_MODULE_6_vux_src_components_tab_tab_vue__["a" /* default */], TabItem: __WEBPACK_IMPORTED_MODULE_7_vux_src_components_tab_tab_item_vue__["a" /* default */], Sticky: __WEBPACK_IMPORTED_MODULE_8_vux_src_components_sticky_index_vue__["a" /* default */], XHeader: __WEBPACK_IMPORTED_MODULE_9_vux_src_components_x_header_index_vue__["a" /* default */]
  },

  beforeCreate: function beforeCreate() {},
  beforeMount: function beforeMount() {
    this.$vux.loading.show();
    this.cate = this.$route.params.category;
    this.cate === 1 ? this.historyTit = '月卡充值记录' : this.historyTit = '停车缴费记录';
    __WEBPACK_IMPORTED_MODULE_0_assets_js_Lib__["a" /* default */].M.setTitle(this.historyTit);
    var tabs = [{ id: 1, name: '7天' }, { id: 2, name: '1个月' }, { id: 3, name: '3个月' }];
    this.tabs = tabs;
    this.getSetting();
  },
  mounted: function mounted() {
    var _this = this;

    setTimeout(function () {
      _this.pageIng = true;
      _this.$vux.loading.hide();
    }, 500);
  },


  methods: {
    url: function url() {
      return __WEBPACK_IMPORTED_MODULE_0_assets_js_Lib__["a" /* default */].M.url('home');
    },
    getSetting: function getSetting() {
      var _this2 = this;

      __WEBPACK_IMPORTED_MODULE_1_assets_js_api__["a" /* default */].setting().then(function (res) {
        _this2.settingItems = res.data;
        _this2.px = _this2.settingItems.hstatus ? '46px' : '0';
      });
    },
    onInfinite: function onInfinite($state) {
      var _this3 = this;

      if (this.currentLoading) {
        return false;
      } else {
        this.currentLoading = true;
      }
      var data = {
        category: this.cate,
        page: this.pIndex,
        type: this.tabIndex
      };
      __WEBPACK_IMPORTED_MODULE_1_assets_js_api__["a" /* default */].getHistoryList(data).then(function (_ref) {
        var data = _ref.data;

        _this3.pIndex++;
        _this3.list = _this3.list.concat(data.list);
        console.log(_this3.list);
        _this3.currentLoading = false;
        if (data.list.length) {
          $state.loaded();
        } else {
          $state.complete();
        }
      });
    },
    goUrl: function goUrl(url) {
      url && Object(__WEBPACK_IMPORTED_MODULE_2_assets_js_router__["a" /* go */])(url, this.$router);
    }
  },

  computed: {},
  watch: {
    tabIndex: function tabIndex() {
      this.list = [];
      this.pIndex = 1;
      this.infiniteId += 1;
    }
  }
});

/***/ }),
/* 1002 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
var render = function () {var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;return _c('view-box',{ref:"viewBox",staticClass:"container",attrs:{"body-padding-top":"0px","body-padding-bottom":"53px"}},[(_vm.settingItems.hstatus)?_c('x-header',{staticStyle:{"position":"absolute","width":"100%","left":"0","top":"0","z-index":"100"},attrs:{"slot":"header","title":"停车缴费列表"},slot:"header"},[_c('a',{staticStyle:{"color":"#FFFFFF"},attrs:{"slot":"right","href":_vm.url()},slot:"right"},[_vm._v("首页")])]):_vm._e(),_vm._v(" "),(_vm.cate==1)?[_c('div',{staticClass:"mcr_title"},[_vm._v("近一年订单")]),_vm._v(" "),_vm._l((_vm.list),function(item,index){return _c('div',{staticClass:"item_box"},[_c('div',{staticClass:"item sl-li ui-border-b"},[_c('div',{staticClass:"sl-content"},[_c('div',{staticClass:"header flex_layout_center"},[_c('div',{staticClass:"label"},[_vm._v("订单号")]),_vm._v(" "),_c('div',{staticClass:"label_info flex_layout_full"},[_vm._v(_vm._s(item.ordersn))]),_vm._v(" "),_c('div',{staticClass:"label_other money"},[_vm._v("￥"+_vm._s(item.price))])]),_vm._v(" "),_c('div',{staticClass:"content"},[_c('div',{staticClass:"row flex_layout_center"},[_c('div',{staticClass:"label"},[_vm._v("车牌号")]),_vm._v(" "),_c('div',{staticClass:"label_info flex_layout_full"},[_vm._v(_vm._s(item.carno))])]),_vm._v(" "),_c('div',{staticClass:"row flex_layout_center"},[_c('div',{staticClass:"label"},[_vm._v("支付时间")]),_vm._v(" "),_c('div',{staticClass:"label_info flex_layout_full"},[_vm._v(_vm._s(item.paytime))])]),_vm._v(" "),_c('div',{staticClass:"row flex_layout_center"},[_c('div',{staticClass:"label"},[_vm._v("停车场")]),_vm._v(" "),_c('div',{staticClass:"label_info flex_layout_full"},[_vm._v(_vm._s(item.title))])])])])])])}),_vm._v(" "),(_vm.pageIng)?_c('infinite-loading',{ref:"infiniteLoading",attrs:{"spinner":"waveDots"},on:{"infinite":_vm.onInfinite}},[_c('span',{attrs:{"slot":"no-more"},slot:"no-more"},[_vm._v("已经加载完啦!")]),_vm._v(" "),_c('ym-abnor',{staticStyle:{"margin-top":"-58px"},attrs:{"slot":"no-results","type":"DATA"},slot:"no-results"})],1):_vm._e()]:(_vm.cate==2)?[_c('sticky',{ref:"sticky",attrs:{"scroll-box":"vux_view_box_body","check-sticky-support":false}},[_c('tab',{staticClass:"ln-order-tab",attrs:{"line-width":1,"bar-active-color":"#39f","active-color":"#39f","custom-bar-width":"36px"},model:{value:(_vm.tabIndex),callback:function ($$v) {_vm.tabIndex=$$v},expression:"tabIndex"}},_vm._l((_vm.tabs),function(item,index){return _c('tab-item',{key:index,attrs:{"selected":index === _vm.tabIndex},on:{"click":function($event){index === _vm.tabIndex}}},[_vm._v(_vm._s(item.name))])}))],1),_vm._v(" "),_vm._l((_vm.list),function(item,index){return _c('div',{staticClass:"item_box"},[_c('div',{staticClass:"item sl-li ui-border-b"},[_c('div',{staticClass:"sl-content"},[_c('div',{staticClass:"header flex_layout_center"},[_c('div',{staticClass:"label"},[_vm._v("订单号")]),_vm._v(" "),_c('div',{staticClass:"label_info flex_layout_full"},[_vm._v(_vm._s(item.ordersn))]),_vm._v(" "),_c('div',{staticClass:"label_other money"},[_vm._v("￥"+_vm._s(item.price))])]),_vm._v(" "),_c('div',{staticClass:"content"},[_c('div',{staticClass:"row flex_layout_center"},[_c('div',{staticClass:"label"},[_vm._v("车牌号")]),_vm._v(" "),_c('div',{staticClass:"label_info flex_layout_full"},[_vm._v(_vm._s(item.carno))])]),_vm._v(" "),_c('div',{staticClass:"row flex_layout_center"},[_c('div',{staticClass:"label"},[_vm._v("入场时间")]),_vm._v(" "),_c('div',{staticClass:"label_info flex_layout_full"},[_vm._v(_vm._s(item.intotime))])]),_vm._v(" "),_c('div',{staticClass:"row flex_layout_center"},[_c('div',{staticClass:"label"},[_vm._v("缴费时间")]),_vm._v(" "),_c('div',{staticClass:"label_info flex_layout_full"},[_vm._v(_vm._s(item.paytime))])]),_vm._v(" "),_c('div',{staticClass:"row flex_layout_center"},[_c('div',{staticClass:"label"},[_vm._v("停车场")]),_vm._v(" "),_c('div',{staticClass:"label_info flex_layout_full"},[_vm._v(_vm._s(item.title))])])])])])])}),_vm._v(" "),(_vm.pageIng)?_c('infinite-loading',{ref:"infiniteLoading",attrs:{"identifier":_vm.infiniteId,"spinner":"waveDots"},on:{"infinite":_vm.onInfinite}},[_c('span',{attrs:{"slot":"no-more"},slot:"no-more"},[_vm._v("已经加载完啦!")]),_vm._v(" "),_c('ym-abnor',{attrs:{"slot":"no-results","type":"DATA"},slot:"no-results"})],1):_vm._e()]:_vm._e()],2)}
var staticRenderFns = []
var esExports = { render: render, staticRenderFns: staticRenderFns }
/* harmony default export */ __webpack_exports__["a"] = (esExports);

/***/ })
],[939]);