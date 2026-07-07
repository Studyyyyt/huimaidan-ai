<template>
  <div class="h-full">
    <div class="date-select-box">
      <el-date-picker
        class="date-select"
        v-model="date"
        type="date"
        ref="dateSelect"
        size="small"
        placeholder="选择日期"
        :editable="false"
        value-format="yyyy-MM-dd"
        @change="changeDate"
      >
      </el-date-picker>
    </div>
    <FullCalendar
      ref="fullCalendar"
      :options="calendarOptions"
    >
      <template v-slot:eventContent="arg">
        <el-tooltip>
          <div slot="content">
            <div class="reservation-name flex">
              <div
                class="reservation-type border-color"
                v-if="arg.event.extendedProps.order_type == 1"
              >
                到店
              </div>
              <div
                class="reservation-type border-color"
                v-else-if="arg.event.extendedProps.order_type == 0"
              >
                上门
              </div>
              <div style="display: inline-block" class="name">
                {{ arg.event.extendedProps.real_name }}
              </div>
              <div style="display: inline-block" class="phone text-1">
                {{ arg.event.extendedProps.user_phone }}
                <span v-if="arg.event.extendedProps.order_type == 0">
                  {{ arg.event.extendedProps.user_address }}</span
                >
              </div>
            </div>
            <div class="text-1" style="margin-top: 4px;">
              {{ arg.event.extendedProps.productInfo.store_name }}
            </div>
          </div>
          <div>
            <div class="reservation-name">
              <div
                class="reservation-type"
                v-if="arg.event.extendedProps.order_type == 1"
              >
                到店
              </div>
              <div
                class="reservation-type"
                v-else-if="arg.event.extendedProps.order_type == 0"
              >
                上门
              </div>
              <div style="display: inline-block" class="name">
                {{ arg.event.extendedProps.real_name }}
              </div>
              <div style="display: inline-block" class="phone text-1">
                {{ arg.event.extendedProps.user_phone }}
                <span v-if="arg.event.extendedProps.order_type == 0">
                  {{ arg.event.extendedProps.user_address }}</span
                >
              </div>
            </div>
            <div class="text-1">
              {{ arg.event.extendedProps.productInfo.store_name }}
            </div>
          </div>
        </el-tooltip>
      </template>
    </FullCalendar>
    <div class="float-box">
      <div class="list">
        <div class="item">
          <span
            class="iconfont  mark"
            :class="
              checkBox.includes(1)
                ? 'iconfuxuankuang-xuanzhong'
                : 'iconfuxuankuang-weixuanzhong'
            "
            @click="handleMark(1)"
          />
          待服务
        </div>
        <div class="item">
          <span
            class="iconfont  mark mark2"
            :class="
              checkBox.includes(2)
                ? 'iconfuxuankuang-xuanzhong'
                : 'iconfuxuankuang-weixuanzhong'
            "
            @click="handleMark(2)"
          ></span>
          服务中
        </div>
        <div class="item">
          <span
            class="iconfont mark mark3"
            :class="
              checkBox.includes(3)
                ? 'iconfuxuankuang-xuanzhong'
                : 'iconfuxuankuang-weixuanzhong'
            "
            @click="handleMark(3)"
          ></span>
          已完成
        </div>
      </div>
    </div>
    <!-- 时段设置 -->
    <el-dialog title="时间间隔" :visible.sync="dialogVisible" width="504px">
      <div class="slider">
        <el-slider
          v-if="dialogVisible"
          v-model="sliderValue"
          :min="30"
          :max="180"
          :step="30"
          :marks="marks"
          show-stops
          style="width: 226;white-space: nowrap"
        >
        </el-slider>
      </div>
      <span slot="footer" class="dialog-footer">
        <el-button size="small" @click="dialogVisible = false">取消</el-button>
        <el-button type="primary" size="small" @click="setConfig"
          >确定</el-button
        >
      </span>
    </el-dialog>
  </div>
</template>

