import { State, Action, StateContext, Selector, NgxsOnInit } from '@ngxs/store';
import { SiteStateModel } from './site-state.model';
import { AppStateService } from '../../app-state/app-state.service';
import { take } from 'rxjs/operators';
import { CreateSiteAction, DeleteSiteAction, CloneSiteAction } from './sites.actions';

@State<SiteStateModel[]>({
  name: 'sites',
  defaults: []
})
export class SitesState implements NgxsOnInit {

  constructor(
    private appStateService: AppStateService) {
  }


  ngxsOnInit({ setState }: StateContext<SiteStateModel[]>) {
    this.appStateService.getInitialState('', 'sites').pipe(take(1)).subscribe({
      next: (response) => {
        setState(response as SiteStateModel[]);
      },
      error: (error) => console.error(error)
    });
  }


  @Action(CreateSiteAction)
  createSite({ setState, getState }: StateContext<SiteStateModel[]>) {
    const currentState = getState();
    const newSite: SiteStateModel = {
      // @todo sync with backend
      // @todo get unique name from backend
      name: 'untitled-' + Math.random().toString(36).substr(2, 9),
      title: '',
      order: currentState.length,
      '@attributes': {
        published: 0
      }
    };

    setState(
      [...currentState, newSite]
    );
  }


  @Action(CloneSiteAction)
  cloneSite({ setState, getState }: StateContext<SiteStateModel[]>, action: CloneSiteAction) {
    const currentState = getState();
    const newSite: SiteStateModel = {
      // @todo sync with backend
      // @todo get new site from backend
      // @todo sync related site data
      name: 'clone-of-' + action.site.name,
      title: 'Clone of ' + action.site.title,
      order: currentState.length,
      '@attributes': {
        published: 0
      }
    };

    setState(
      [...currentState, newSite]
    );
  }


  @Action(DeleteSiteAction)
  DeleteSite({setState, getState}: StateContext<SiteStateModel[]>, action: DeleteSiteAction) {
    const currentState = getState();
    // @todo sync with backend
    // @todo delete associated data from state
    setState(currentState.filter(site => site.name !== action.site.name));
  }
}
