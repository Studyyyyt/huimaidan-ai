<template>
	<div class="mobile-config">
		<Form ref="formInline">
			<div v-for="(item) of renderComp" :key="item.configNme">
				<component :is="item.components.name" :configObj="configObj" ref="childData" :configNme="item.configNme"
					:key="item.configNme" :index="activeIndex" :num="item.num"></component>
			</div>
			<rightBtn :activeIndex="activeIndex" :configObj="configObj"></rightBtn>
		</Form>
	</div>
</template>

<script>
import toolCom from '@/components/mobileConfigRight/index.js';
import rightBtn from '@/components/rightBtn/index.vue';
import { merchantListApi } from '@/api/merchant';

export default {
	name: 'c_home_store',
	componentsName: 'home_store',
	cname: '店铺街',
	props: {
		activeIndex: {
			type: null,
		},
		num: {
			type: null,
		},
		index: {
			type: null,
		},
	},
	components: {
		...toolCom,
		rightBtn,
	},
	data() {
		return {
			hotIndex: 1,
			configObj: {}, // 配置对象

			baseComp: [
				{
					components: toolCom.c_set_up,
					configNme: 'setUp',
				},
			],

			contentConfig: {
				fullComp: [],
				components: [
					{ // 标题设置
						components: toolCom.c_title,
						configNme: 'titleSetting'
					},
					{ // 标题类型
						components: toolCom.c_radio,
						configNme: 'titleType'
					},
					{ // 标题图片
						components: toolCom.c_upload_img,
						configNme: 'titleImg',
						visible: (configObj) => {
							return configObj.titleType.tabVal == 0;
						}
					},
					{ // 展示风格
						components: toolCom.c_title,
						configNme: 'displaySetting'
					},
					{ // 选择风格
						components: toolCom.c_radio,
						configNme: 'styleConfig'
					},
					{ // 更多按钮
						components: toolCom.c_radio,
						configNme: 'moreBtn'
					},
					{ // 筛选条件
						components: toolCom.c_radio,
						configNme: 'filterBtn'
					},
					{ // 店铺设置
						components: toolCom.c_title,
						configNme: 'storeSetting'
					},
					{ // 选择方式
						components: toolCom.c_select,
						configNme: 'selectStoreType'
					},
					{ // 选择店铺
						components: toolCom.c_shop_list,
						configNme: 'storeList',
						visible: (configObj) => {
							return configObj.selectStoreType.activeValue == 1;
						}
					},
					{ // 店铺类型
						components: toolCom.c_select,
						configNme: 'storeType',
						visible: (configObj) => {
							return configObj.selectStoreType.activeValue == 2;
						}
					},
					{ // 店铺分类
						components: toolCom.c_select,
						configNme: 'storeCate',
						visible: (configObj) => {
							return configObj.selectStoreType.activeValue == 4;
						}
					},
					{ // 店铺排序
						components: toolCom.c_radio,
						configNme: 'storeSort'
					},
					{ // 展示数量
						components: toolCom.c_slider,
						configNme: 'storeNum'
					},
					{ // 更多链接
						components: toolCom.c_input_item,
						configNme: 'linkConfig'
					},
					{ // 商品设置
						components: toolCom.c_title,
						configNme: 'goodsSetting'
					},
					{ // 展示信息
						components: toolCom.c_checkbox,
						configNme: 'goodsInfo'
					},
					{ // 展示数量
						components: toolCom.c_slider,
						configNme: 'goodsNum'
					},
				],
			},

			styleConfig: {
				fullComp: [],
				components: [
					// ===== 标题设置 =====
					{
						children: [
							{ // 标题
								components: toolCom.c_title,
								configNme: 'titleRight',
							},
							{ // 标题颜色
								components: toolCom.c_bg_color,
								configNme: 'titleColor'
							},
							{ // 标题字体大小
								components: toolCom.c_slider,
								configNme: 'titleFontSize'
							},
							{ // 标题字体样式
								components: toolCom.c_radio,
								configNme: 'titleFontStyle'
							}
						],
						visible: configObj => {
							return configObj.titleType.tabVal == 1;
						}
					},

					// ===== 店铺样式 =====
					{ // 标题
						components: toolCom.c_title,
						configNme: 'storeImgTitle',
					},
					{ // 店铺图片圆角
						components: toolCom.c_fillet,
						configNme: 'storeImgRounded'
					},
					{ // 店铺间距
						components: toolCom.c_slider,
						configNme: 'storePadding'
					},
					{ // 店铺名称样式
						components: toolCom.c_radio,
						configNme: 'storeNameStyle'
					},
					{ // 店铺名称颜色
						components: toolCom.c_bg_color,
						configNme: 'storeNameColor'
					},
					{ // 自营背景颜色
						components: toolCom.c_bg_color,
						configNme: 'selfSupportBgColor'
					},
					{ // 自营文字颜色
						components: toolCom.c_bg_color,
						configNme: 'selfSupportTextColor'
					},

					{
						children: [
							{ // 商品样式标题
								components: toolCom.c_title,
								configNme: 'goodsStyleTitle',
							},
							{ // 商品图片圆角
								components: toolCom.c_fillet,
								configNme: 'goodsImgRounded'
							},
							{ // 商品间距
								components: toolCom.c_slider,
								configNme: 'goodsPadding'
							},
							{ // 商品名称样式
								components: toolCom.c_radio,
								configNme: 'goodsNameStyle'
							},
							{ // 商品名称颜色
								components: toolCom.c_bg_color,
								configNme: 'goodsNameColor'
							},
							{ // 阴影颜色
								components: toolCom.c_shadow,
								configNme: 'goodsShadowConfig'
							}
						],
						visible: (configObj) => {
							return configObj.styleConfig.tabVal != 2;
						}
					},

					{ // 通用样式标题
						components: toolCom.c_title,
						configNme: 'commonStyleTitle',
					},
					{ // 组件上浮
						components: toolCom.c_slider,
						configNme: 'componentFloat'
					},
					{ // 组件背景
						components: toolCom.c_bg_color,
						configNme: 'componentBg'
					},
					{ // 组件底部背景
						components: toolCom.c_bg_color,
						configNme: 'componentBottomBg'
					},
					{ // 组件上边距
						components: toolCom.c_slider,
						configNme: 'marginTop'
					},
					{ // 组件下边距
						components: toolCom.c_slider,
						configNme: 'marginBottom'
					},
					{ // 组件左右边距
						components: toolCom.c_slider,
						configNme: 'marginLr'
					},
					{ // 页面上间距
						components: toolCom.c_slider,
						configNme: 'itemMarginTop'
					},
					{ // 组件背景圆角
						components: toolCom.c_fillet,
						configNme: 'bgRadius'
					},
					{ // 阴影颜色
						components: toolCom.c_shadow,
						configNme: 'shadowConfig'
					}
				],
			},
		};
	},
	watch: {
		num(nVal) {
			// debugger;
			let value = JSON.parse(JSON.stringify(this.$store.state.mobildConfig.defaultArray[nVal]));
			this.configObj = value;
		},
		configObj: {
			handler(nVal, oVal) {
				this.$store.commit('mobildConfig/UPDATEARR', { num: this.num, val: nVal });
				this.updateContentConfig();
				this.updateStyleConfig();
			},
			deep: true,
		},
		queryParams(val) {
			this.fetchStoreList(val);
		}
	},
	computed: {
		queryParams() {
			if (!this.configObj || !this.configObj.selectStoreType) return {};
			const {
				selectStoreType,
				storeType,
				storeCate,
				storeGroup,
				storeSort,
				storeNum
			} = this.configObj;
			const storeTypeVal = selectStoreType.activeValue;

			const query = {
				status: 1,
				sort: storeSort.tabVal,
				limit: storeNum.val,
			};

			if (storeTypeVal == 2) {
				query.type_id = storeType.activeValue.join(',');
			} else if (storeTypeVal == 3) {
				query.is_best = 1;
			} else if (storeTypeVal == 4) {
				query.category_id = storeCate.activeValue.join(',');
			} else if (storeTypeVal == 5) {
				query.region_id = storeGroup.activeValue.join(',');
			}

			return query;
		},
		renderComp() {
			const isStyleMode = this.configObj.setUp && this.configObj.setUp.tabVal === 1;
			const comp = isStyleMode ? this.styleConfig.fullComp : this.contentConfig.fullComp;
			return this.baseComp.concat(comp);
		}
	},
	mounted() {
		this.$nextTick(() => {
			let value = JSON.parse(JSON.stringify(this.$store.state.mobildConfig.defaultArray[this.num]));
			this.configObj = value;
		});
	},
	methods: {
		fetchStoreList(query) {
			if (this.configObj.selectStoreType.activeValue == 1) return;
			merchantListApi(query).then(res => {
				this.configObj.storeList.list = res.data.list.slice(0, query.limit);
			});
		},
		filterComp(components) {
			return components
				.filter(item => !item.visible || item.visible(this.configObj))
				.reduce((acc, item) => {
					if (item.children) {
						acc.push(...item.children);
					} else {
						acc.push(item);
					}
					return acc;
				}, []);
		},
		updateContentConfig() {
			this.contentConfig.fullComp = this.filterComp(this.contentConfig.components);
		},
		updateStyleConfig() {
			this.styleConfig.fullComp = this.filterComp(this.styleConfig.components);
		}
	},
};
</script>

<style scoped lang="scss"></style>
