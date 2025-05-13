<?php
// File: Broadstreet/Views/admin/zones.php

// Initialize a variable to hold any caught error message during rendering.
$zones_page_render_error = null;

try {
    // --- START OF ORIGINAL PAGE CONTENT ---
?>
<script>window.bs_bootstrap = <?php echo json_encode($data); ?>;</script>
<div id="main" ng-app="bs_zones">
      <?php Broadstreet_View::load('admin/global/header') ?>
      <div class="left_column" ng-controller="ZoneCtrl">
         <?php
         // Display pre-existing errors passed to the view
         if (!empty($errors) && is_array($errors)):
         ?>
             <div class="box">
                    <div class="shadow_column">
                        <div class="title" style="">
                            <span class="dashicons dashicons-warning"></span> Alerts
                        </div>
                        <div class="content">
                            <p>
                                We've noticed some things you may want to take care of:
                            </p>
                            <ol>
                                <?php foreach($errors as $error_item): ?>
                                    <li><?php echo htmlspecialchars((string)$error_item); ?></li>
                                <?php endforeach; ?>
                            </ol>
                        </div>
                    </div>
                    <div class="shadow_bottom"></div>
             </div>
         <?php endif; ?>
               <div><strong>Please note:</strong> These options will work with <em>most</em> themes,
               but not all of them, due to non-standard behavior of some themes. Remember, you can
               create new zones specifically for these placements in our dashboard.</div>
          <div id="controls">
            <div class="box">
                <div class="title"><span class="dashicons dashicons-admin-generic"></span> Additional Zone Options</div>
                <div class="content">
                    <div ng-repeat="position in positions track by position.id">
                        <div class="option">
                            <div class="control-label">
                                <div class="name nomargin">
                                    {{position.name}}
                                </div>
                                <div class="desc nomargin">
                                    {{position.description}}
                                </div>
                            </div>
                            <div class="control-container">
                                <select type="text" ng-options="zone.id as zone.name for zone in data.zones" ng-model="data.positions_zones[position.id]">
                                    <option value="">None</option>
                                </select>
                            </div>
                            <div style="clear:both;"></div>
                        </div>
                        <div style="margin-top: 7px;" ng-if="data.positions_zones[position.id]" ng-repeat="param in position.params track by position.params.id" class="option">
                            <div class="control-label">
                                <div class="name nomargin">
                                    {{param.name}}
                                </div>
                                <div class="desc nomargin">
                                    {{param.description}}
                                </div>
                            </div>

                            <div class="control-container">
                                <input ng-model="data.positions_zones[position.id + '_' + param.id]" type="text"  placeholder="{{param.default_value}}" />
                            </div>
                            <div style="clear:both;"></div>
                        </div>
                        <div class="break"></div>
                    </div>
                    <div class="option">
                        <div class="control-label">
                            <div class="name nomargin">
                                Advertisements Label
                            </div>
                            <div class="desc nomargin">
                                If set, this text will appear above injected ads to let readers
                                know it's an ad and not part of the content
                            </div>
                        </div>
                        <div class="control-container">
                           <input ng-model="data.positions_zones.show_label" type="text" />
                        </div>
                    </div>
                    <div class="clearfix"></div>
                    <div class="break"></div>
                    <div class="option">
                        <div class="control-label">
                            <div class="name nomargin">
                                Max Width (Outside of Loop)
                            </div>
                            <div class="desc nomargin">
                                If your ads in the "In Between Posts" or "Before Comments"
                                are are too wide, try to tweak this to something like '500px'
                            </div>
                        </div>
                        <div class="control-container">
                           <input ng-model="data.positions_zones.max_width" type="text" placeholder="100%" />
                        </div>
                    </div>
                    <div class="clearfix"></div>
                    <div class="break"></div>
                    <div class="option">
                        <div class="control-label">
                            <div class="name nomargin">
                                Categories to Avoid
                            </div>
                            <div class="desc nomargin">
                                Don't place in-story ad units in these categories
                            </div>
                        </div>
                        <div class="control-container">
                            <div
                                isteven-multi-select
                                input-model="data.categories"
                                output-model="data.positions_zones.avoid_categories"
                                button-label="icon name"
                                item-label="icon name maker"
                                tick-property="ticked">
                            </div>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                    <div class="break"></div>
                    <div class="option">
                        <div class="control-label">
                            <div class="name nomargin">
                                URLs to Avoid
                            </div>
                            <div class="desc nomargin">
                                Don't place in-story ad units on pages with URLs matching these patterns.
                                Use asterisks (*) as wilcards. Patterns will be matched against full URLs.
                                Regular expressions supported.
                            </div>
                        </div>
                        <div class="control-container">
                            <textarea placeholder="*/example-category/*" ng-model="data.positions_zones.avoid_urls" style="width: 250px; height: 100px;"></textarea>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                    <div class="break"></div>
                    <div class="option">
                        <div class="control-label">
                            <div class="name nomargin">
                                Adserver Whitelabel
                            </div>
                            <div class="desc nomargin">
                                <strong>DO NOT</strong> set this if you are unfamiliar with what it does.
                            </div>
                        </div>
                        <div class="control-container">
                            <input ng-model="data.positions_zones.adserver_whitelabel" type="text" placeholder="content.yourdomain.com" />
                        </div>
                    </div>
                    <div class="clearfix"></div>
                    <div class="break"></div>
                    <div class="option">
                        <div class="control-label">
                            <div class="name nomargin">
                                CDN Whitelabel
                            </div>
                            <div class="desc nomargin">
                                <strong>DO NOT</strong> set this if you are unfamiliar with what it does.
                            </div>
                        </div>
                        <div class="control-container">
                            <input ng-model="data.positions_zones.cdn_whitelabel" type="text" placeholder="assets.yourdomain.com" />
                        </div>
                    </div>
                    <?php if (Broadstreet_Utility::isNewspack()) :?>,
                        <div class="clearfix"></div>
                        <div class="break"></div>
                        <div class="option">
                            <div class="control-label">
                                <div class="name nomargin">
                                    Newspack: Ignore AMP Settings
                                </div>
                                <div class="desc nomargin">
                                    Use normal zone tags instead of AMP-specific tags, even if loaded in an AMP context.
                                </div>
                            </div>
                            <div class="control-container">
                                <input type="checkbox" ng-model="data.positions_zones.newspack_ignore_amp" />
                            </div>
                            <div style="clear:both;"></div>
                        </div>
                    <?php endif; ?>
                    <div class="clearfix"></div>
                    <div class="break"></div>
                    <div class="option">
                        <div class="control-label">
                            <div class="name nomargin">
                                Old Ad Tags
                            </div>
                            <div class="desc nomargin">
                                Would you like to use Broadstreet's old ad tags, as opposed to the new <a href="http://information.broadstreetads.com/using-broadstreets-v2-ad-tags/">async ad tags</a>?
                            </div>
                        </div>
                        <div class="control-container">
                            <input type="checkbox" ng-model="data.positions_zones.use_old_tags" />
                        </div>
                        <div style="clear:both;"></div>
                    </div>
                    <div class="clearfix"></div>
                    <div class="break"></div>
                    <div class="option">
                        <div class="control-label">
                            <div class="name nomargin">
                                Load Initialization Script in &lt;head&gt;
                            </div>
                            <div class="desc nomargin">
                                Load <code>init</code> tags in the header of the page.
                                This is Useful for situations where other scripts "hang" ad loading.</small>
                            </div>
                        </div>
                        <div class="control-container">
                            <input type="checkbox" ng-model="data.positions_zones.load_in_head" />
                        </div>
                        <div style="clear:both;"></div>
                    </div>
                    <div class="clearfix"></div>
                    <div class="break"></div>
                    <div class="option">
                        <div class="control-label">
                            <div class="name nomargin">
                                Defer Configuration
                            </div>
                            <div class="desc nomargin">
                                Only enable this if Broadstreet personnel asks you to (otherwise, your ads won't load without the right setup)
                            </div>
                        </div>
                        <div class="control-container">
                            <input type="checkbox" ng-model="data.positions_zones.defer_configuration" />
                        </div>
                        <div style="clear:both;"></div>
                    </div>
                    <div class="clearfix"></div>
                    <div class="break"></div>
                    <div class="option">
                        <div class="control-label">
                            <div class="name nomargin">
                                Web Analytics (Beta)
                            </div>
                            <div class="desc nomargin">
                                Enable this for website analytics, a simple and free alternative to Google Analytics. This can safely run alongside Google Analytics too.
                            </div>
                        </div>
                        <div class="control-container">
                            <input type="checkbox" ng-model="data.positions_zones.enable_analytics" />
                        </div>
                        <div style="clear:both;"></div>
                    </div>
                    <?php if (defined('WP_PLUGIN_URL') && (strstr(WP_PLUGIN_URL, 'localhost') || strstr(WP_PLUGIN_URL, '127.0.0.1')) ): ?>
                    <div class="clearfix"></div>
                    <div class="break"></div>
                    <div class="option">
                        <div class="control-label">
                            <div class="name nomargin">
                                Use Local Broadstreet API
                            </div>
                            <div class="desc nomargin">
                                Enable if your name is Kenny and you're testing locally
                            </div>
                        </div>
                        <div class="control-container">
                            <input type="checkbox" ng-model="data.positions_zones.use_local_bsa" />
                        </div>
                        <div style="clear:both;"></div>
                    </div>
                    <?php endif; ?>
                    <div class="clearfix"></div>
                    <div class="break"></div>
                    <div class="option">
                        <div class="control-label">
                            <div class="name nomargin">
                                Custom Selector
                            </div>
                            <div class="desc nomargin">
                                If provided, this will replace the default 'broadstreet-zone' tag in ad code.
                            </div>
                        </div>
                        <div class="control-container">
                            <input ng-model="data.positions_zones.custom_selector" type="text" placeholder="broadstreet-zone" />
                        </div>
                        <div style="clear:both;"></div>
                    </div>
                    <div class="break"></div>
                    <div class="option">
                        <div class="control-label">
                            <div class="name nomargin">
                                Ad Tag Init Arguments (Optional, JSON)
                            </div>
                            <div class="desc nomargin">
                                You can specify (optional) arguments here.
                            </div>
                        </div>
                        <div class="full-control-container" style="clear: both; display: block;">
                            <textarea placeholder="{ ... }" ng-model="data.positions_zones.beta_tag_arguments" style="width: 100%; height: 100px;"></textarea>
                        </div>
                        <div style="clear:both;"></div>
                    </div>
                    <div class="break"></div>
                    <div class="option">
                        <div class="control-label">
                            <div class="name nomargin">
                                <a target="_blank" href="https://broadstreetads.com/ad-platform/ad-formats/">Not sure what this is? Broadstreet is also an adserver.</a>
                            </div>
                        </div>
                        <div class="save-container">
                            <span class="success" id="save-success" style="display:none;">Saved!</span>
                            <input type="button" value="Save" name="" ng-click="save()" />
                        </div>
                    </div>
                    <div class="clearfix"></div>
                </div>
            </div>
        </div>
        <div class="selfie-loading-box" ng-show="loadingMessage !== null">
            <img src="<?php echo Broadstreet_Utility::getImageBaseURL() . 'ajax-loader-bar.gif'; ?>" alt="Loading Image"/>
            <span>{{loadingMessage}}</span>
        </div>
      </div>
      <div class="right_column">
          <?php Broadstreet_View::load('admin/global/sidebar') ?>
      </div>
    </div>
      <div class="clearfix"></div>
<script>
    (function() {
        var app = angular.module('bs_zones', ['isteven-multi-select']);

        app.controller('ZoneCtrl', function($scope, $http) {
            var bootstrap = window.bs_bootstrap || {}; // Ensure bootstrap is an object
            $scope.loadingMessage = null;

            $scope.positions = [
                {
                    id: 'above_page',
                    name: 'Above Page',
                    description: 'Which zone should at the very top of the page, full width?'
                },                
                {
                    id: 'above_content',
                    name: 'Above Content',
                    description: 'Which zone should appear above the story?'
                },
                {
                    id: 'below_content',
                    name: 'Below Content',
                    description: 'Which zone should appear below the story?'
                },
                {
                    id: 'in_content',
                    name: 'Inside Content',
                    description: 'Which zone should appear in the middle of the story?',
                    params: [{
                        id: 'paragraph',
                        name: 'Paragraph',
                        description: 'After which paragraph should the zone appear? You can comma separate for multiple positions. Default is 4.',
                        default_value: 4
                    }]
                },
                {
                    id: 'before_comments',
                    name: 'Before Comments',
                    description: 'Which zone should appear right before the comments?'
                },
                {
                    id: 'inbetween_archive',
                    name: 'In Between Posts (Archive)',
                    description: 'Which zone should appear in between posts on the archive page?'
                }<?php if (Broadstreet_Utility::isNewspack()) :?>,
                {
                    id: 'newspack_after_header',
                    name: 'Newspack: After Header',
                    description: 'A zone to go after the header section and nav section',
                    params: [{
                        id: 'padding',
                        name: 'Padding',
                        description: 'Padding to apply to the area around this ad.',
                        default_value: 25
                    }]
                },
                {
                    id: 'newspack_before_footer',
                    name: 'Newspack: Before Footer',
                    description: 'A zone to go before the footer area',
                    params: [{
                        id: 'padding',
                        name: 'Padding',
                        description: 'Padding to apply to the area around this ad.',
                        default_value: 25
                    }]
                },
                {
                    id: 'newspack_before_title',
                    name: 'Newspack: Before Article Title',
                    description: 'A zone to go above the title on articles without featured images'
                },
                {
                    id: 'amp_sticky',
                    name: 'Newspack: AMP Sticky Zone',
                    description: 'A zone (displayed at 300x100) to appear at the footer of a page'
                }<?php endif; ?>,
                {
                    id: 'in_rss_feed',
                    name: 'In RSS Feed',
                    description: 'Embed zone position information in the <source> tag in your RSS feed. This may have an adverse impact on your feed if not used correctly.'
                }
            ];

            var zoneList = [];
            if (bootstrap.zones && typeof bootstrap.zones === 'object') {
                zoneList = Object.values(bootstrap.zones);
            }
            
            $scope.data = {
                zones: zoneList.sort(function(a, b) {
                    var nameA = (a.name || "").toLowerCase();
                    var nameB = (b.name || "").toLowerCase();
                    return nameA.localeCompare(nameB);
                }),
                positions_zones: bootstrap.placements || {}
            };

            var catList = [], found = false;
            if (bootstrap.categories && angular.isArray(bootstrap.categories)) {
                for(var i = 0; i < bootstrap.categories.length; i++) {
                    found = false; // Reset found for each category
                    if ($scope.data.positions_zones && angular.isArray($scope.data.positions_zones.avoid_categories)) {
                        for(var j = 0; j < $scope.data.positions_zones.avoid_categories.length; j++) {
                            // Ensure properties exist before comparison
                            if (bootstrap.categories[i] && bootstrap.categories[i].cat_ID !== undefined &&
                                $scope.data.positions_zones.avoid_categories[j] && $scope.data.positions_zones.avoid_categories[j].id !== undefined &&
                                bootstrap.categories[i].cat_ID == $scope.data.positions_zones.avoid_categories[j].id) {
                                found = true;
                                break; // Found a match, no need to check further for this category
                            }
                        }
                    }
                    // Ensure category item has necessary properties
                    var catName = (bootstrap.categories[i] && bootstrap.categories[i].cat_name) ? bootstrap.categories[i].cat_name : 'Unknown Category';
                    var catID = (bootstrap.categories[i] && bootstrap.categories[i].cat_ID !== undefined) ? bootstrap.categories[i].cat_ID : null;
                    if (catID !== null) {
                       catList.push({name: catName, id: catID, selected: found, ticked: found});
                    }
                }
            }

            $scope.data.categories = catList;

            $scope.save = function() {
                console.log('Saving settings:', $scope.data.positions_zones);
                $scope.loadingMessage = 'Saving ...';
                var params = $scope.data.positions_zones;
                $http.post(window.ajaxurl + '?action=save_zone_settings', params)
                    .success(function(response) {
                        $scope.loadingMessage = null;
                        var saveSuccessEl = document.getElementById('save-success');
                        if (saveSuccessEl) {
                            saveSuccessEl.style.display = 'inline';
                            setTimeout(function() { saveSuccessEl.style.display = 'none'; }, 3000);
                        }
                   }).error(function(response) {
                        $scope.loadingMessage = null;
                        alert('There was an error saving the zone information! Please try again.');
                   });
            };
        });
    })()
</script>
<?php
    // --- END OF ORIGINAL PAGE CONTENT ---
} catch (Throwable $e) { // Catch any throwable error or exception (PHP 7+)
    $zones_page_render_error = "<strong>A critical error occurred while rendering the page content:</strong><br>"
                             . htmlspecialchars($e->getMessage())
                             . "<br><br><strong>File:</strong> " . htmlspecialchars($e->getFile())
                             . "<br><strong>Line:</strong> " . htmlspecialchars($e->getLine())
                             . "<br><br><pre><strong>Trace:</strong>\n" . htmlspecialchars($e->getTraceAsString()) . "</pre>";

    // Log this error to the server's error log for backend debugging
    if (function_exists('error_log')) {
        error_log("Broadstreet Plugin Error in Broadstreet/Views/admin/zones.php: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
    }
}

// If a rendering error occurred, display it.
// This will be shown even if part of the page above the error point was already output.
if ($zones_page_render_error) {
    echo '<div style="border: 3px solid red; padding: 15px; margin: 15px; background-color: #fff0f0; color: #a00; font-family: sans-serif;">';
    echo '<h2><span class="dashicons dashicons-warning" style="vertical-align: middle;"></span> Page Rendering Error</h2>';
    echo '<p>' . $zones_page_render_error . '</p>';
    echo '</div>';
}
?>