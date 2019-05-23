webpackJsonp([60],{349:function(t,e,i){"use strict";function s(t){i(391)}var n=(i(392),i(393)),a=i(394),o=i(0),c=s,r=o(n.a,a.a,!1,c,null,null);e.a=r.exports},391:function(t,e){},392:function(t,e,i){"use strict";var s=i(76);s.a,Boolean,String,String,String,Array,Boolean,String,String,Boolean},393:function(t,e,i){"use strict";var s=i(76);e.a={name:"search",mixins:[s.a],props:{required:{type:Boolean,default:!1},placeholder:String,cancelText:String,value:{type:String,default:""},results:{type:Array,default:function(){return[]}},autoFixed:{type:Boolean,default:!0},top:{type:String,default:"0px"},position:{type:String,default:"fixed"},autoScrollToTop:Boolean},created:function(){this.value&&(this.currentValue=this.value)},computed:{fixPosition:function(){return this.isFixed?"absolute"===this.position?"absolute":"fixed":"static"}},methods:{emitEvent:function(){var t=this;this.$nextTick(function(){t.$emit("input",t.currentValue),t.$emit("on-change",t.currentValue)})},onComposition:function(t,e){"start"===e&&(this.onInput=!0),"end"===e&&(this.onInput=!1,this.emitEvent()),"input"===e&&(this.onInput||this.emitEvent())},clear:function(){this.currentValue="",this.emitEvent(),this.isFocus=!0,this.setFocus(),this.autoFixed&&!this.isFixed&&(this.isFixed=!0),this.$emit("on-clear")},cancel:function(){this.isCancel=!0,this.currentValue="",this.emitEvent(),this.isFixed=!1,this.$emit("on-cancel")},handleResultClick:function(t){this.$emit("result-click",t),this.$emit("on-result-click",t),this.isCancel=!0,this.isFixed=!1},touch:function(){this.isCancel=!1,this.autoFixed&&(this.isFixed=!0),this.$emit("on-touch")},setFocus:function(){this.$refs.input.focus()},setBlur:function(){this.$refs.input.blur()},onFocus:function(){this.isFocus=!0,this.$emit("on-focus"),this.touch()},onBlur:function(){this.isFocus=!1,this.$emit("on-blur")}},data:function(){return{onInput:!1,currentValue:"",isCancel:!0,isFocus:!1,isFixed:!1}},watch:{isFixed:function(t){!0===t&&(this.setFocus(),this.isFocus=!0,this.autoScrollToTop&&setTimeout(function(){window.scrollTo(0,0)},150))},value:function(t){this.currentValue=t}}}},394:function(t,e,i){"use strict";var s=function(){var t=this,e=t.$createElement,i=t._self._c||e;return i("div",{staticClass:"vux-search-box",class:{"vux-search-fixed":t.isFixed},style:{top:t.isFixed?t.top:"",position:t.fixPosition}},[i("div",{staticClass:"weui-search-bar",class:{"weui-search-bar_focusing":!t.isCancel||t.currentValue}},[t._t("left"),t._v(" "),i("form",{staticClass:"weui-search-bar__form",attrs:{action:"."},on:{submit:function(e){e.preventDefault(),t.$emit("on-submit",t.value)}}},[i("label",{directives:[{name:"show",rawName:"v-show",value:!t.isFixed&&t.autoFixed,expression:"!isFixed && autoFixed"}],staticClass:"vux-search-mask",attrs:{for:"search_input_"+t.uuid},on:{click:t.touch}}),t._v(" "),i("div",{staticClass:"weui-search-bar__box"},[i("i",{staticClass:"weui-icon-search"}),t._v(" "),i("input",{directives:[{name:"model",rawName:"v-model",value:t.currentValue,expression:"currentValue"}],ref:"input",staticClass:"weui-search-bar__input",attrs:{type:"search",autocomplete:"off",id:"search_input_"+t.uuid,placeholder:t.placeholder,required:t.required},domProps:{value:t.currentValue},on:{focus:t.onFocus,blur:t.onBlur,compositionstart:function(e){t.onComposition(e,"start")},compositionend:function(e){t.onComposition(e,"end")},input:[function(e){e.target.composing||(t.currentValue=e.target.value)},function(e){t.onComposition(e,"input")}]}}),t._v(" "),i("a",{directives:[{name:"show",rawName:"v-show",value:t.currentValue,expression:"currentValue"}],staticClass:"weui-icon-clear",attrs:{href:"javascript:"},on:{click:t.clear}})]),t._v(" "),i("label",{directives:[{name:"show",rawName:"v-show",value:!t.isFocus&&!t.value,expression:"!isFocus && !value"}],staticClass:"weui-search-bar__label",attrs:{for:"search_input_"+t.uuid}},[i("i",{staticClass:"weui-icon-search"}),t._v(" "),i("span",[t._v(t._s(t.placeholder||"搜索"))])])]),t._v(" "),i("a",{staticClass:"weui-search-bar__cancel-btn",attrs:{href:"javascript:"},on:{click:t.cancel}},[t._v(t._s(t.cancelText||"取消")+"\n    ")]),t._v(" "),t._t("right")],2),t._v(" "),i("div",{directives:[{name:"show",rawName:"v-show",value:t.isFixed,expression:"isFixed"}],staticClass:"weui-cells vux-search_show"},[t._t("default"),t._v(" "),t._l(t.results,function(e){return i("div",{staticClass:"weui-cell weui-cell_access",on:{click:function(i){t.handleResultClick(e)}}},[i("div",{staticClass:"weui-cell__bd weui-cell_primary"},[i("p",[t._v(t._s(e.title))])])])})],2)])},n=[],a={render:s,staticRenderFns:n};e.a=a},76:function(t,e,i){"use strict";e.a={created:function(){this.uuid=Math.random().toString(36).substring(3,8)}}},826:function(t,e,i){"use strict";Object.defineProperty(e,"__esModule",{value:!0});var s=i(827);new Vue({render:function(t){return t(s.a)}}).$mount("#app")},827:function(t,e,i){"use strict";function s(t){i(828)}var n=(i(829),i(830)),a=i(831),o=i(0),c=s,r=o(n.a,a.a,!1,c,"data-v-6018ad07",null);e.a=r.exports},828:function(t,e){},829:function(t,e,i){"use strict";var s=i(8),n=i(3),a=i(10),o=i(21),c=i(5),r=i(11),u=i(13),l=i(349),d=i(12),h=i.n(d);a.a,o.a,c.a,r.a,u.a,l.a,h.a},830:function(t,e,i){"use strict";var s=i(8),n=i(3),a=i(10),o=i(21),c=i(5),r=i(11),u=i(13),l=i(349),d=i(12),h=i.n(d);e.a={components:{ViewBox:a.a,Badge:o.a,Group:c.a,XHeader:r.a,LoadMore:u.a,Search:l.a,InfiniteLoading:h.a},data:function(){return{items:[],keyword:""}},beforeCreate:function(){this.initialize={currentLoaded:!1,currentLoading:!1,pindex:1,psize:20}},beforeMount:function(){this.$vux.loading.show()},mounted:function(){var t=this;setTimeout(function(){t.$vux.loading.hide()},300)},methods:{onInfinite:function(t){var e=this;if(e.initialize.currentLoading)return!1;e.initialize.currentLoading=!0,n.a.appcostOrder(e.initialize.pindex).then(function(i){e.initialize.pindex++,e.items=e.items.concat(i.data),i.data.length>=e.initialize.psize?(e.initialize.currentLoading=!1,t.loaded()):t.complete()}).catch(function(t){e.$vux.toast.show({type:"warn",text:t.err_msg})})},url:function(t){return s.a.M.url(t)},getResult:function(t){var e=this;e.keyword=t,n.a.appcostOrder("",e.keyword).then(function(t){e.items=t.data}).catch(function(t){e.$vux.toast.show({type:"warn",text:t.err_msg})})},onFocus:function(){console.log("on focus")},onCancel:function(){console.log("on cancel")}},computed:{}}},831:function(t,e,i){"use strict";var s=function(){var t=this,e=t.$createElement,i=t._self._c||e;return i("div",{staticStyle:{height:"100%"}},[i("view-box",{ref:"viewBox",staticClass:"container",attrs:{"body-padding-top":"0","body-padding-bottom":"10px"}},[i("x-header",{staticStyle:{"background-color":"#ff9800"},attrs:{title:"订单列表"}},[i("a",{staticStyle:{color:"#FFFFFF"},attrs:{slot:"right",href:t.url("xqsys/home")},slot:"right"},[t._v("首页")])]),t._v(" "),i("search",{ref:"search",attrs:{autoFixed:!1,placeholder:"搜索姓名/房号"},on:{"on-change":t.getResult,"on-focus":t.onFocus,"on-cancel":t.onCancel}}),t._v(" "),i("div",{staticClass:"weui-panel weui-panel_access"},[i("div",{staticClass:"weui-panel__bd"},t._l(t.items,function(e,s){return i("a",{key:s,staticClass:"weui-media-box weui-media-box_appmsg",attrs:{href:e.url}},[i("div",{staticClass:"weui-media-box__bd"},[i("p",{staticClass:"weui-media-box__title"},[t._v(t._s(e.title)+t._s(e.address))]),t._v(" "),i("p",{staticClass:"weui-media-box__desc",staticStyle:{"-webkit-box-orient":"vertical","margin-top":"10px"}},[t._v("\n                            订单号:"+t._s(e.ordersn)+"   费用:"+t._s(e.price)+"元\n                        ")]),t._v(" "),i("p",{staticClass:"weui-media-box__desc",staticStyle:{"-webkit-box-orient":"vertical","margin-top":"10px"}},[t._v("\n                            "+t._s(e.cctime)+"("),1==e.status?i("span",[t._v("已付款")]):i("span",[t._v("未付款")]),t._v(")\n                        ")])]),t._v(" "),i("span",{staticClass:"weui-cell__ft"},[1==e.status?i("span",[t._v("已付款")]):i("span",[t._v("未付款")])])])}))]),t._v(" "),i("div",{staticClass:"clearfloat"}),t._v(" "),i("infinite-loading",{ref:"infiniteLoading",attrs:{spinner:"waveDots"},on:{infinite:t.onInfinite}},[i("span",{attrs:{slot:"no-more"},slot:"no-more"},[t._v("已全部加载完…")]),t._v(" "),i("span",{attrs:{slot:"no-results"},slot:"no-results"},[i("load-more",{attrs:{"show-loading":!1,tip:"暂无数据","background-color":"#fbf9fe"}})],1)])],1)],1)},n=[],a={render:s,staticRenderFns:n};e.a=a}},[826]);