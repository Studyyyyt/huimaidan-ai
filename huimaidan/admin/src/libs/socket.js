// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

import { wss, getCookies, setCookies } from '@/libs/util';
import Setting from '@/setting';
// import { getWorkermanUrl } from '@/api/kefu';
import Vue from 'vue';
const vm = new Vue();
let wsAdminSocketUrl = getCookies('WS_ADMIN_URL') || '';
let wsKefuSocketUrl = getCookies('WS_CHAT_URL') || '';

class wsSocket {
  constructor(opt) {
    this.ws = null;
    this.opt = opt || {};
    this.reconnectAttempts = 0;
    this.maxReconnectAttempts = 5;
    this.init(opt.key);
  }

  onOpen(key = false) {
    this.opt.open && this.opt.open();
    let that = this;
    that.ping();
    this.socketStatus = true;
  }

  init(key) {
    let wsUrl = '';
    if (key == 1) {
      wsUrl = wsAdminSocketUrl;
    }
    if (key == 2) {
      wsUrl = wsKefuSocketUrl;
    }
    if (wsUrl) {
      try {
        this.ws = new WebSocket(wsUrl);
        this.ws.onopen = this.onOpen.bind(this);
        this.ws.onerror = this.onError.bind(this);
        this.ws.onmessage = this.onMessage.bind(this);
        this.ws.onclose = this.onClose.bind(this);
      } catch (e) {
        this.handleReconnect();
      }
    }
  }
  handleReconnect() {
    if (this.reconnectAttempts < this.maxReconnectAttempts) {
      this.reconnectAttempts++;
      setTimeout(() => {
        this.init(this.opt.key);
      }, 3000 * this.reconnectAttempts);
    }
  }
  ping() {
    if (!this.ws || this.ws.readyState !== WebSocket.OPEN) return;

    this.timer = setInterval(() => {
      if (this.ws.readyState === WebSocket.OPEN) {
        this.send({ type: 'ping' }).catch(() => {
          clearInterval(this.timer);
          this.handleReconnect();
        });
      }
    }, 10000);
  }
  destroy() {
    clearInterval(this.timer);
    this.ws && this.ws.close();
    this.ws = null;
  }
  send(data) {
    return new Promise((resolve, reject) => {
      try {
        this.ws.send(JSON.stringify(data));
        resolve({ status: true });
      } catch (e) {
        reject({ status: false });
      }
    });
  }

  onMessage(res) {
    this.opt.message && this.opt.message(res);
  }

  onClose() {
    this.timer && clearInterval(this.timer);
    this.opt.close && this.opt.close();
  }

  onError(e) {
    this.opt.error && this.opt.error(e);
  }

  $on(...args) {
    vm.$on(...args);
  }
}

function createSocket(key) {
  return new Promise((resolve, reject) => {
    const ws = new wsSocket({
      key,
      open() {
        resolve(ws);
      },
      error(e) {
        reject(e);
      },
      message(res) {
        const { type, data = {} } = JSON.parse(res.data);
        vm.$emit(type, data);
      },
      close(e) {
        vm.$emit('close', e);
      },
    });
  });
}

export const adminSocket = createSocket(1);
export const Socket = createSocket(2);
