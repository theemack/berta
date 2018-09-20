export class UpdateShopProductAction {
  static readonly type = 'SHOP_PRODUCT:UPDATE';
  constructor(
    public uniqid: string,
    public payload: {
      field: string,
      value: any
    }) {
  }
}
