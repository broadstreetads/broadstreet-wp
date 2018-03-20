<script src="https://broadstreet-common.s3.amazonaws.com/broadstreet-net/init.js"></script>
<script>window.bs_bootstrap = <?php echo json_encode($data) ?>;</script>
<div id="main" ng-app="bs_zones">
      <?php Broadstreet_View::load('admin/global/header') ?>
      <div class="left_column" ng-controller="ZoneCtrl">
         <?php if($errors): ?>
             <div class="box">
                    <div class="shadow_column">
                        <div class="title" style="padding-left: 27px; background: #F1F1F1 url('<?php echo Broadstreet_Utility::getImageBaseURL(); ?>info.png') no-repeat scroll 7px center;">
                            Alerts
                        </div>
                        <div class="content">
                            <p>
                                Nice to have you! We've noticed some things you may want to take
                                care of:
                            </p>
                            <ol>
                                <?php foreach($errors as $error): ?>
                                    <li><?php echo $error; ?></li>
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
                <div class="title">Additional Zone Options</div>
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
                                <a target="_blank" href="http://broadstreetads.com/ad-platform/ad-formats/">Not sure what this? Broadstreet is also an adserver.</a>
                            </div>
                        </div>
                        <div class="save-container">
                            <span class="success" id="save-success">Saved!</span>
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
            var bootstrap = window.bs_bootstrap;
            $scope.loadingMessage = null;

            $scope.positions = [
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
                }
            ];

            var zoneList = Object.values(bootstrap.zones);
            $scope.data = {
                zones: zoneList.sort(function(a, b) {
                    return a.name.toLowerCase().localeCompare(b.name.toLowerCase());
                }),
                positions_zones: bootstrap.placements
            };

            var catList = [], found = false;
            for(var i = 0; i < bootstrap.categories.length; i++) {
                if (angular.isArray($scope.data.positions_zones.avoid_categories)) {
                    for(var j = 0; j < $scope.data.positions_zones.avoid_categories.length; j++) {
                        if (bootstrap.categories[i].cat_ID == $scope.data.positions_zones.avoid_categories[j].id) {
                            found = true;
                        }
                    }

                    catList.push({name: bootstrap.categories[i].cat_name, id: bootstrap.categories[i].cat_ID, selected: found, ticked: found});

                    found = false;
                } else {
                    catList.push({name: bootstrap.categories[i].cat_name, id: bootstrap.categories[i].cat_ID, selected: false, ticked: false});                    
                }
            }

            $scope.data.categories = catList;

            $scope.save = function() {
                console.log($scope.data.positions_zones);
                $scope.loadingMessage = 'Saving ...';
                var params = $scope.data.positions_zones;
                $http.post(window.ajaxurl + '?action=save_zone_settings', params)
                    .success(function(response) {
                        $scope.loadingMessage = null;
                   }).error(function(response) {
                        $scope.loadingMessage = null;
                        alert('There was an error saving the zone information! Try again.');
                   });
            }

            console.log();
        });
    })()

</script>