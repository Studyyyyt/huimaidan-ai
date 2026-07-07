<template>
	<div class="price-format" :style="{justifyContent: align }" :class="weight ? 'semiBold' : 'regular'">
		<span class="intPrice">
			<span :style="{'font-size': labelSize +'px'}">¥</span>
			<span :style="{'font-size': intSize +'px'}">{{ intPrice }}</span>
		</span>
		<span class="floatPrice" :style="{'font-size': floatSize +'px'}">{{ floatPrice }}</span>
	</div>
</template>
<script>
	// +----------------------------------------------------------------------
	// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
	// +----------------------------------------------------------------------
	// | Copyright (c) 2016~2024 https://www.crmeb.com All rights reserved.
	// +----------------------------------------------------------------------
	// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
	// +----------------------------------------------------------------------
	// | Author: CRMEB Team <admin@crmeb.com>
	// +----------------------------------------------------------------------
	export default {
		name: 'PriceFormat',
		props: {
			price: {
				type: String | Number,
				default: ""
			},
			// 粗体
			weight: {
				type: Boolean,
				default: false
			},
			intSize: {
				type: String,
				default: '32'
			},
			floatSize: {
				type: String,
				default: '22'
			},
			labelSize: {
				type: String,
				default: '20'
			},
			align: {
				type: String,
				default: 'flex-start'
			}
		},
		data() {
			return {
				floatPrice: ""
			};
		},
		computed: {
			intPrice() {
				const str = this.price+'' //转成字符串
				const arr = str.split(".") //使用分隔符分割字符串成数组
				let tempStr = arr[1] ? arr[1] : '00' //如果小数点后有值就用该值，没有默认'00'
				this.floatPrice = tempStr.length === 1 ? '.'+tempStr + '0' : '.'+tempStr //小数点后只有一位的话补0
				return arr[0]
			}
		},
		watch: {

		}
	}
</script>

<style lang="scss" scoped>
	.price-format {
		display: flex;
		align-items: baseline;
	}
</style>
