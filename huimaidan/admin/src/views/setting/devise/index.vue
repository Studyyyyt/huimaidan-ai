<template>
    <div class="diy-page" :style="cssVarStr">
        <div class="product_tabs">
            <div slot="title" class="title-wrapper">
                <div>
                    <el-button icon="el-icon-arrow-left" type="text" size="small" @click="goBack">返回</el-button>
                </div>
                <div class="divider"></div>
                <div>
                    <span class="name mr5">当前页面：{{ nameTxt || '模板' }}</span>
                    <el-popover v-model="visible" width="347">
                        <span slot="reference" class="iconfont iconzidingyicaidan cup"></span>
                        <template>
                            <div class="flex">
                                <el-input size="small" v-model="nameTxt" placeholder="必填不超过15个字" maxlength="15"
                                    style="width: 200px"></el-input>
                                <el-button style="padding-inline: 15px; color: #4073fa;" type="text" size="small"
                                    @click="cancel">取消</el-button>
                                <el-button type="primary" size="small" @click="determine">确定</el-button>
                            </div>
                        </template>
                    </el-popover>
                </div>
                <div class="btn-wrapper">
                    <el-button type="primary" size="small" @click="() => saveConfig(pageId)"
                        :loading="loading">保存</el-button>
                    <el-button class="ml20" size="small" @click="reast">重置</el-button>
                </div>
            </div>
        </div>
        <el-card :bordered="false" shadow="never">
            <div class="diy-wrapper" :style="'height:' + clientHeight + 'px;'">
                <!-- 左侧 -->
                <div class="left">
                    <div class="wrapper" :style="'height:' + clientHeight + 'px;'">
                        <div class="list" v-for="(item, index) in leftMenu" :key="index">
                            <div class="tips" @click="item.isOpen = !item.isOpen">
                                {{ item.title }}
                                <div class="iconfont  iconyou" v-if="!item.isOpen"></div>
                                <div class="iconfont  iconxia" v-else></div>
                            </div>
                            <!-- 拖拽组件 -->
                            <draggable class="dragArea list-group" :list="item.list"
                                :group="{ name: 'people', pull: 'clone', put: false }" :clone="cloneDog"
                                dragClass="dragClass" filter=".search , .navbar , .homeComb , .service">
                                <div class="list-group-item" :class="{
                                    search: element.cname == '搜索框',
                                    navbar: element.cname == '选项卡',
                                    homeComb: element.cname == '轮播搜索',
                                    service: element.cname == '悬浮按钮',
                                }" v-for="(element, index) in item.list" :key="element.id" @click="addDom(element, 1)"
                                    v-show="item.isOpen">
                                    <div>
                                        <div class="position" style="display: none">释放鼠标将组建添加到此处</div>
                                        <svg class="conter iconfont icon svg-icon" aria-hidden="true">
                                            <use :xlink:href="element.icon"></use>
                                        </svg>
                                        <p class="conter">{{ element.cname }}</p>
                                    </div>
                                </div>
                            </draggable>
                        </div>
                    </div>
                </div>
                <!-- 中间自定义配置移动端页面 -->
                <div class="wrapper-con">
                    <div class="content">
                        <div class="contxt">
                            <div class="overflowy">
                                <div class="picture"><img src="@/assets/images/electric.png" /></div>
                                <div class="page-title" :class="{ on: activeIndex == -100 }" @click="showTitle">
                                    {{ titleTxt }}
                                    <div class="delete-box"></div>
                                    <div class="handle"></div>
                                </div>
                            </div>
                            <div class="scrollCon" ref="scrollCon" :style="'height:' + rollHeight + 'px;'">
                                <div style="width: 460px; margin: 0 auto">
                                    <div class="scroll-box" :class="picTxt && tabValTxt == 2
                                        ? 'fullsize noRepeat'
                                        : picTxt && tabValTxt == 1
                                            ? 'repeat ysize'
                                            : 'noRepeat ysize'
                                        " :style="pageStyle" ref="imgContainer">
                                        <draggable class="dragArea list-group" :list="mConfig" group="people"
                                            @change="log" filter=".top" :move="onMove" animation="300">
                                            <div class="mConfig-item" :data-id="item.id" :data-comp="item.name" :class="{
                                                on: activeIndex == key,
                                                top: item.name == 'search_box' || item.name == 'nav_bar' || item.name == 'store_head',
                                                hide: defaultArrays[item.num].isHide,
                                            }" v-for="(item, key) in mConfig" :key="key"
                                                @click.stop="bindconfig(item, key)">
                                                <component :is="item.name" ref="getComponentData" :configData="propsObj"
                                                    :index="key" :num="item.num" :colorStyle="colorStyle"></component>
                                                <div class="delete-box">
                                                    <div class="handleType">
                                                        <div class="iconfont iconshang" :class="key === 0 ? 'on' : ''"
                                                            @click.stop="movePage(item, key, 1)" />
                                                        <div class="iconfont iconxia"
                                                            :class="key === mConfig.length - 1 ? 'on' : ''"
                                                            @click.stop="movePage(item, key, 0)" />
                                                        <div class="iconfont"
                                                            :class="defaultArrays[item.num].isHide ? 'iconyincang' : 'iconxianshi'"
                                                            @click.stop="bindHide(item)"></div>
                                                        <div class="iconfont icona-fuzhi1"
                                                        @click.stop="bindAddDom(item, 0, key)"></div>
                                                        <div class="iconfont iconshanchu3" @click.stop="bindDelete(item, key)" />
                                                    </div>
                                                </div>
                                                <div class="handle"></div>
                                                <div class="delete-name" :class="{ on: activeIndex == key }">{{
                                                    item.cname }}</div>
                                            </div>
                                        </draggable>
                                    </div>
                                </div>
                            </div>
                            <div class="overflowy" v-if="allowShowFooter">
                                <div class="page-foot mConfig-item" @click="showFoot" :class="{ on: activeIndex == -101, hide: pageFooterHide }"
                                    :style="pageFooterType == 1 ? 'bottom:' + (50 + pageFooterBottom) + 'px' : ''">
                                    <footPage></footPage>
                                    <div class="delete-box">
                                        <div class="handleType">
                                            <div class="iconfont" :class="pageFooterHide ? 'iconyincang' : 'iconxianshi'" @click.stop="hideFooter"></div>
                                            <div class="iconfont iconshanchu3" @click.stop="removeFooter">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="handle"></div>
                                </div>
                            </div>
                            <div class="defaultData" v-if="pageId !== 0">
                                <!-- <div class="data" @click="setmoren">设置默认</div>
                                  <div class="data" @click="getmoren">恢复默认</div> -->
                                <el-button class="data" @click="showTitle">页面设置</el-button>
                                <el-button class="data" @click="nameModal = true">另存模板</el-button>
                                <el-button class="data" @click="reast">重置</el-button>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- 右侧页面设置 -->
                <div class="right-box" :key="activeIndex">
                    <div class="mConfig-item" style="background-color: #fff" v-for="(item, key) in rConfig" :key="item.num">
                        <!-- <div class="title-bar">{{ item.cname }}</div> -->
                        <component :is="item.configName" @config="config" :activeIndex="activeIndex" :num="item.num"
                            :index="key"></component>
                    </div>
                </div>
            </div>
        </el-card>

        <el-dialog :visible.sync="nameModal" width="470px" title="设置模版名称" :show-close="true">
            <el-input v-model="saveName" placeholder="请输入模版名称"></el-input>
            <span slot="footer" class="dialog-footer">
                <el-button v-db-click @click="nameModal = false">取 消</el-button>
                <el-button type="primary" v-db-click @click="saveModal">确 定</el-button>
            </span>
        </el-dialog>
    </div>
