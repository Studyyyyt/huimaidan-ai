<template>
    <el-popover
      ref="popover"
      placement="bottom-start"
      width="800"
      trigger="click">
        <div class="filter-container">
          <div class="filter-header"><span class="header">筛选条件</span></div>
          <div class="filter-main">
            <div v-for="(item,index) in filterList" :key="index" class="filter-list">
              <div class="filter-count mr-16">{{index+1}}</div>
              <div class="filter-items">
                <div class="term-one mr-16">
                  <el-select
                    :key="index"
                    v-model="item.condition_one"
                    placeholder="请选择"
                    class="width-one"
                    clearable
                    filterable
                    @change="getItemSelect(item,index)"
                    >
                    <el-option
                      v-for="option in conditionList"
                      :key="option.value"
                      :label="option.label"
                      :value="option.value"
                      :disabled="selectedOne&&selectedOne.length>0&&selectedOne.indexOf(option.value.toString())!==-1"
                      />
                  </el-select>
                </div>
                <div class="term-two mr-16">
                  <el-select
                    v-model="item.condition_two"
                    placeholder="请选择"
                    class="width-two"
                    filterable
                    :disabled="!item.condition_one"
                    @change="handleChange(item.condition_two,index)"
                    >
                    <el-option v-for="iOption in item.selectList" :key="iOption.value" :label="iOption.label" :value="iOption.value" />
                  </el-select>
                </div>
                <div v-if="item.children" class="term-three mr-16">
                  <el-select
                    v-show="item.type=='select'"
                    v-model="item.threeArr"
                    placeholder="请选择"
                    class="width-three"
                    clearable
                    filterable
                    multiple
                    :disabled="!item.condition_one || !item.condition_two"
                    >
                    <el-option v-for="tOption in item.options" :key="tOption.value" :label="tOption.label" :value="tOption.value.toString()" />
                  </el-select>
                  <el-input
                    v-show="item.type=='input'"
                    v-model="item.condition_three"
                    @input="forceUpdate(item.condition_three,index)"
                    placeholder="请输入"
                    class="width-three">
                  </el-input>
                  <el-date-picker
                    v-show="item.type=='date_time_picker' && item.value=='between'"
                    v-model="item.dateRange"
                    type="daterange"
                    value-format="yyyy/MM/dd"
                    format="yyyy/MM/dd"
                    class="width-three"
                    range-separator="-"
                    start-placeholder="开始日期"
                    end-placeholder="结束日期"
                    >
                  </el-date-picker>
                  <el-date-picker
                    v-show="item.type=='date_time_picker' && item.value!=='between'"
                    v-model="item.date"
                    :default-value="new Date()"
                    type="date"
                    class="width-three"
                    value-format="yyyy/MM/dd"
                    placeholder="选择日期">
                  </el-date-picker>
                  <template v-if="item.type=='input_number' && item.value=='between'">
                    <el-input-number
                      v-model.lazy="item.three1"
                      :controls="false"
                      style="width: 143px;">
                    </el-input-number>
                      -
                    <el-input-number
                      v-model.lazy="item.three2"
                      :controls="false"
                      style="width: 143px;">
                    </el-input-number>
                  </template>
                  <el-input-number
                    v-show="item.type=='input_number' && item.value!=='between'"
                    v-model.lazy="item.condition_three"
                    :controls="false"
                    placeholder="请输入"
                    class="width-three">
                  </el-input-number>
                </div>
                <div class="filter-delete" @click="deleteFilter(index)">
                  <span class="el-icon-delete"></span>
                </div>
              </div>
            </div>
            <div class="filter-footer">
              <div class="btn-add">
                <span class="el-icon-circle-plus" @click="addIterm"></span>
                <div @click="addIterm">添加条件</div>
              </div>
              <div class="btn-footer">
                <div class="mt14">
                  <span>条件规则：</span>
                  <el-radio-group v-model="match">
                    <el-radio :label="0">符合任一</el-radio>
                    <el-radio :label="1">符合全部</el-radio>
                  </el-radio-group>
                </div>
                <div style="text-align: right;">
                  <el-button size="small" @click="resetFilter">清空数据</el-button>
                  <el-button size="small" type="primary" @click="filterSearch">筛选</el-button>
                </div>
              </div>
            </div>
          </div>
        </div>
      <el-button slot="reference">高级筛选</el-button>
    </el-popover>
</template>

