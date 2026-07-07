// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
import Vue from "vue";

import Cookies from "js-cookie";

import "normalize.css/normalize.css"; // a modern alternative to CSS resets

import Element from "element-ui";
import Moment from "moment";
import cascader from "element-ui/lib/cascader";
import "./styles/element-variables.scss";
import "viewerjs/dist/viewer.css";

import "@/styles/index.scss"; // global css
import "@/styles/iconfont/iconfont.css";
import "@/styles/iconfont2/iconfont.css";
import "@/styles/fonts/font.css";
// 懒加载
import VueLazyload from "vue-lazyload";

import App from "./App";
import store from "./store";
import router from "./router";
import Viewer from "v-viewer";
import FormCreate from "@form-create/element-ui";
import uploadPicture from "./components/uploadPictures/uploadFrom";
import VueUeditorWrap from "vue-ueditor-wrap";
import attrFrom from "./components/attrFrom";
import base from './components/base/index'; // 公共组件
import templatesFrom from "./components/templatesFrom";
import couponList from "./components/couponList";
import "./icons"; // icon
import "./permission"; // permission control
// swiper
import VueAwesomeSwiper from "vue-awesome-swiper";
import "swiper/dist/css/swiper.css";
import dbClick from "@/directive/dbClick";
import { HandlePrice } from "@/utils/util";
import modalForm from "@/libs/modal-form";
import modalAttr from "@/libs/modal-attr";
import modalTemplates from "@/libs/modal-templates";
import videoCloud from "@/utils/videoCloud";
import modalCoupon from "@/libs/modal-coupon";
import { modalSure } from "@/libs/public";
import { modalSureDelete } from "@/libs/public";
import { handleDeleteTable } from "@/libs/public";
import {VueJsonp} from "vue-jsonp"
import * as filters from "./filters"; // global filters modalTemplates
import notice from "@/libs/notice"; // global filters
import guidancePop from "@/components/guidancePop";
import { getToken } from "./utils/auth";
Vue.prototype.bus = new Vue();
Vue.use(uploadPicture);
Vue.use(base);
Vue.use(FormCreate);
Vue.use(VueJsonp);
Vue.use(VueAwesomeSwiper);
Vue.use(Viewer, {
  defaultOptions: {
    zIndex: 9999
  }
});
Vue.use(VueLazyload, {
  preLoad: 1.3,
  error: require("./assets/images/no.png"),
  loading: require("./assets/images/moren.jpg"),
  attempt: 1,
  listenEvents: [
    "scroll",
    "wheel",
    "mousewheel",
    "resize",
    "animationend",
    "transitionend",
    "touchmove"
  ]
});
Vue.component("vue-ueditor-wrap", VueUeditorWrap);
Vue.component("attrFrom", attrFrom);
Vue.component("templatesFrom", templatesFrom);
Vue.component("couponList", couponList);
Vue.prototype.$modalForm = modalForm;
Vue.prototype.$modalSure = modalSure;
Vue.prototype.$videoCloud = videoCloud;
Vue.prototype.$modalSureDelete = modalSureDelete;
Vue.prototype.handleDeleteTable = handleDeleteTable;
Vue.prototype.$modalAttr = modalAttr;
Vue.prototype.$modalTemplates = modalTemplates;
Vue.prototype.$modalCoupon = modalCoupon;
Vue.prototype.moment = Moment;
Vue.component("guidancePop", guidancePop);
Vue.prototype.$HandlePrice = HandlePrice;
Vue.use(Element, {
  size: Cookies.get("size") || "medium" // set element-ui default size
});
Vue.use(cascader);

// register global utility filters
Object.keys(filters).forEach(key => {
  Vue.filter(key, filters[key]);
});

if (process.env.NODE_ENV === "production") {
  // 生产环境下加载统计脚本
  import("@/utils/track");
}

var _hmt = _hmt || [];

router.beforeEach((to, from, next) => {
  /* 路由发生变化修改页面title */
  if (_hmt) {
    if (to.path) {
      _hmt.push(["_trackPageview", "/#" + to.fullPath]);
    }
  }
  if (to.meta.title) {
    document.title =
      to.meta.title + "-" + JSON.parse(Cookies.get("MerInfo")).login_title;
  }
  next();
});

const token = getToken();
let _notice;
if (token) {
  _notice = notice(token);
}
Vue.directive("dbClick", dbClick);
Vue.config.productionTip = false;
export default new Vue({
  el: "#app",
  data: {
    notice: _notice
  },
  methods: {
    closeNotice() {
      this.notice && this.notice();
    }
  },
  router,
  store,
  render: h => h(App)
});