</template>

<script>
import { diyGetInfo,merchantDiyInfo, diySave, merchantDiySave, setDefault, recovery, getRoutineCode } from '@/api/diy';
import vuedraggable from 'vuedraggable';
import mPage from '@/components/mobilePage/index.js';
import mConfig from '@/components/mobileConfig/index.js';
import footPage from '@/components/mobilePage/pages_foot';
import { mapState } from 'vuex';
import html2canvas from 'html2canvas';
import Setting from '@/setting';
import QRCode from 'qrcodejs2';
import theme from '@/mixins/theme';

export default {
    name: 'index.vue',
    components: {
        footPage,
        html2canvas,
        draggable: vuedraggable,
        ...mPage,
        ...mConfig,
    },
    filters: {
        filterTxt(val) {
            if (val) {
                return (val = val.substr(0, val.length - 1));
            }
        },
    },
    mixins: [theme],
    computed: {
        ...mapState({
            titleTxt: (state) => state.mobildConfig.pageTitle || '首页',
            showTxt: (state) => state.mobildConfig.pageShow,
            colorTxt: (state) => state.mobildConfig.pageColor,
            picTxt: (state) => state.mobildConfig.pagePic,
            colorPickerTxt: (state) => state.mobildConfig.pageColorPicker,
            tabValTxt: (state) => state.mobildConfig.pageTabVal,
            picUrlTxt: (state) => state.mobildConfig.pagePicUrl,
            pageFooterType: (state) => {
                const config = state.mobildConfig.pageFooter.navConfig;
                if (config && config.tabVal) return config.tabVal;
                return 0;
            },
            pageFooterHide: (state) => state.mobildConfig.pageFooter.isHide,
            pageFooterBottom: (state) => state.mobildConfig.pageFooter.mbConfig.val,
            defaultArrays: (state) => state.mobildConfig.defaultArray,
        }),
        nameTxt: {
            get() {
                return this.$store.state.mobildConfig.pageName;
            },
            set(value) {
                this.$store.commit('mobildConfig/UPNAME', value);
            },
        },
        pageStyle() {
            const style = {
                minHeight: this.rollHeight + "px",
                backgroundColor: "#f5f5f5"
            };

            const bgColor = this.colorTxt ? this.colorPickerTxt : null;
            const bgImg = this.picTxt ? this.picUrlTxt : null;

            if (bgColor) {
                style.backgroundColor = bgColor;
            } 
            if (bgImg) {
                style.backgroundImage = `url(${bgImg})`;
            }

            return style;
        }
    },
    data() {
        return {
            BaseURL: Setting.apiBaseURL.replace(/adminapi/, ''),
            qrcodeImg: '',
            modal: false,
            clientHeight: '', //页面动态高度
            rollHeight: '',
            leftMenu: [], // 左侧菜单
            lConfig: [], // 左侧组件
            mConfig: [], // 中间组件渲染
            rConfig: [], // 右侧组件配置
            activeConfigName: '',
            propsObj: {}, // 组件传递的数据,
            activeIndex: -100, // 选中的下标
            number: 0,
            pageId: '',
            category: [],
            tabList: [
                {
                    title: '组件库',
                    key: 0,
                },
                {
                    title: '页面链接',
                    key: 1,
                },
            ],
            loading: false,
            isSearch: false,
            isTab: false,
            isFllow: false,
            isComb: false,
            isService: false,
            visible: false,
            diyStatus: 0,
            nameModal: false,
            saveName: '',

            isFooterExist: false,
            allowShowFooter: false,
            isStore: false,
            isStoreHeadAlready: false,
        };
    },
    created() {
        this.isStore = this.$route.query.store == "1";
        this.$store.commit('mobildConfig/TOGGLE_MENU_LIST', { isMerchant: this.isStore });
        this.pageId = this.$route.query.id;
        this.lConfig = this.objToArr(mPage)
            .filter(item => {
                // 无targetScope则通用
                if (!item.targetScope) return true;

                // 门店模式下，只显示门店组件
                if (this.isStore) {
                    return item.targetScope === "store";
                }

                // 平台模式下，只显示平台组件
                if (!this.isStore) {
                    return item.targetScope === "platform";
                }

                return true;
            })
            .sort((a, b) => a.sortOrder - b.sortOrder);
        this.$nextTick(() => {
            this.arraySort();
            if (this.pageId != 0) {
                this.getDefaultConfig();
            } else {
                this.showTitle();
                !this.isStore && this.addFooter();
            }
            this.clientHeight = `${document.documentElement.clientHeight}` - 55; //获取浏览器可视区域高度
            let H = `${document.documentElement.clientHeight}` - 180;
            this.rollHeight = H > 650 ? 650 : H;
            let that = this;
            window.onresize = function () {
                that.clientHeight = `${document.documentElement.clientHeight}` - 55;
                let H = `${document.documentElement.clientHeight}` - 180;
                that.rollHeight = H > 650 ? 650 : H;
            };
        });
    },
    methods: {
        removeFooter() {
            this.isFooterExist = false;
            this.allowShowFooter = false;
        },
        saveModal() {
            if (!this.saveName) return this.$message.warning('请先输入模板名称');
            this.saveConfig(0, this.saveName);
        },
        goBack() {
            this.$router.back();
        },
        exportView() {
            let that = this;
            this.loading = true;
            this.$nextTick(() => {
                console.log(this.mConfig);
            });
        },
        preview() {
            this.modal = true;
            this.creatQrCode(this.pageId, this.diyStatus);
            this.routineCode(this.pageId);
        },
        //小程序二维码
        routineCode(id) {
            getRoutineCode(id)
                .then((res) => {
                    this.qrcodeImg = res.data.image;
                })
                .catch((err) => {
                    this.$message.error(err);
                });
        },
        //生成二维码
        creatQrCode(id, status) {
            this.$refs.qrCodeUrl.innerHTML = '';
            let url = '';
            if (status) {
                url = `${this.BaseURL}pages/index/index`;
            } else {
                url = `${this.BaseURL}pages/annex/special/index?id=${id}`;
            }
            var qrcode = new QRCode(this.$refs.qrCodeUrl, {
                text: url, // 需要转换为二维码的内容
                width: 160,
                height: 160,
                colorDark: '#000000',
                colorLight: '#ffffff',
                correctLevel: QRCode.CorrectLevel.H,
            });
        },
        changName(val) {
            this.$store.commit('mobildConfig/UPNAME', val);
        },
        cancel() {
            this.visible = false;
        },
        determine() {
            if (this.nameTxt.trim() == '') {
                return this.$message.error('请输入模板名称');
            }
            if (this.pageId == 0) {
                this.visible = false;
                return false;
            }

            this.saveConfig(this.pageId)
                .then(() => {
                    this.visible = false;
                });
        },
        returnTap() {
            this.$msgbox({
                title: '温馨提示',
                message: '确定离开此页面？系统可能不会保存您所做的更改。',
                showCancelButton: true,
                cancelButtonText: '取消',
                confirmButtonText: '确定',
                iconClass: 'el-icon-warning',
                confirmButtonClass: 'btn-custom-cancel',
            })
                .then(() => {
                    this.$router.push('/admin/setting/pages/devise/0');
                })
                .catch(() => { });
        },
        leftRemove({ to, from, item, clone, oldIndex, newIndex }) {
            if (this.isSearch && newIndex == 0) {
                if (item._underlying_vm_.name == 'z_wechat_attention') {
                    this.isFllow = true;
                } else {
                    this.$store.commit('mobildConfig/ARRAYREAST', this.mConfig[0].num);
                    this.mConfig.splice(0, 1);
                }
            }
            if ((this.isFllow = true && newIndex >= 1)) {
                this.$store.commit('mobildConfig/ARRAYREAST', this.mConfig[0].num);
            }
        },
        onMove(e) {
            const ignoreElement = [
                'search_box',
                'nav_bar',
                'home_comb',
                'store_head'
            ];
            return !ignoreElement.includes(e.relatedContext.element.name)
        },
        onCopy() {
            this.$message.success('复制成功');
        },
        onError() {
            this.$message.error('复制失败');
        },
        //设置默认数据
        setmoren() {
            setDefault(this.pageId)
                .then((res) => {
                    this.$message.success(res.msg);
                })
                .catch((err) => {
                    this.$message.error(err.msg);
                });
        },
        //恢复默认
        getmoren() {
            recovery(this.pageId)
                .then((res) => {
                    this.$message.success(res.msg);
                    this.reload();
                })
                .catch((err) => {
                    this.$message.error(err.msg);
                });
        },
        // 页面标题点击
        showTitle() {
            this.activeIndex = -100;
            let obj = {};
            for (var i in mConfig) {
                if (i == 'pageTitle') {
                    // this.rConfig = obj
                    obj = mConfig[i];
                    obj.configName = mConfig[i].name;
                    obj.cname = '页面设置';
                }
            }
            let abc = obj;
            this.rConfig = [];
            this.rConfig[0] = JSON.parse(JSON.stringify(obj));
        },
        // 页面底部点击
        showFoot() {
            this.activeIndex = -101;
            let obj = {};
            for (var i in mConfig) {
                if (i == 'pageFoot') {
                    // this.rConfig = obj
                    obj = mConfig[i];
                    obj.configName = mConfig[i].name;
                    obj.cname = '底部导航';
                }
            }
            this.rConfig = [];
            this.rConfig[0] = JSON.parse(JSON.stringify(obj));
        },
        // 对象转数组
        objToArr(data) {
            let obj = Object.keys(data);
            let m = obj.map((key) => data[key]);
            return m;
        },
        log(evt) {
            // 中间拖拽排序
            if (evt.moved) {
                if (evt.moved.element.name == 'search_box') {
                    return this.$message.warning('该组件禁止拖拽');
                }
                // if (evt.moved.element.name == "nav_bar") {
                //     return this.$message.warning("该组件禁止拖拽");
                // }
                evt.moved.oldNum = this.mConfig[evt.moved.oldIndex].num;
                evt.moved.newNum = this.mConfig[evt.moved.newIndex].num;
                evt.moved.status = evt.moved.oldIndex > evt.moved.newIndex;
                this.mConfig.forEach((el, index) => {
                    el.num = new Date().getTime() * 1000 + index;
                });
                evt.moved.list = this.mConfig;
                this.rConfig = [];
                let item = evt.moved.element;
                let tempItem = JSON.parse(JSON.stringify(item));
                this.rConfig.push(tempItem);
                this.activeIndex = evt.moved.newIndex;
                this.$store.commit('mobildConfig/SETCONFIGNAME', item.name);
                this.$store.commit('mobildConfig/defaultArraySort', evt.moved);
            }
            // 从左向右拖拽排序
            if (evt.added) {
                let data = evt.added.element;
                let obj = {};
                let timestamp = new Date().getTime() * 1000;
                data.num = timestamp;
                this.activeConfigName = data.name;
                let tempItem = JSON.parse(JSON.stringify(data));
                tempItem.id = 'id' + tempItem.num;
                this.mConfig[evt.added.newIndex] = tempItem;
                this.rConfig = [];
                this.rConfig.push(tempItem);
                this.mConfig.forEach((el, index) => {
                    el.num = new Date().getTime() * 1000 + index;
                });
                evt.added.list = this.mConfig;
                this.activeIndex = evt.added.newIndex;
                // 保存组件名称
                this.$store.commit('mobildConfig/SETCONFIGNAME', data.name);
                this.$store.commit('mobildConfig/defaultArraySort', evt.added);
            }
        },
        cloneDog(data) {
            // this.mConfig.push(tempItem)
            return {
                ...data,
            };
        },
        //数组元素互换位置
        swapArray(arr, index1, index2) {
            arr[index1] = arr.splice(index2, 1, arr[index1])[0];
            return arr;
        },
        //点击上下移动；
        movePage(item, index, type) {
            if (type) {
                if (index == 0) {
                    return;
                }
            } else {
                if (index == this.mConfig.length - 1) {
                    return;
                }
            }
            if (item.name == 'search_box' || item.name == 'nav_bar' || item.name == 'home_comb' || item.name == 'store_head') {
                return this.$message.warning('该组件禁止移动');
            }
            if (type) {
                if (
                    this.mConfig[index - 1].name == 'search_box' ||
                    this.mConfig[index - 1].name == 'nav_bar' ||
                    this.mConfig[index - 1].name == 'home_comb' ||
                    this.mConfig[index - 1].name == 'store_head'
                ) {
                    if (item.name !== "z_wechat_attention") {
                        return this.$message.warning('搜索框/组合组件/选项卡组件/门店头部必须为顶部组件');
                    }
                }
                this.swapArray(this.mConfig, index - 1, index);
            } else {
                this.swapArray(this.mConfig, index, index + 1);
            }
            let obj = {};
            this.rConfig = [];
            obj.oldIndex = index;
            if (type) {
                obj.newIndex = index - 1;
            } else {
                obj.newIndex = index + 1;
            }
            this.mConfig.forEach((el, index) => {
                el.num = new Date().getTime() * 1000 + index;
            });
            let tempItem = JSON.parse(JSON.stringify(item));
            this.rConfig.push(tempItem);
            obj.element = item;
            obj.list = this.mConfig;
            if (type) {
                this.activeIndex = index - 1;
            } else {
                this.activeIndex = index + 1;
            }

            this.$store.commit('mobildConfig/SETCONFIGNAME', item.name);
            this.$store.commit('mobildConfig/defaultArraySort', obj);
        },
        addFooter() {
            if (this.isFooterExist) return this.$message.error('该组件只能添加一次');
            this.isFooterExist = true;
            this.allowShowFooter = true;
            this.showFoot();
        },
        // 组件添加
        addDomCon(item, type, index) {
            if (item.name === "pagesFoot") {
                this.addFooter();
                return;
            }
            if (item.name == 'store_head') {
                if (this.isStoreHeadAlready) return this.$message.error('该组件只能添加一次');
                if (this.isTab) return this.$message.error('头部组件不能和选项卡组件同时使用');
                this.isStoreHeadAlready = true;
            }
            if (item.name == 'search_box') {
                if (this.isSearch) return this.$message.error('该组件只能添加一次');
                if (this.isComb) return this.$message.error('搜索组件不能和组合组件同时使用');
                this.isSearch = true;
            }
            if (item.name == 'nav_bar') {
                if (this.isTab) return this.$message.error('该组件只能添加一次');
                if (this.isComb) return this.$message.error('选项卡组件不能和组合组件同时使用');
                if (this.isStoreHeadAlready) return this.$message.error('选项卡组件不能和头部组件同时使用');
                this.isTab = true;
            }
            if (item.name == 'home_comb') {
                if (this.isComb) return this.$message.error('该组件只能添加一次');
                if (this.isSearch) return this.$message.error('组合组件不能和搜索组件同时使用');
                if (this.isTab) return this.$message.error('组合组件不能和选项卡组件同时使用');
                this.isComb = true;
            }
            if (item.name == 'home_service') {
                if (this.isService) return this.$message.error('该组件只能添加一次');
                this.isService = true;
            }
            let obj = {};
            let timestamp = new Date().getTime() * 1000;
            item.num = `${timestamp}`;
            item.id = `id${timestamp}`;
            this.activeConfigName = item.name;
            let tempItem = JSON.parse(JSON.stringify(item));
            if (item.name == 'home_comb') {
                this.rConfig = [];
                this.mConfig.unshift(tempItem);
                this.activeIndex = 0;
                this.rConfig.push(tempItem);
            } else if (item.name == 'search_box') {
                this.rConfig = [];
                this.mConfig.unshift(tempItem);
                this.activeIndex = 0;
                this.rConfig.push(tempItem);
            } else if (item.name == 'nav_bar') {
                this.rConfig = [];
                if (this.mConfig[0] && this.mConfig[0].name === 'search_box') {
                    this.mConfig.splice(1, 0, tempItem);
                    this.activeIndex = 1;
                } else {
                    this.mConfig.splice(0, 0, tempItem);
                    this.activeIndex = 0;
                }
                this.rConfig.push(tempItem);
            } else if (item.name == 'store_head') {
                this.rConfig = [];
                this.mConfig.unshift(tempItem);
                this.activeIndex = 0;
                this.rConfig.push(tempItem);
            } else {
                if (type) {
                    this.rConfig = [];
                    if (this.activeIndex == 0 && this.mConfig[1] && this.mConfig[1].name == 'nav_bar') {
                        this.activeIndex = 2;
                    } else {
                        this.activeIndex = this.activeIndex >= 0 ? this.activeIndex + 1 : this.mConfig.length;
                    }
                    this.mConfig.splice(this.activeIndex, 0, tempItem);
                    this.rConfig.push(tempItem);
                } else {
                    this.mConfig.splice(index + 1, 0, tempItem);
                    this.activeIndex = index + 1;
                    this.rConfig = [tempItem];
                }
            }
            this.mConfig.forEach((el, index) => {
                el.num = new Date().getTime() * 1000 + index;
            });
            // 保存组件名称
            obj.element = item;
            obj.list = this.mConfig;
            this.$store.commit('mobildConfig/SETCONFIGNAME', item.name);
            this.$store.commit('mobildConfig/defaultArraySort', obj);



            setTimeout(() => {
                const dom = document.querySelector(`[data-id="${item.id}"]`);
                if (dom) {
                    dom.scrollIntoView({ behavior: 'smooth' });
                }
            });
        },
        //中间页点击添加模块；
        bindAddDom(item, type, index) {
            let i = item;
            this.lConfig.forEach((j) => {
                if (item.name == j.name) {
                    i = j;
                }
            });
            this.addDomCon(i, type, index);
        },
        //左边配置模块点击添加；
        addDom(item, type) {
            this.addDomCon(item, type);
        },
        // 点击显示相应的配置
        bindconfig(item, index) {
            let tempItem = JSON.parse(JSON.stringify(item));
            this.rConfig = [tempItem];
            this.activeIndex = index;
            this.$store.commit('mobildConfig/SETCONFIGNAME', item.name);
        },
        hideFooter() {
            this.$store.commit('mobildConfig/footerHide');
        },
        bindHide(item) {
            let obj = this.$store.state.mobildConfig.defaultArray;
            let num = item.num;
            obj[num].isHide = !obj[num].isHide;
            this.$store.commit('mobildConfig/UPDATEARR', { num: num, val: obj[num] });
        },
        // 组件删除
        bindDelete(item, key) {
            if (item.name == 'store_head') {
                this.isStoreHeadAlready = false;
            }
            if (item.name == 'search_box') {
                this.isSearch = false;
            }
            if (item.name == 'nav_bar') {
                this.isTab = false;
            }
            if (item.name == 'home_comb') {
                this.isComb = false;
            }
            if (item.name == 'home_service') {
                this.isService = false;
            }
            this.mConfig.splice(key, 1);
            this.rConfig.splice(0, 1);
            if (this.mConfig.length != key) {
                this.rConfig.push(this.mConfig[key]);
            } else {
                if (this.mConfig.length) {
                    this.activeIndex = key - 1;
                    this.rConfig.push(this.mConfig[key - 1]);
                } else {
                    this.showTitle();
                }
            }
            // 删除第几个配置
            this.$store.commit('mobildConfig/DELETEARRAY', item);
        },
        // 组件返回
        config(data) {
            let propsObj = this.propsObj;
            propsObj.data = data;
            propsObj.name = this.activeConfigName;
        },
        addSort(arr, index1, index2) {
            arr[index1] = arr.splice(index2, 1, arr[index1])[0];
            return arr;
        },
        // 数组排序
        arraySort() {
            let tempArr = [];
            let basis = {
                title: '基础组件',
                list: [],
                isOpen: true,
            };
            let marketing = {
                title: '营销组件',
                list: [],
                isOpen: true,
            };
            let tool = {
                title: '工具组件',
                list: [],
                isOpen: true,
            };
            this.lConfig.map((el, index) => {
                if (el.type == 0) {
                    basis.list.push(el);
                }
                if (el.type == 1) {
                    marketing.list.push(el);
                }
                if (el.type == 2) {
                    tool.list.push(el);
                }
            });
            tempArr.push(basis, marketing, tool);
            this.leftMenu = tempArr;
        },
        diySaveDate(val, pageId, templateName) {
            const api = this.isStore ? merchantDiySave : diySave;
            return api(pageId, {
                value: val,
                title: this.titleTxt,
                name: templateName || this.nameTxt || '模板',
                is_show: this.showTxt ? 1 : 0,
                is_bg_color: this.colorTxt ? 1 : 0,
                color_picker: this.colorPickerTxt,
                bg_pic: this.picUrlTxt,
                bg_tab_val: this.tabValTxt,
                is_bg_pic: this.picTxt ? 1 : 0,
            })
                .then((res) => {
                    this.pageId = res.data.id;
                    this.$message.success(res.message);
                    this.nameModal = false;
                    this.loading = false;
                })
                .catch((res) => {
                    this.loading = false;
                    this.$message.error(res.message);
                });
        },
        // 保存配置
        saveConfig(pageId, templateName) {
            if (this.mConfig.length == 0) {
                return this.$message.error('暂未添加任何组件，保存失败！');
            }
            this.loading = true;
            let val = this.$store.state.mobildConfig.defaultArray;

            val = Object.entries(val).reduce((acc, [key, value]) => {
                if (value.name === "pageFoot") return acc;
                acc[key] = value;
                return acc;
            }, {});

            if (this.allowShowFooter) {
                let timestamp = new Date().getTime() * 1000;
                val[timestamp] = this.$store.state.mobildConfig.pageFooter;
            }

            return new Promise((resolve) => {
                this.$nextTick(() => {
                    this.diySaveDate(val, pageId, templateName).then(resolve);
                });
            });
        },
        // 获取默认配置
        getDefaultConfig() {
            const api = this.isStore ? merchantDiyInfo : diyGetInfo;
            api(this.pageId).then(({ data }) => {
                let obj = {};
                let tempARR = [];
                this.$store.commit('mobildConfig/titleUpdata', data.info.title);
                this.$store.commit('mobildConfig/nameUpdata', data.info.name);
                this.$store.commit('mobildConfig/showUpdata', data.info.is_show);
                this.$store.commit('mobildConfig/colorUpdata', data.info.is_bg_color || 0);
                this.$store.commit('mobildConfig/picUpdata', data.info.is_bg_pic || 0);
                this.$store.commit('mobildConfig/pickerUpdata', data.info.color_picker || '#f5f5f5');
                this.$store.commit('mobildConfig/radioUpdata', data.info.bg_tab_val || 0);
                this.$store.commit('mobildConfig/picurlUpdata', data.info.bg_pic || '');
                this.diyStatus = data.info.status;
                let newArr = this.objToArr(data.info.value);

                function sortNumber(a, b) {
                    return a.timestamp - b.timestamp;
                }
                newArr.sort(sortNumber);
                newArr.map((el, index) => {
                    if (el.name == 'headerSerch' || el.name == 'storeHeaderSerch') {
                        this.isSearch = true;
                    }
                    if (el.name == 'tabNav') {
                        this.isTab = true;
                    }
                    if (el.name == 'homeComb') {
                        this.isComb = true;
                    }
                    if (el.name == 'customerService') {
                        this.isService = true;
                    }
                    if (el.name == 'goodList') {
                        // let storage = window.localStorage;
                        // storage.setItem(el.timestamp, el.selectConfig.activeValue);
                    }
                    el.id = 'id' + el.timestamp;
                    this.lConfig.map((item, j) => {
                        if (el.name == item.defaultName) {
                            item.num = el.timestamp;
                            item.id = 'id' + el.timestamp;
                            let tempItem = JSON.parse(JSON.stringify(item));
                            tempARR.push(tempItem);
                            obj[el.timestamp] = el;
                            this.mConfig.push(tempItem);
                            // 保存默认组件配置
                            this.$store.commit('mobildConfig/ADDARRAY', {
                                num: el.timestamp,
                                val: el,
                            });
                        }
                    });
                });

                let objs = newArr[newArr.length - 1];

                if (objs.name == 'pageFoot') {
                    this.isFooterExist = true;
                    this.allowShowFooter = true;
                    this.$store.commit('mobildConfig/footPageUpdata', objs);
                } else if (objs.name == 'store_head') {
                    this.isStoreHeadAlready = true;
                }
                this.showTitle();
            });
        },
        // 重置
        reast() {
            if (this.pageId == 0) {
                this.$message.error('新增页面，无法重置');
            } else {
                this.$confirm('此操作将清空模板内容, 是否继续?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning',
                }).then((res) => {
                    this.removeFooter();
                    this.mConfig = [];
                    this.rConfig = [];
                    this.activeIndex = -99;
                    this.getDefaultConfig();
                });
            }
        },
    },
    beforeDestroy() {
        this.$store.commit('mobildConfig/titleUpdata', '');
        this.$store.commit('mobildConfig/nameUpdata', '');
        this.$store.commit('mobildConfig/showUpdata', 1);
        this.$store.commit('mobildConfig/colorUpdata', 0);
        this.$store.commit('mobildConfig/picUpdata', 0);
        this.$store.commit('mobildConfig/pickerUpdata', '#f5f5f5');
        this.$store.commit('mobildConfig/radioUpdata', 0);
        this.$store.commit('mobildConfig/picurlUpdata', '');
        this.$store.commit('mobildConfig/SETEMPTY');
    },
    destroyed() {
        this.$store.commit('mobildConfig/titleUpdata', '');
        this.$store.commit('mobildConfig/nameUpdata', '');
        this.$store.commit('mobildConfig/showUpdata', 1);
        this.$store.commit('mobildConfig/colorUpdata', 0);
        this.$store.commit('mobildConfig/picUpdata', 0);
        this.$store.commit('mobildConfig/pickerUpdata', '#f5f5f5');
        this.$store.commit('mobildConfig/radioUpdata', 0);
        this.$store.commit('mobildConfig/picurlUpdata', '');
        this.$store.commit('mobildConfig/SETEMPTY');
    },
};
</script>
<style>
.el-main {
    padding: 0px !important;
}