<script>
import resourceTimelinePlugin from "@fullcalendar/resource-timeline";
import { getReservationList } from "@/api/order";
export default {
  components: {
    FullCalendar: () => import("@fullcalendar/vue")
  },
  props: {
    tableFrom: {
      type: Object,
      default: () => {}
    }
  },
  data() {
    return {
      marks: {
        30: "30分钟",
        60: "1小时",
        90: "",
        120: "2小时",
        150: "",
        180: "3小时"
      },
      sliderValue: 30,
      checkBox: [1, 2, 3],
      dialogVisible: false,
      formValidate: {
        is_show_staff_switch: 1,
        chart_time_interval: 30
      },
      date: "", // 当前日期
      isShowDateSelect: false, // 是否显示日期选择器
      calendarApi: {}, // 日历api
      calendarOptions: {
        height: "calc(100vh - 276px)",
        slotMinWidth: 90,
        eventMaxStack: 50,
        moreLinkContent: {
          html:
            '<a>全部预约<span class="fc-icon fc-icon-chevron-right"></span></a>'
        },
        // moreLinkClick: this.handleMoreLinkClick,
        locale: "zh-cn",
        plugins: [resourceTimelinePlugin],
        initialView: "resourceTimelineDay",
        aspectRatio: 3,
        headerToolbar: {
          left: "addEventButton,prev,title,next,volume,number",
          right: "setup"
        },
        customButtons: {
          setup: {
            text: "时间设置",
            click: event => {
              this.showPopover();
            }
          },
          volume: {
            text: "当日预约量"
          },
          number: {
            text: "0"
          },
          addEventButton: {
            click: event => {
              this.$refs.dateSelect.focus();
            }
          }
        },
        slotLabelFormat: {
          hour: "2-digit", // 两位数小时（如 10）
          minute: "2-digit", // 两位数分钟（如 00）
          hour12: false, // 24小时制
          omitZeroMinute: false, // 不省略分钟（强制显示:00）
          locale: "zh-cn" // 本地化（需加载中文语言包）
        },
        nowIndicator: true,
        editable: false,
        droppable: false,
        resourceAreaWidth: "15%",
        scrollTime: "9:00",
        resourceAreaHeaderContent: "",
        resources: [],
        events: [],
        slotDuration: "00:30:00",
        slotLabelInterval: "00:30", // 标签间隔2小时
        datesSet: this.handleDatesSet,
        eventClick: this.handleEventClick,
        schedulerLicenseKey: "GPL-My-Project-Is-Open-Source"
      },
      reservationTime: "",
      where: {
        service_type: "",
        staff_id: "",
        uid: "",
        phone: "",
        nickname: "",
        reservation_date: "",
        reservation_status: [1, 2, 3]
      }
    };
  },
  watch: {},
  beforeDestroy() {
    // 清理事件监听
    document.removeEventListener("click", this.closePopover);
  },
  mounted() {
    this.getConfig();
    this.getList();
  },

  methods: {
    getList() {
      for (let key in this.tableFrom) {
        this.where[key] = this.tableFrom[key];
      }

      getReservationList(this.where).then(res => {
        let data = res.data.list;
        let staff = [];
        let list = [];
        data.forEach(dataItem => {
          staff.push({
            id: dataItem.staffs_id,
            title: dataItem.staffs_id == 0 ? "未分配" : dataItem.name
          });
          dataItem.orders.forEach(item => {
            let color = "#377DFF";
            if (
              (dataItem.order_type == 0 && item.status == 1) ||
              (dataItem.order_type == 1 &&
                (item.status == 1 || item.status == 0))
            ) {
              // s上面 1
              // 到店 01
              color = "##377DFF";
            } else if (item.status == 20) {
              color = "#FF8D30";
            } else if (item.status >= 2) {
              color = "#23C471";
            }
            list.push({
              ...item,
              id: item.order_id,
              staff_name: dataItem.name,
              resourceId: dataItem.staffs_id + "",
              start: this.moment(
                item.productInfo.reservation_date +
                  " " +
                  item.productInfo.reservation_start
              ).format("YYYY-MM-DD HH:mm"),
              end: this.moment(
                item.productInfo.reservation_date +
                  " " +
                  item.productInfo.reservation_end
              ).format("YYYY-MM-DD HH:mm"),
              color
            });
          });
        });
        this.calendarOptions.resources = staff;
        this.$set(this.calendarOptions, "events", list);
        this.calendarOptions.customButtons.number.text = res.data.count + "";
      });
    },
    showPopover() {
      this.dialogVisible = true;
    },
    handleMark(type) {
      if (this.checkBox.includes(type)) {
        this.checkBox = this.checkBox.filter(item => item != type);
        this.where.reservation_status = this.checkBox;
      } else {
        this.checkBox.push(type);
        this.where.reservation_status = this.checkBox;
      }

      this.getList();
    },

    handleDatesSet(arg) {
      this.where.reservation_date = this.moment(arg.start).format("YYYY-MM-DD");
      this.getList();
      // this.getReservationNoticeBoard();
    },
    handleEventClick({ event }) {
      this.$emit("onOrderDetails", event.extendedProps.order_id);
    },

    // 保存看板配置
    setConfig() {
      localStorage.setItem("sliderValue", this.sliderValue);
      this.dialogVisible = false;
      this.getConfig();
    },
    getConfig() {
      let value = 30;
      if (localStorage.getItem("sliderValue")) {
        value = localStorage.getItem("sliderValue");
      }
      this.sliderValue = Number(value);
      this.calendarOptions.slotDuration = this.moment
        .utc(this.sliderValue * 60 * 1000)
        .format("HH:mm:ss");
      this.calendarOptions.slotLabelInterval = this.moment
        .utc(this.sliderValue * 60 * 1000)
        .format("HH:mm:ss");
    },
    close() {
      this.modal = false;
    },
    handleRefresh() {
      this.isRefresh = true;
      // this.getReservationNoticeBoard();
    },
    handleMoreLinkClick(info) {
      info.jsEvent.preventDefault();
      const currentEvent = info.hiddenSegs[0].event;
      const resources = currentEvent.getResources();
      const rawEvents = resources[0].getEvents();
      const overlapEvents = rawEvents.filter(({ start, end }) => {
        return currentEvent.start <= end && start < currentEvent.end;
      });
      overlapEvents.sort((a, b) => {
        return a.start - b.start;
      });
      let eventList = overlapEvents.map(({ extendedProps, id }) => {
        return {
          ...extendedProps,
          id
        };
      });
      this.$emit("serviceTap", eventList);
    },
    // 更改日期
    changeDate(date) {
      let calendarApi = this.$refs.fullCalendar.getApi();
      calendarApi.gotoDate(date);
    }
  }
};
</script>

