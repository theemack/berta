import { AppStateModel } from './app-state.interface';

export class AppShowOverlay {
  static readonly type = 'OVERLAY:SHOW';
}

export class AppHideOverlay {
  static readonly type = 'OVERLAY:HIDE';
}

export class AppShowLoading {
  static readonly type = 'LOADING:SHOW';
}

export class AppHideLoading {
  static readonly type = 'LOADING:HIDE';
}

export class ResetAppStateAction {
  static readonly type = 'APP:RESET';
}

export class InitAppStateAction {
  static readonly type = 'APP:INIT';
  constructor(public payload: Partial<AppStateModel>) {}
}