.el-input__suffix-inner {
    line-height: 2.5;
}

.el-form-item__label {
    color: var(--prev-color-text-regular);
    font-size: 12px !important;
}

.el-radio__label {
    font-size: 12px !important;
}

.el-input__inner {
    font-size: 12px !important;
}

.el-slider__input {
    width: 80px;
}

.el-slider__runway.show-input {
    margin-right: 100px;
}
</style>
<style scoped>
.header-title {
    background: var(--prev-color-primary);
    border-radius: 0;
    margin-bottom: 0;
    padding: 16px;
}

.ivu-page-header-title {
    color: #fff;
    font-size: 16px;
}
</style>
<style scoped lang="scss">
.product_tabs {
    padding: 10px 20px;
    background: #fff;
    border-bottom: 1px solid #e8eaec;
    text-align: right;

    .title-wrapper {
        display: flex;
        align-items: center;

        .divider {
            width: 1px;
            height: 16px;
            background: #e8eaec;
            margin-inline: 15px;
        }
    }

    .btn-wrapper {
        margin-left: auto;
    }
}

::v-deep .el-card__body {
    padding: 0;
}

::v-deep {

    .icondel_1,
    .upload-box {
        cursor: pointer;
    }
}

.c_label {
    margin-top: 0;
}

::v-deep .el-button--small {
    // border-radius: 0;
    border-radius: 4px;
}