<style lang="scss" scoped>
.slider {
  padding: 20px;
  padding-top: 0;
  ::v-deep .el-slider__marks-text {
    font-size: 13px;
    color: #303133;
  }
  ::v-deep .el-slider__stop {
    position: absolute;
    height: 10px;
    width: 2px;
    background-color: #dddddd;
    -webkit-transform: translateY(-50%);
    transform: translateY(-80%);
  }

  ::v-deep .el-slider__runway {
    height: 2px;
    background: #dddddd;
  }
  ::v-deep .el-slider__button {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid #1890ff;
  }
  ::v-deep .el-slider__bar {
    background-color: transparent;
  }
  // ::v-deep .el-slider__stop:nth-child(3) {
  //   display: none !important;
  // }
  // ::v-deep .el-slider__stop:nth-child(5) {
  //   display: none !important;
  // }
}
::v-deep.fc {
  .fc-more-popover .fc-popover-body {
    max-height: 300px;
    overflow-y: scroll;
  }
  .fc-popover {
    z-index: 22;
  }
  .fc-datagrid-cell-frame {
    display: flex;
    align-items: center;
  }
  .fc-datagrid-header {
    background-color: #fcfcfc;
  }
  .fc-datagrid-body {
    background-color: #fcfcfc;
  }
  .fc-timeline-slot-minor {
    border-style: none;
  }
  .fc-event {
    padding: 5px 6px;
    border-radius: 4px;
    white-space: nowrap;
    font-size: 12px;
    overflow: hidden;
  }
  .reservation-name {
    display: flex;
    align-items: center;
    margin-bottom: 2px;
  }
  .reservation-type {
    flex-shrink: 0;
    display: flex;
    justify-content: center;
    align-items: center;
    width: 29px;
    height: 17px;
    border: 1px solid rgba(255, 255, 255, 0.5);
    border-radius: 3px;
    margin-right: 4px;
    font-family: PingFang SC, PingFang SC;
    font-weight: 400;
    font-size: 12px;
    color: #ffffff;
  }

  .fc-prev-button {
    display: inline-flex;
    justify-content: center;
    align-items: center;
    width: 30px;
    height: 30px;
    border: 1px solid #dddddd;
    border-radius: 50% !important;
    background: none;
    font-size: 13px;
    color: #303133;
    vertical-align: middle;
  }
  .fc-prev-button:not(:disabled):active {
    border: 1px solid #dddddd;
    background: none;
    color: #303133;
  }
  .fc-prev-button:focus {
    box-shadow: none;
  }
  .fc-prev-button:not(:disabled):active:focus {
    box-shadow: none;
  }
  .fc-next-button {
    display: inline-flex;
    justify-content: center;
    align-items: center;
    width: 30px;
    height: 30px;
    border: 1px solid #dddddd;
    border-radius: 50% !important;
    background: none;
    font-size: 16px;
    color: #303133;
    vertical-align: middle;
  }
  .fc-next-button:not(:disabled):active {
    border: 1px solid #dddddd;
    background: none;
    color: #303133;
  }
  .fc-next-button:focus {
    box-shadow: none;
  }
  .fc-next-button:not(:disabled):active:focus {
    box-shadow: none;
  }
  .fc-toolbar-title {
    display: inline-flex;
    justify-content: center;
    align-items: center;
    width: 130px;
    font-weight: 500;
    font-size: 16px;
    color: #303133;
    vertical-align: middle;
  }
  .fc-volume-button {
    border: 0;
    background: none;
    font-size: 16px;
    color: #303133;
    cursor: auto;
  }
  .fc-volume-button:not(:disabled):active {
    border: 0;
    background: none;
    color: #303133;
  }
  .fc-volume-button:focus {
    box-shadow: none;
  }
  .fc-volume-button:not(:disabled):active:focus {
    box-shadow: none;
  }
  .fc-number-button {
    padding: 0;
    border: 0;
    background: none;
    font-weight: 500;
    font-size: 16px;
    color: #377dff;
    cursor: auto;
  }
  .fc-number-button:not(:disabled):active {
    border: 0;
    background: none;
    color: #303133;
  }
  .fc-number-button:focus {
    box-shadow: none;
  }
  .fc-number-button:not(:disabled):active:focus {
    box-shadow: none;
  }
  .fc-setup-button {
    display: inline-flex;
    justify-content: center;
    align-items: center;
    width: 100px;
    height: 32px;
    border: 1px solid #1890ff;
    border-radius: 20px;
    background: #1890ff;
    font-size: 13px;
    vertical-align: middle;
  }
  // 日历工具栏
  .fc-toolbar-chunk {
    position: relative;
    .fc-addEventButton-button {
      position: absolute;
      width: 130px;
      height: 36px;
      opacity: 0;
      top: 0;
      left: 30px;
      z-index: 3;
    }
  }
  .fc-setup-button:not(:disabled):active {
    border: 1px solid #1890ff;
    background: #1890ff;
  }
  .fc-setup-button:focus {
    box-shadow: none;
  }
  .fc-setup-button:not(:disabled):active:focus {
    box-shadow: none;
  }
  .fc-timeline-slot-cushion {
    font-weight: 400;
    font-size: 14px;
    color: #303133;
  }
  .fc-datagrid-cell-cushion {
    font-size: 14px;
    color: #303133;
  }
  .fc-timeline-now-indicator-container {
    z-index: auto;
  }
  .fc-timeline-now-indicator-arrow {
    border-top-color: #377dff;
  }
  .fc-timeline-now-indicator-line {
    border-width: 0px 0px 0px 2px;
    border-color: #377dff;
  }
  td {
    border-color: #ededed;
    cursor: auto;
  }
  .fc-scrollgrid-section-header > th:last-child {
    border-color: transparent;
  }
  .fc-scrollgrid-section-body > td:last-child {
    border-color: transparent;
  }
  th {
    border-color: #ededed;
  }
  .fc-scrollgrid {
    border-color: transparent;
  }
  .ivu-tooltip {
    width: 100%;
  }
}
.text-1 {
  overflow: hidden;
  white-space: nowrap;
  text-overflow: ellipsis;
  color: #fff;
}

