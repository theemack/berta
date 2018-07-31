import { Component, OnInit } from '@angular/core';
import { Store, Select } from '@ngxs/store';
import { Observable } from 'rxjs';
import { SiteStateModel } from './sites-state/site-state.model';
import { UserState } from '../user/user-state';

@Component({
  selector: 'berta-sites',
  template: `
    <h2>Sites</h2>
    <div class="sites">
      <berta-site *ngFor="let site of sites$ | async" [site]="site" (update)="siteUpdated($event)"></berta-site>
    </div>
  `,
  styles: [`
    .sites {
      padding: 5px;
    }
    berta-site {
      margin: 10px 0;
    }
  `]
})
export class SitesComponent implements OnInit {
  @Select(UserState.isLoggedIn) isLoggedIn$;
  @Select('sites') public sites$: Observable<SiteStateModel[]>;

  constructor(private store$: Store) { }

  ngOnInit() {
    this.sites$.subscribe((state) => console.log(state));
  }

  siteUpdated([site, data]) {
    console.log('update: ', site, data);
  }
}