.look,
.look:hover,
.look:focus,
.look:active,
.close,
.close:hover,
.close:focus,
.close:active {
    background: var(--prev-color-primary);
    color: #fff;
    border-color: #fff;
}

.save,
.save:hover,
.save:active,
.save:focus {
    background: #fff;
    color: var(--prev-color-primary);
    border-color: var(--prev-color-primary);
}

::v-deep .c_row-item {
    // margin-bottom: 15px;
}

.ysize {
    background-size: 100%;
}

.fullsize {
    background-size: 100% 100%;
}

.repeat {
    background-repeat: repeat;
}

.noRepeat {
    background-repeat: no-repeat;
}

.fl_header {
    color: #fff;

    .f-title {
        position: relative;
    }

    .return {
        color: #fff;
        margin-right: 34px;
        margin-left: 5px;

        &::after {
            content: ' ';
            position: absolute;
            width: 1px;
            height: 16px;
            background-color: rgba(238, 238, 238, 0.5);
            left: 65px;
            top: 50%;
            margin-top: -8px;
        }
    }

    .iconfont {
        color: #fff;
    }

    .f_title {
        &:hover {
            .return {
                color: rgba(255, 255, 255, 0.8);
            }

            .iconfanhui {
                color: rgba(255, 255, 255, 0.8);
            }
        }

        .name {
            font-size: 16px;
        }

        .iconfont {
            margin-left: 10px;
            color: #fff;
        }
    }
}