.name {
  font-family: PingFang SC, PingFang SC;
  font-weight: 400;
  font-size: 12px;
  color: #ffffff;
  margin: 0 4px;
}
.phone {
  color: rgba(255, 255, 255, 0.5);
  font-family: PingFang SC, PingFang SC;
  font-weight: 400;
  font-size: 12px;
}

.float-box {
  position: fixed;
  right: 44px;
  bottom: 60px;
  z-index: 9;
  display: flex;
  align-items: center;
  height: 61px;
  padding: 0 13px 0 26px;
  border-radius: 84px;
  background: #ffffff;
  box-shadow: 0px 4px 16px 0px rgba(0, 0, 0, 0.08);
  .list {
    display: flex;
    align-items: center;
    font-size: 14px;
    color: #303133;
  }
  .item {
    cursor: pointer;
    display: flex;
    align-items: center;
    margin-right: 18px;
  }
  .mark {
    font-size: 14px;
    color: #377dff;
    margin-right: 8px;
    &.mark2 {
      color: #ff8d30;
    }
    &.mark3 {
      color: #23c471;
    }
    &.mark4 {
      color: #f95e45;
    }
  }
}
.h-full {
  position: relative;
  .date-select-box {
    position: absolute;
    top: 0px;
    left: 30px;
    width: 130px;
    cursor: pointer;
    .date-select {
      width: 130px;
      opacity: 0;
      z-index: 2;
    }
  }
}

.ml0 {
  margin-left: 0 !important;
}
</style>
