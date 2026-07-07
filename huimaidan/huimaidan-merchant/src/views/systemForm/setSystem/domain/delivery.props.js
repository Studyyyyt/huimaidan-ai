import { DELIVERY_TYPE } from './delivery.enum';

export const DELIVERY_TYPE_LIST = [
  {
    value: DELIVERY_TYPE.IN_STORE + '',
    name: '到店自提'
  },
  {
    value: DELIVERY_TYPE.EXPRESS + '',
    name: '快递配送'
  },
  {
    value: DELIVERY_TYPE.CITY_DELIVERY + '',
    name: '同城配送'
  }
];