.wrapper-con {
    position: relative;
    flex: 1;
    background: #f0f2f5;
    display: flex;
    justify-content: center;
    padding-top: 20px;
    height: 100%;

    .acticons {
        position: absolute;
        right: 20px;
        top: 20px;
        display: flex;
        flex-direction: column;
        z-index: 1;

        .el-button+.el-button {
            margin-left: 0;
        }
    }

    /* min-width 700px; */
}

.main .content-wrapper {
    padding: 0 !important;
}

.defaultData {
    /* margin-left 20px; */
    cursor: pointer;
    position: absolute;
    left: 50%;
    margin-left: 245px;

    .data {
        display: block;
        margin-top: 20px;
        color: #282828;
        background-color: #fff;
        width: 94px;
        text-align: center;
        height: 32px;
        border-radius: 3px;
        font-size: 12px;
        margin-left: 0 !important;
    }

    .data:hover {
        background-color: #2d8cf0;
        color: #fff;
        border: 0;
    }
}

.overflowy {
    margin-right: 4px;

    .picture {
        width: 379px;
        height: 20px;
        margin: 0 auto;
        background-color: #fff;
    }
}

.bnt {
    width: 80px !important;
}

/* 定义滑块 内阴影+圆角 */
::-webkit-scrollbar-thumb {
    -webkit-box-shadow: inset 0 0 6px #fff;
    display: none;
}