<script>
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
import {userSearchTerms,userSearchTypes} from '@/api/user'
export default {
  name: 'AdvancedFilter',
  props: {

  },
  data() {
    return {
      filterList: [
        {condition_one: null,condition_two:null,condition_three:undefined,threeArr:[],date:"",dateRange:[],selectList:[],type: "select",children:true}
      ],
      conditionList: [],
      match: 0,
      selectedOne: []
    }
  },
  mounted() {
    this.getSearchTerms()
  },
  methods: {
    forceUpdate(value,index) {
      if(value == ' '||value == '')
      this.filterList[index]['condition_three'] = undefined
      this.$forceUpdate()
    },
   addIterm(){
      this.filterList.push({condition_one: null,condition_two:null,condition_three: undefined,threeArr:[],date: "",dateRange: [],selectList:[],type: "select",children:true})
    },
    resetFilter(){
      this.filterList=[]
      this.selectedOne = []
    },
    deleteFilter(index){
      this.filterList.splice(index, 1)
      this.selectedOne = []
      this.filterList.forEach((itm,idx)=>{
        if(itm.condition_one){
          this.selectedOne.push(itm.condition_one)
        }
      })
    },
    /**筛选数据处理 */
    filterSearch(){
      let filter_conditions = []
      this.filterList.forEach((item,index)=>{
        if(item.type == 'date_time_picker'){
          item.condition_three = item.value == "between" ? item.dateRange.join('-') : item.date
        }else if(item.type == "input_number" && item.value == "between"){
          item.condition_three = item.three1+','+item.three2
        }else if(item.type == "select"){
          item.condition_three = item.threeArr.toString()
        }
        filter_conditions.push({
          field_name: item.condition_one,
          operator: item.value,
          value: item.condition_three,
          form_value: item.type,
          boolean: this.match == 0 ? 'or' : 'and'
        })
      })
      this.$refs.popover.doClose();
      this.$emit('getList',filter_conditions)
    },
    /**获取高级筛选搜索项 */
    getSearchTerms(){
      userSearchTerms().then(res => {
        this.conditionList = res.data
      }).catch(res => {
        this.$message.error(res.message)
      })
    },
    getItemSelect(item,index){
      if(item.condition_one){
        userSearchTypes(item.condition_one).then(res => {
          item.selectList = res.data
          item.options = res.data[0].options
          item.condition_two = res.data[0]['value']
          item.children = res.data[0]['children']
          this.handleChange(res.data[0]['value'],index)
        }).catch(res => {
          this.$message.error(res.message)
        })
      }
      this.selectedOne = []
      this.filterList.forEach((itm,idx)=>{
        if(itm.condition_one){
          this.selectedOne.push(itm.condition_one)
        }
      })
    },
    handleChange(value,index){
      const selectedRow = this.filterList[index]['selectList'].find(option => option.value === value);
      this.filterList[index]['type'] = selectedRow.type
      this.filterList[index]['value'] = selectedRow.value
      this.filterList[index]['children'] = selectedRow.children
      this.filterList[index]['condition_three'] = undefined
    },
  }
}
</script>

<style scoped lang="scss">
.filter-container {
  position: relative;
  padding-bottom: 120px;
}
.filter-container .filter-header{
  padding: 12px;
  border-bottom: 1px solid var(--prev-border-color-base);
  .header{
    font-size: 14px;
    line-height: 24px;
    margin: 0px;
  }
}
.filter-main{
  padding: 12px 12px 8px;
}
.mr-16{
  margin-right: 10px;
}
.filter-list{
  display: flex;
  margin-bottom: 14px;
  align-items: center;
  .filter-count{
    color: var(--prev-color-primary);
    border: 1px solid var(--prev-color-primary);
    border-radius: 50%;
    display: block;
    font-size: 14px;
    height: 20px;
    line-height: 20px;
    text-align: center;
    width: 20px;
  }
  .filter-items{
    display: flex;
    align-items: center;
  }
  .filter-delete{
    cursor: pointer;
    span{
      font-size: 16px;
      color: var(--prev-color-primary);
    }
  }
  .width-one{
    width: 192px;
  }
  .width-two{
    width: 142px;
  }
  .width-three{
    width: 300px;
  }
}
::v-deep .el-input-number .el-input__inner {
  text-align: left;
}
.term-three{
  position: relative;
}
.filter-footer{
  position: absolute;
  bottom: 0;
  left: 0;
  width: 100%;
  .btn-add{
    display: flex;
    padding: 12px;
    font-size: 12px;
    border-bottom: 1px solid var(--prev-border-color-base);
    color: var(--prev-color-primary);
    cursor: pointer;
    span{
      font-size: 18px;
      margin-right: 4px;
    }
  }
  .btn-footer{
    padding: 12px;
  }
}
.el-popover{
  padding: 0;
}
</style>
