import { zoneAgentSettlementReviewUpdateApi, zoneAgentSettlementReviewTransferApi, zoneAgentSettlementReviewPlatformRemarkApi, agentSettlementRevokeApi, zoneAgentSettlementReviewRemarkApi } from '@/api/accounts';
import { TABLE_ACTION } from '../domain/settlement.actions.js';
import { generateActionList } from '../domain/settlement.rules.js';
import { ROLE } from '../domain/settlement.enum.js';
import { AGENT_STATUS } from '@/views/businessZones/domain/agent.enum.js';

export default {
  created() {
    this._handlerMap = {
      [TABLE_ACTION.DETAIL]: settlement => () => this.handleOpenDetailDialog(settlement),
      [TABLE_ACTION.PASS]: settlement => () => this.handlePassReview(settlement),
      [TABLE_ACTION.REJECT]: settlement => () => this.handleOpenRejectDialog(settlement),
      [TABLE_ACTION.TRANSFER]: settlement => () => this.handleOpenTransferDialog(settlement),
      [TABLE_ACTION.PLATFORM_REMARK]: settlement => () => this.handleOpenRemarkDialog(settlement, "platform"),
      [TABLE_ACTION.AGENT_REMARK]: settlement => () => this.handleOpenRemarkDialog(settlement, "agent"),
      [TABLE_ACTION.CANCEL]: settlement => () => this.handleCancelApply(settlement),
    };
  },
  methods: {
    // 处理详情面板操作
    handleDetailChange({ type, settlement }) {
      const func = this._handlerMap[type];
      func && func(settlement)();
    },
    // 生成审核按钮列表
    getActionList(record) {
      const handlerMap = this._handlerMap;

      const actionList = generateActionList(record, this.ROLE);

      actionList.unshift({
        text: "详情",
        action: TABLE_ACTION.DETAIL,
      });

      actionList.forEach(item => {
        item.action = handlerMap[item.action](record)
      });

      actionList[actionList.length - 1].isLast = true;
      return actionList;
    },
    // 打开详情面板
    handleOpenDetailDialog(settlement) {
      this.$refs.settlementDetail.open(settlement.checkout_id);
    },
    // 打开转账面板
    handleOpenTransferDialog(record) {
      this.$refs.formDialog.open({
        title: "转账",
        rule: [
          {
            type: "upload",
            field: "transfer_voucher",
            title: "转账凭证：",
            props: {
              limit: 9,
              multiple: true,
              placeholder: "请上传转账凭证",
              accept: "image/*"
            },
            validate: [
              {
                min: 0,
                max: 9,
                message: "最多支持上传9张凭证",
                type: "array",
                trigger: "change"
              }
            ],
            $required: true
          },
          {
            type: "FormDialogTip",
            name: "tip",
            props: {
              tip: "最多支持上传9张凭证"
            }
          },
          {
            type: "input",
            field: "transfer_remark",
            title: "备注：",
            props: {
              type: "textarea",
              placeholder: "请输入备注",
              rows: 5
            }
          }
        ],
        action: formData => this.handleTransfer(record, formData)
      });
    },
    // 通过审核
    async handlePassReview(record) {
      try {
        await this.$confirm('您确定要通过此提成审核吗？', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning',
        });
      } catch (error) {
        return;
      }

      try {
        const res = await zoneAgentSettlementReviewUpdateApi(record.checkout_id, {
          audit_status: AGENT_STATUS.APPROVED
        });
        this.$message.success(res.message);
        this.refreshData();
      } catch (error) {
        this.$message.error(error.message);
      }

    },
    // 打开拒绝审核面板
    handleOpenRejectDialog(record) {
      this.$refs.formDialog.open({
        title: "审核拒绝",
        rule: [
          {
            type: "input",
            field: "audit_reason",
            title: "拒绝原因：",
            props: {
              type: "textarea",
              placeholder: "请输入拒绝原因",
              rows: 5
            },
            $required: true
          }
        ],
        action: formData => this.handleRejectReview(record, formData)
      });
    },

    // 打开备注面板
    handleOpenRemarkDialog(record, role) {
      this.$refs.formDialog.open({
        title: "备注",
        rule: [
          {
            type: "input",
            field: role === ROLE.PLATFORM ? "platform_remark" : "remark",
            title: "备注：",
            props: {
              type: "textarea",
              placeholder: "请输入备注",
              rows: 5
            }
          }
        ],
        action: formData => this.handleConfirmRemark(record, formData, role)
      });
    },

    refreshData() {
      this.handleGetList && this.handleGetList();
      this.handleDetailFresh && this.handleDetailFresh();
    },

    // 确认备注
    async handleConfirmRemark(record, formData, role) {
      const api = role === ROLE.PLATFORM ? zoneAgentSettlementReviewPlatformRemarkApi : zoneAgentSettlementReviewRemarkApi;
      try {
        const res = await api(record.checkout_id, formData);
        this.$message.success(res.message);
        this.refreshData();
        return true;
      } catch (error) {
        this.$message.error(error.message);
      }
    },


    // 拒绝审核
    async handleRejectReview(record, formData) {
      try {
        const res = await zoneAgentSettlementReviewUpdateApi(record.checkout_id, {
          audit_status: AGENT_STATUS.REJECTED,
          audit_reason: formData.audit_reason
        });
        this.$message.success(res.message);
        this.refreshData();
        return true;
      } catch (error) {
        this.$message.error(error.message);
      }
    },


    // 转账
    async handleTransfer(record, formData) {
      try {
        const res = await zoneAgentSettlementReviewTransferApi(record.checkout_id, formData);
        this.$message.success(res.message);
        this.refreshData();
        return true;
      } catch (error) {
        this.$message.error(error.message);
      }
    },

    // 撤销申请
    async handleCancelApply(record) {
      try {
        await this.$confirm('您确定要撤销此提成审核吗？', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning',
        });
      } catch {
        return;
      }

      try {
        const result = await agentSettlementRevokeApi(record.checkout_id);
        this.$message.success(result.message);
        this.refreshData();
      } catch (error) {
        this.$message.error(error.message);
      }

    },
  }
}