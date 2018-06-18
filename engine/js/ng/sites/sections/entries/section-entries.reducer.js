(function(window, Immutable, ActionTypes) {
  'use strict';

  window.reducers = window.reducers || {};

  Object.assign(window.reducers, {
    sectionEntries: function(state, action) {
      // var entry, entries = [];

      if (state === undefined) {
        state = Immutable.Map();
      }

      switch (action.type) {

        case ActionTypes.SET_STATE:
          return Immutable.fromJS(action.state.sectionEntries);


        case ActionTypes.UPDATE_SECTION_ENTRY:
          var path = action.resp.path.split('/');
          var prop = action.resp.path.split('/').slice(4);
          var siteName = path[0];
          var sectionName = path[2];
          var entryId = path[3];
          var value = action.resp.value;

          var index = state.get(siteName).findIndex(function (entry) {
            return entry.get('id') === entryId && entry.get('sectionName') === sectionName;
          });

          if (index < 0) {
            return state;
          }

          return state.setIn(
            [siteName, index].concat(prop),
            value
          );


        case ActionTypes.DELETE_SITE_SECTIONS_ENTRIES:
          return state.filter(function (entries, site_name) {
            return site_name !== action.data.site_name;
          });


        // case ActionTypes.SECTION_CREATED:
        //   entries = state.getIn([action.resp.site]).toJSON();
        //   entries[action.resp.section.name] = action.resp.entries;

        //   return state.setIn([action.resp.site], Immutable.fromJS(entries));

        // case ActionTypes.SECTION_UPDATED:
        //   if (action.resp.old_name) {
        //     entries = state.getIn([action.resp.site]).toJSON();

        //     if (entries.length) {
        //       entry = entries[action.resp.old_name];
        //       entry['@attributes'].section = action.resp.section.name;
        //       entries[action.resp.section.name] = entry;
        //       delete entries[action.resp.old_name];
        //     }

        //     return state.setIn([action.resp.site], Immutable.fromJS(entries));
        //   }

        //   return state;

        // case ActionTypes.SECTION_DELETED:
        //   entries = state.getIn([action.resp.site]).toJSON();
        //   delete entries[action.resp.name];

        //   return state.setIn([action.resp.site], Immutable.fromJS(entries));

        default:
          return state;
      }
    }
  });
})(window, window.Immutable, window.ActionTypes);
