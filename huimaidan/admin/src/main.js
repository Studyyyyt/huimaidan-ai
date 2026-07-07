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

// 懒加载
import VueLazyload from "vue-lazyload";

import Element from "element-ui";
import cascader from "element-ui/lib/cascader";
import Moment from "moment";
import "../node_modules/element-ui/packages/theme-chalk/src/index.scss";
import "./styles/element-variables.scss";
import "viewerjs/dist/viewer.css";
import "@/styles/index.scss"; // global css
import "@/styles/iconfont/iconfont.css";
import "@/styles/iconfont/iconfont.js";
import "@/styles/iconfont2/iconfont.css";
import "@/styles/fonts/font.css";

import App from "./App";
import store from "./store";
import router from "./router";
import base from './components/base/index'; // 公共组件
import Viewer from "v-viewer";
import FormCreate from "@form-create/element-ui";
import uploadPicture from "./components/uploadPictures/uploadFrom";
import VueUeditorWrap from "vue-ueditor-wrap";
import newsCategory from "./components/newsCategory/newsCategoryFrom";

import { getToken } from "./utils/auth";
Vue.prototype.bus = new Vue();
import "./icons"; // icon
import "./permission"; // permission control
// import './utils/error-log' // error log
import modalForm from "@/libs/modal-form";
import videoCloud from "@/utils/videoCloud";
import { modalSure, deleteSure } from "@/libs/public";
import { modalSureDelete } from "@/libs/public";
import * as filters from "./filters";
import notice from "@/libs/notice";
import guidancePop from "@/components/guidancePop";
// swiper
import VueAwesomeSwiper from "vue-awesome-swiper";
import "swiper/dist/css/swiper.css";
import { HandlePrice } from "@/utils/util";
// 数据大屏
import {
  fullScreenContainer,
  loading,
  borderBox1,
  digitalFlop,
  capsuleChart,
  scrollRankingBoard
} from "@jiaminghi/data-view";
import "vue-easytable/libs/theme-default/index.css";
import "@/views/dataScreen/assets/css/index.scss";
import "@/views/dataScreen/assets/css/public.scss";
import Echart from "@/views/dataScreen/components/echart/index.vue";
import ItemWrap from "@/views/dataScreen/components/item-wrap/item-wrap.vue";
import Message from "@/views/dataScreen/components/message/message.vue";
import Reacquire from "@/views/dataScreen/components/reacquire/reacquire.vue";
import Messages from "@/views/dataScreen/components/message/message";
import dbClick from "@/directive/dbClick";
import { VueJsonp } from "vue-jsonp"
Vue.use(VueJsonp)
// datav组件
Vue.use(fullScreenContainer);
Vue.use(loading);
Vue.use(borderBox1);
Vue.use(digitalFlop);
Vue.use(capsuleChart);
Vue.use(scrollRankingBoard);

// 自定义组件
Vue.component("Echart", Echart);
Vue.component("ItemWrap", ItemWrap);
Vue.component("Message", Message);
Vue.component("Reacquire", Reacquire);
Vue.prototype.$Message = Messages;

// --------------
Vue.use(uploadPicture);
Vue.use(FormCreate);
Vue.use(newsCategory);

Vue.use(base);
Vue.component("vue-ueditor-wrap", VueUeditorWrap);
Vue.use(VueAwesomeSwiper);
Vue.use(Viewer, {
  defaultOptions: {
    zIndex: 9999
  }
});
Vue.use(VueLazyload, {
  preLoad: 1.3,
  error: require("@/assets/images/no.png"),
  loading: require("@/assets/images/moren.jpg"),
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

Vue.prototype.$modalForm = modalForm;
Vue.prototype.$videoCloud = videoCloud;
Vue.prototype.$modalSure = modalSure;
Vue.prototype.$deleteSure = deleteSure;
Vue.prototype.$modalSureDelete = modalSureDelete;
Vue.prototype.moment = Moment;
Vue.prototype.$HandlePrice = HandlePrice;

Vue.component("guidancePop", guidancePop);

Vue.use(Element, {
  size: Cookies.get("size") || "medium", // set element-ui default size
  zIndex: 1000
});
Vue.use(cascader);

// register global utility filters
Object.keys(filters).forEach(key => {
  Vue.filter(key, filters[key]);
});

Vue.directive("dbClick", dbClick);
Vue.directive("debounce", {
  inserted(el, binding) {
    el.addEventListener("click", e => {
      el.classList.add("is-disabled");
      el.disabled = true;
      setTimeout(() => {
        el.disabled = false;
        el.classList.remove("is-disabled");
      }, 1000);
    });
  }
});

const token = getToken();
let _notice;
if (token) {
  _notice = notice(token);
}

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
  // if (to.meta.title) {
  //   document.title = to.meta.title + '-' + JSON.parse(Cookies.get('MerInfo')).login_title
  // }
  next();
});

Vue.config.productionTip = false;
export default new Vue({
  el: "#app",
  router,
  data: {
    notice: _notice
  },
  methods: {
    closeNotice() {
      this.notice && this.notice();
    }
  },
  store,
  render: h => h(App),
  watch: {
    // 监听路由 控制侧边栏显示 标记当前顶栏菜单（如需要）
    $route(to, from) {
      const onRoutes = to.meta.activeMenu ? to.meta.activeMenu : to.meta.path;
      this.$store.commit("menu/setActivePath", onRoutes);
      if (to.name == "crud_crud") {
        this.$store.state.menus.oneLvRoutes.map(e => {
          if (e.path === to.path) {
            to.meta.title = e.title;
          }
        });
      }
    }
  }
});
