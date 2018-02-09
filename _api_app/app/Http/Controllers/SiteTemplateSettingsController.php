<?php

namespace App\Http\Controllers;

use App\SiteTemplateSettings;
use Illuminate\Http\Request;

class SiteTemplateSettingsController extends Controller
{

    public function update(Request $request) {
        $json = $request->json()->all();
        $path_arr = explode('/', $json['path']);
        $site = $path_arr[0];
        $template = $path_arr[2];
        $site_template_settings = new SiteTemplateSettings($site, $template);

        $res = $site_template_settings->saveValueByPath($json['path'], $json['value']);
        // @@@:TODO: Replace this with something sensible, when migration to redux is done
        $res['update'] = $res['value'];
        $res['real'] = $res['value'];
        // @@@:TODO:END

        return response()->json($res);
    }

}
