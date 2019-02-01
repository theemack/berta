import { Component, OnInit } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { SafeHtml, DomSanitizer } from '@angular/platform-browser';
import { Store } from '@ngxs/store';
import { Observable, combineLatest } from 'rxjs';
import { map, filter, scan } from 'rxjs/operators';
import { splitCamel, uCFirst } from '../../shared/helpers';
import { Animations } from '../../shared/animations';
import { SiteSettingsState } from './site-settings.state';
import { SiteSettingsConfigState } from './site-settings-config.state';
import { UpdateSiteSettingsAction } from './site-settings.actions';
import { SettingModel, SettingChildrenModel, SettingConfigModel, SettingGroupConfigModel } from '../../shared/interfaces';


@Component({
  selector: 'berta-site-settings',
  template: `
    <div class="setting-group"
         [class.is-expanded]="camelifySlug(currentGroup) === settingGroup.slug"
         *ngFor="let settingGroup of settings$ | async">
      <h3 [routerLink]="['/settings', slugifyCamel(settingGroup.slug)]" queryParamsHandling="preserve" class="hoverable">
        {{ settingGroup.config.title || settingGroup.slug }}
        <svg class="drop-icon" width="10" height="6" viewBox="0 0 10 6" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M9 1L4.75736 5.24264L0.514719 1" stroke="#9b9b9b" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </h3>
      <div class="settings" [@isExpanded]="camelifySlug(currentGroup) === settingGroup.slug">
        <div *ngFor="let setting of settingGroup.settings">
          <berta-setting *ngIf="!setting.config.children"
                         [setting]="setting.setting"
                         [config]="setting.config"
                         (update)="updateSetting(settingGroup.slug, $event)"></berta-setting>

          <div *ngIf="setting.config.children">
            <div class="setting">
              <h4>{{ setting.config.title }}</h4>
            </div>

            <berta-setting-row *ngFor="let inputFields of setting.children; let index = index"
                               [inputFields]="inputFields"
                               (update)="updateChildren(settingGroup.slug, setting.setting.slug, index, $event)"
                               (delete)="deleteChildren(settingGroup.slug, setting.setting.slug, index)">
            </berta-setting-row>

            <berta-setting-row-add [config]="setting.config.children"
                                   (add)="addChildren(settingGroup.slug, setting.setting.slug, $event)">
            </berta-setting-row-add>

            <div class="setting" *ngIf="setting.config.description">
              <p class="setting-description" [innerHTML]="getSettingDescription(setting.config.description)"></p>
            </div>
          </div>
        </div>
      </div>
    </div>
  `,
  animations: [
    Animations.slideToggle
  ]
})
export class SiteSettingsComponent implements OnInit {
  defaultGroup = 'template';
  currentGroup: string;
  settings$: Observable<Array<{
    config: SettingGroupConfigModel['_'],
    settings: Array<{
      setting: SettingModel,
      config: SettingConfigModel,
      children?: SettingChildrenModel[]
    }>,
    slug: string
  }>>;

  constructor(
    private store: Store,
    private route: ActivatedRoute,
    private sanitizer: DomSanitizer) {
  }

  ngOnInit() {
    this.settings$ = combineLatest(
      this.store.select(SiteSettingsState.getCurrentSiteSettings),
      this.store.select(SiteSettingsConfigState)
    ).pipe(
      filter(([settings, config]) => settings && settings.length > 0 && config && Object.keys(config).length > 0),
      map(([settings, config]) => {
        return settings
          .filter(settingGroup => !config[settingGroup.slug]._.invisible)
          .map(settingGroup => {
            return {
              settings: settingGroup.settings
                .filter(setting => !!config[settingGroup.slug][setting.slug])  // don't show settings that have no config
                .map(setting => {
                  let settingObj: {
                    setting: SettingModel,
                    config: SettingConfigModel,
                    children?: SettingChildrenModel[]
                  } = {
                    setting: setting,
                    config: config[settingGroup.slug][setting.slug]
                  };
                  const childrenConfig = config[settingGroup.slug][setting.slug].children;

                  if (childrenConfig) {
                    const children = (setting.value as any).map(child => {
                      let childObj = {};
                      for (const slug in child) {
                        childObj[slug] = {
                          setting: {
                            slug: slug,
                            value: child[slug]
                          },
                          config: childrenConfig[slug]
                        }
                      }
                      return childObj;
                    });

                    settingObj = { ...settingObj, ...{ children: children } };
                  }

                  return settingObj;
                }),
              config: config[settingGroup.slug]._,
              slug: settingGroup.slug
            };
          });

      }),
      /**
       * settingGroups in this step aren't the ones we get from the store,
       * they are virtual objects created in prev step (the map function)
       */
      scan((prevSettingGroups, settingGroups) => {
        if (!prevSettingGroups || prevSettingGroups.length === 0) {
          return settingGroups;
        }

        return settingGroups.map(settingGroup => {
          const prevSettingGroup = prevSettingGroups.find(psg => {
            return psg.slug === settingGroup.slug &&
              psg.config === settingGroup.config &&
              psg.settings.length === settingGroup.settings.length;
          });

          if (prevSettingGroup) {
            if (settingGroup.settings.some(((setting, index) => prevSettingGroup.settings[index].setting !== setting.setting))) {
              /* Careful, not to mutate anything coming from the store: */
              prevSettingGroup.settings = settingGroup.settings.map(setting => {
                const prevSetting = prevSettingGroup.settings.find(ps => {
                  return ps.setting === setting.setting && ps.config === setting.config;
                });
                if (prevSetting) {
                  return prevSetting;
                }
                return setting;
              });
            }
            return prevSettingGroup;
          }
          return settingGroup;
        });
      })
    );

    this.route.paramMap.subscribe(params => {
      this.currentGroup = params['params']['group'] || this.defaultGroup;
    });
  }

  slugifyCamel(camelText: string) {
    return splitCamel(camelText).map(piece => piece.toLowerCase()).join('-');
  }

  camelifySlug(slug: string) {
    return slug.split('-').map((piece, i) => {
      return i ? uCFirst(piece) : piece;
    }).join('');
  }

  getSettingDescription(text: string): SafeHtml {
    return this.sanitizer.bypassSecurityTrustHtml(text);
  }

  updateSetting(settingGroup: string, updateEvent) {
    const data = { [updateEvent.field]: updateEvent.value };
    this.store.dispatch(new UpdateSiteSettingsAction(settingGroup, data));
  }

  addChildren(settingGroup: string, slug: string, updateEvent) {
    console.log('settingGroup', settingGroup, 'slug', slug, 'updateEvent', updateEvent);
  }

  updateChildren(settingGroup: string, slug: string, index: number,  updateEvent) {
    console.log('settingGroup', settingGroup, 'slug', slug, 'index', index, 'updateEvent', updateEvent);
  }

  deleteChildren(settingGroup: string, slug: string, index: number) {
    console.log('settingGroup', settingGroup, 'slug', slug, 'index', index);
  }
}