.left:hover::-webkit-scrollbar-thumb,
.right-box:hover::-webkit-scrollbar-thumb {
    display: block;
}

.contxt:hover ::-webkit-scrollbar-thumb {
    display: block;
}

::-webkit-scrollbar {
    width: 4px !important;
    /* 对垂直流动条有效 */
}

.scrollCon {
    overflow-y: scroll;
    overflow-x: hidden;
}

.scroll-box .position {
    display: block !important;
    height: 40px;
    text-align: center;
    line-height: 40px;
    border: 1px dashed var(--prev-color-primary);
    color: var(--prev-color-primary);
    background-color: #edf4fb;
}

.scroll-box .conter {
    display: none !important;
}

.conter {
    margin-top: 3px;
}

.dragClass {
    background-color: #fff;
}

.ivu-mt {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
}

.iconfont-diy {
    font-size: 24px;
    color: var(--prev-color-primary);
}

.diy-wrapper {
    max-width: 100%;
    min-width: 1100px;
    display: flex;
    justify-content: space-between;
    height: calc(100vh - 62px);

    .left {
        width: 300px;
        /* border 1px solid #DDDDDD */
        border-radius: 4px;
        height: 100%;

        .title-bar {
            display: flex;
            color: #333;
            border-bottom: 1px solid #eee;
            border-radius: 4px;
            cursor: pointer;

            .title-item {
                display: flex;
                align-items: center;
                justify-content: center;
                flex: 1;
                height: 45px;

                &.on {
                    color: var(--prev-color-primary);
                    font-size: 14px;
                    border-bottom: 1px solid var(--prev-color-primary);
                }
            }
        }

        .wrapper {
            padding: 24px 20px;
            overflow-y: scroll;
            -webkit-overflow-scrolling: touch;

            .tips {
                display: flex;
                justify-content: space-between;
                padding-bottom: 15px;
                font-size: 13px;
                color: #000;
                cursor: pointer;

                .ivu-icon {
                    color: #000;
                }

                .iconfont {
                    font-size: 12px;
                    height: 18px;
                    display: flex;
                    align-items: center;
                }
            }


        }

        .link-item {
            padding: 10px;
            border-bottom: 1px solid #f5f5f5;
            font-size: 12px;
            color: #323232;

            .name {
                font-size: 14px;
                color: var(--prev-color-primary);
            }

            .copy_btn {
                cursor: pointer;
            }

            .link-txt {
                margin-top: 2px;
                word-break: break-all;
            }

            .params {
                margin-top: 5px;
                color: #1cbe6b;
                word-break: break-all;

                .txt {
                    color: #323232;
                }

                span {
                    &:last-child i {
                        display: none;
                        color: red;
                    }
                }
            }

            .lable {
                display: flex;
                margin-top: 5px;
                color: #999;

                p {
                    flex: 1;
                    word-break: break-all;
                }

                button {
                    margin-left: 30px;
                    width: 38px;
                }
            }
        }

        .dragArea.list-group {
            display: flex;
            flex-wrap: wrap;

            .list-group-item {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                width: 74px;
                height: 66px;
                margin-right: 15px;
                margin-bottom: 10px;
                font-size: 12px;
                color: #666;
                cursor: pointer;
                border-radius: 5px;
                text-align: center;

                &:hover {
                    box-shadow: 0 0 5px 0 rgba(24, 144, 255, 0.3);
                    border-right: 5px;
                    transform: scale(1.1);
                    transition: all 0.2s;
                }

                &:nth-child(3n) {
                    margin-right: 0;
                }
            }
        }
    }

    .content {
        position: relative;
        height: 100%;
        width: 100%;

        .page-foot {
            position: relative;
            width: 379px;
            margin: 0 auto 20px auto;

            .delete-box {
                display: none;
                position: absolute;
                left: -2px;
                top: 0;
                width: 383px;
                height: 100%;
                border: 2px dashed var(--prev-color-primary);
                padding: 10px 0;
            }

            &:hover,
            &.on {

                /* cursor: move; */
                .delete-box {
                    /* display: block; */
                }
            }

            &.on {
                cursor: move;

                .delete-box {
                    display: block;
                    border: 2px solid var(--prev-color-primary);
                    box-shadow: 0 0 10px 0 rgba(24, 144, 255, 0.3);
                }
            }
        }

        .page-title {
            position: relative;
            height: 35px;
            line-height: 35px;
            background: #fff;
            font-size: 15px;
            color: #333333;
            text-align: center;
            width: 379px;
            margin: 0 auto;

            .delete-box {
                display: none;
                position: absolute;
                left: -2px;
                top: 0;
                width: 383px;
                height: 100%;
                border: 2px dashed var(--prev-color-primary);
                padding: 10px 0;

                span {
                    position: absolute;
                    right: 0;
                    bottom: 0;
                    width: 32px;
                    height: 16px;
                    line-height: 16px;
                    display: inline-block;
                    text-align: center;
                    font-size: 10px;
                    color: #fff;
                    background: rgba(0, 0, 0, 0.4);
                    margin-left: 2px;
                    cursor: pointer;
                    z-index: 11;
                }
            }

            &:hover,
            &.on {

                /* cursor: move; */
                .delete-box {
                    /* display: block; */
                }
            }

            &.on {
                cursor: move;

                .delete-box {
                    display: block;
                    border: 2px solid var(--prev-color-primary);
                    box-shadow: 0 0 10px 0 rgba(24, 144, 255, 0.3);
                }
            }
        }

        .scroll-box {
            flex: 1;
            background-color: #fff;
            width: 379px;
            margin: 0 auto;
            padding-top: 1px;
        }

        .dragArea.list-group {
            width: 100%;
            height: 100%;


        }

        .mConfig-item {
            position: relative;
            cursor: move;

            &.hide {
                &::before {
                    position: absolute;
                    content: '已隐藏';
                    background: rgba(0, 0, 0, 0.5);
                    width: 100%;
                    height: 100%;
                    z-index: 99;
                    color: #fff;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                }
            }

            .delete-name.on {
                background: var(--prev-color-primary-light-3);
                color: #fff;

                &::before {
                    background: var(--prev-color-primary-light-3);
                }
            }

            &[data-comp="z_auxiliary_box"],
            &[data-comp="z_auxiliary_line"] {
                .delete-name {
                    top: -12px;
                }
            }

            .delete-name {
                position: absolute;
                top: 0;
                background: #fff;
                left: -100px;
                width: 86px;
                height: 32px;
                text-align: center;
                line-height: 32px;
                font-size: 13px;
                color: #666;
                border-radius: 3px;

                &::before {
                    content: '';
                    position: absolute;
                    width: 10px;
                    height: 10px;
                    background: #fff;
                    transform: rotate(45deg);
                    top: 50%;
                    right: -5px;
                    margin-top: -5px;
                }
            }



            &.on {
                cursor: move;

                .delete-box {
                    display: block;
                    border: 2px solid var(--prev-color-primary);
                    box-shadow: 0 0 10px 0 rgba(24, 144, 255, 0.3);
                }
            }
        }

        .mConfig-item:hover {
            transform: scale(1.01);
            box-shadow: 0 0 10px 0 rgba(24, 144, 255, 0.3);
            transition: all 0.2s;
        }

        .delete-box {
            display: none;
            position: absolute;
            left: -2px;
            top: 0;
            width: 383px;
            height: 100%;
            border: 2px dashed var(--prev-color-primary);

            /* padding: 10px 0; */
            .handleType {
                position: absolute;
                right: -43px;
                top: 0;
                width: 36px;
                border-radius: 4px;
                background-color: var(--prev-color-primary);
                cursor: pointer;
                color: #fff;
                font-weight: bold;
                text-align: center;
                padding: 4px 0;

                .el-tooltip {
                    background-color: inherit;
                    color: inherit;
                }

                .iconfont {
                    padding: 5px 0;

                    &.on {
                        opacity: 0.4;
                    }
                }
            }
        }
    }

    .right-box {
        max-width: 400px;
        min-width: 400px;
        height: 100%;
        border-radius: 4px;
        overflow: scroll;
        -webkit-overflow-scrolling: touch;

        ::v-deep .ivu-tabs-bar {
            margin-bottom: 16px;
        }

        .title-bar {
            width: 100%;
            height: 45px;
            line-height: 45px;
            padding-left: 24px;
            color: #000;
            border-radius: 4px;
            border-bottom: 1px solid #eee;
            font-size: 14px;
        }
    }

    ::-webkit-scrollbar {
        width: 6px;
        background-color: transparent;
    }

    ::-webkit-scrollbar-track {
        border-radius: 10px;
    }

    ::-webkit-scrollbar-thumb {
        background-color: #bfc1c4;
    }
}

.list ~.list .tips {
    margin-top: 24px;
}

.foot-box {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    height: 80px;
    background: #fff;
    box-shadow: 0px -2px 4px 0px rgba(0, 0, 0, 0.03);

    button {
        width: 100px;
        height: 32px;
        font-size: 13px;

        &:first-child {
            margin-right: 20px;
        }
    }
}

::v-deep .ivu-scroll-loader {
    display: none;
}

::v-deep .ivu-card-body {
    width: 100%;
    padding: 0;
    height: calc(100vh - 73px);
}

.rbtn {
    position: absolute;
    right: 20px;
}

.code {
    position: relative;
}

.QRpic {
    width: 160px;
    height: 160px;

    img {
        width: 100%;
        height: 100%;
    }
}

.contxt {
    display: flex;
    flex-direction: column;
    overflow: hidden;
    height: 100%;
}

.contxt:hover ::-webkit-scrollbar-thumb {
    display: block;
}

.iconfont-diy {
    font-size: 24px;
    color: var(--prev-color-primary);
}

.icon {
    width: 28px !important;
    height: 28px !important;
    // vertical-align: -0.15em;
    fill: currentColor;
    overflow: hidden;
}
</style>
