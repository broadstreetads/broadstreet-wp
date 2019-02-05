<div id="main">
      <?php Broadstreet_View::load('admin/global/header') ?>
      <div class="help-section left_column">
          <h2>Before You Get Started</h2>
          <p>
              Before you get up and running, you'll need an account with Broadstreet.
              This plugin uses special functionality that isn't typically
              available to Wordpress users, and an account with us provides
              that. To get set up, send a quick email to 
              <a href="mailto:kenny@broadstreetads.com">kenny@broadstreetads.com</a>
              and we'll get you set up. If you have more general questions on
              who Broadstreet is or how to make money with this plugin, see the
              <a href="#faq">FAQ</a> section.
          </p>
          
          <h2>Quick Start</h2>
          
          <p>
              If you're in a rush, this short video will go over the basics of
              getting your business directory up and running quickly.
          </p>
          
          <iframe width="560" height="315" src="http://www.youtube.com/embed/Dv5Z3bL9eUM" frameborder="0" allowfullscreen></iframe>
          
          <h2>Using the Business Directory</h2>
          
          <p>
              First, we'll dicuss how to use the business directory. In order to
              anable the business directory, go to the 
              <a href="?page=Broadstreet">Broadstreet settings page</a>,
              tick the "Enable Business Directory" checkbox, and click 'Save'.
          </p>
          
          <img class="figure" alt="Enable the business directory" src="<?php echo Broadstreet_Utility::getImageBaseURL() ?>help/enable-business-directory.png" />
          
          <p>
              <img class="figure" align="left" alt="Business menu item" src="<?php echo Broadstreet_Utility::getImageBaseURL() ?>help/business-menu-item.png" />
              Once you enable the business directory, you will see a new sidebar
              menu item in the Wordpress admin panel. This is where you will add
              and edit new business profiles. You can visit it by
              <a href="edit.php?post_type=bs_business">clicking here</a>.
          </p>
          
          <p>
              Business profiles are just like posts, but they have a special set
              of fields that you can edit, like address, phone, hours, and more.
          </p>
          
          <p>
              Business posts do not get mixed up with your regular posts.
              Additionally, you can categorize them separately from your existing
              posts.
          </p>
          
          <p>
              Businesses are listed by default at <strong>/businesses</strong>, or
              <a target="_blank" href="<?php echo Broadstreet_Utility::getSiteBaseURL() . '/' . Broadstreet_Core::BIZ_SLUG ?>">
                    <?php echo Broadstreet_Utility::getSiteBaseURL() . '/' . Broadstreet_Core::BIZ_SLUG ?>
              </a>.
          </p>
          
          <h3>How To Add New Businesses</h3>
          
          <p>
              This is just like adding a new post. 
              <a href="edit.php?post_type=bs_business">Go to the business post page</a>,
              and click "Add New". This will bring up a blank business editor.
          </p>
          
          <img class="figure" alt="Add a new business" src="<?php echo Broadstreet_Utility::getImageBaseURL() ?>help/add-a-new-biz.png" />
          
          <p>Then you'll see:</p>
          
          <img class="figure" alt="Blank business editor" src="<?php echo Broadstreet_Utility::getImageBaseURL() ?>help/blank-business-editor.png" />
          
          <p>
              Now it's time to fill out the business profile. If you're in a
              rush or find the process time-consuming, Broadstreet offers the 
              <strong>Magic Import</strong> feature. If a business that you're profiling has
              a Facebook page, there's a good shot we can use it to aid an
              automatic search for business information and images.
          </p>
          
          <img class="figure" alt="Magic Import" src="<?php echo Broadstreet_Utility::getImageBaseURL() ?>help/magic-import.png" />
          
          <p>
              The "Business Details" box below the post editor is where the
              main business details go. All of these details are optional. If
              a field is left blank, it will not be visible on the profile.
          </p>
          
          <p>
              Once you're finished editing the profile, be sure to save your
              new post. It will now be available on the 
              <a target="_blank" href="<?php echo Broadstreet_Utility::getSiteBaseURL() . '/' . Broadstreet_Core::BIZ_SLUG ?>">
                 business listing page.
              </a>
          </p>
          
          <h3>How To Categorize Businesses</h3>
          
          <img align="left" class="figure" alt="Business categories" src="<?php echo Broadstreet_Utility::getImageBaseURL() ?>help/business-categories.png" />
          
          <p>
              Categories are a very flexible but powerful way of organizing your
              businesses. For example, maybe you'll want to have categories
              for towns or business types (restaurants, salons, etc). Additionally,
              you might add sub-categories for something like restaurants, which
              might include chinese, pizza, mexican, etc.
          </p>
          
          <p>
              Business categories are separate from your regular post categories,
              so go crazy! You can add categories using the category box on the
              profile editing page, or via the 
              <a href="?taxonomy=business_category&post_type=bs_business">
                  category editor
              </a>. Additionally, most themes allow you to easily add the
              categories as menu items on your site.
          </p>
          
          <p>
              On the profile page, use the box on the right side to add and
                edit categories for a profile, as in the figure above.
          </p>
          
          <h3>How to Add Business/Categories To A Menu</h3>
          
          <p>
              Modern Wordpress themes allow admins to design their own menus
              for use in their site. You'll undoubtedly want a link to your
              business directory in your site's main navigation menu.
          </p>
          
          <p>
              If your Wordpress theme is menu-capable, head to 
              Appearance &LongRightArrow; Menus, or <a href="nav-menus.php">click here</a>.
              Click on the main menu on your site. The name of your main menu
              is likely different from the one pictured:
          </p>
          
          <img class="figure" alt="Select a menu" src="<?php echo Broadstreet_Utility::getImageBaseURL() ?>help/select-menu.png" />
          
          <p>
              <img align="left" class="figure" alt="Business categories" src="<?php echo Broadstreet_Utility::getImageBaseURL() ?>help/menu-business-cats.png" />
              Now you should see the items in your menu. To add a business
              category, just scroll down the page on find the "Business Category"
              box on the left, and select "View All". Select the business category
              you would like to add to your menu.
          </p>
          
          <p>
              Once you click "Add to Menu", the item you selected will appear
              in your menu. At this point, you can arrange the position of the 
              item by dragging and dropping it. You can also add new menu items
              or nest menu items (depending on the theme). When you're finished,
              don't forget to click "Save Menu" on the upper-right.
          </p>
          
          <img class="figure" alt="Complete menu" src="<?php echo Broadstreet_Utility::getImageBaseURL() ?>help/menu-done.png" />
          
          <p>
              If you just want to link to your business directory as a whole, and
              not any business category in particular, scroll to the top of the
              page and look for the "Custom Links" menu. There, you can create
              a custom menu item.
          </p>
          
          <p>
              <img align="left" class="figure" alt="Custom menu item" src="<?php echo Broadstreet_Utility::getImageBaseURL() ?>help/menu-custom.png" />
              
              Type in a name like 'Business Directory', which will be the name
              of the menu item on your site. This can be anything you'd like.
              Also, paste in the URL to your business directory. For you this
              URL is: <br/>
              <a href="<?php echo Broadstreet_Utility::getSiteBaseURL() . '/' . Broadstreet_Core::BIZ_SLUG ?>">
                  <?php echo Broadstreet_Utility::getSiteBaseURL() . '/' . Broadstreet_Core::BIZ_SLUG ?>
              </a>.<br/>
              Click "Add to Menu", and arrange it as yout would have done in
              the example above.
          </p>
          
          <h2>Placing Broadstreet Ads</h2>
            
          <p>You can place Broadstreet ad zone in one of two ways:</p>
          
          <ol>
              <li>Within a post, with a special shortcode</li>
              <li>In your sidebar, with the built-in Broadstreet ad zone widget</li>
          </ol>
          
          <h3>Placing ad zones via a widget</h3>
          
          <p>
              On the <a href="#">widgets page</a>, drag and drop the
              "Broadstreet Ad Zone" widget to a widget area you would like
              the ad zone to appear in. From there, select an ad zone to show
              and click "Save".
          </p>
          
          <img class="figure" alt="Sidebar widget" src="<?php echo Broadstreet_Utility::getImageBaseURL() ?>help/sidebar-widget.png" />
          
          <h3>Placing ad zones right in a post</h3>
          
          <p>
              A common, but usually difficult need is to place an ad in the middle
              of a post. Perhaps you're profiling a business or organization
              and they have an ad that would be useful in the post.
          </p>
          
          <p>
              When editing a post, there will be an information box with a list
              of shortcodes you can use to place an ad zone. Just copy and
              paste that shortcode into the post and save. The ad will appear
              in that position. You can usually use the editor tools to align
              the ad to the left, middle, or right.
          </p>
          
          <img class="figure" alt="Shortcode list" src="<?php echo Broadstreet_Utility::getImageBaseURL() ?>help/shortcode-list.png" />
          
          <a id="faq"></a>
          <h2>FAQs</h2>
          
          <p>Here are some answers to frequently asked questions regarding
              our plugin. If you don't see the answer you're looking for,
              reach out to <a href="mailto:kenny@broadstreetads.com">kenny@broadstreetads.com</a>.
          </p>
          
          <h3>Why do I need an account with Broadstreet to use this plugin?</h3>
          
          <p>
            This plugin uses special functionality that can't be accomplished
            using Wordpress itself, so we use the Broadstreet ad server to
            do the heavy lifting required. We want to make sure only authorized
            users are using this functionality since it uses our resources.
          </p>
          
          <h3>What exactly is this "Broadstreet"?</h3>
          
          <p>
              <a href="http://broadstreetads.com/">Broadstreet</a> 
              is a company that was founded to help local publishers
              monetize their websites. Ad revenue for local news hasn't really
              kept pace with online ad revenue as a whole, and our mission is
              to change that.
          </p>
          
          <h3>Does this plugin cost money?</h3>
          
          <p>
              The core functionality of the plugin, like creating business profiles,
              is free to use. The extra 
              features such as the Magic Import and the Updateable Messages
              have a cost associated with them since they use Broadstreet's
              adserver and other resources.
          </p>
          
          <h3>How will this make me money?</h3>
          
          <p>
              There are few different ways publishers make money from local
              businesses with this plugin. Here are some more common ways:
          </p>
          <ul>
              <li><p>Charge businesses for a listing in your directory</p></li>
              <li>
                  <p>
                      Give businesses a free listing, then charge them for
                      requesting edits, like working and adding pictures
                  </p>
              </li>
              <li>
                  <p>
                      Upsell businesses on the ability to use the updateable
                      message feature we provide. For $xxx monthly, they'll be able
                      to post specials and other deals right from their Facebook
                      or Twitter account without ever logging in to their system.
                  </p>
              </li>
              <li>
                  <p>
                      Charge them to be listed in a special category, like 
                      "Lunch Specials." How cool would it be to have a listing
                      of all the places to grab lunch in town, and each has
                      updateble messages on their profile? It's the one stop for
                      lunch time.
                  </p>
              </li>
          </ul>
          <p>
              It's all about finding ways to do things that competition like
              print media can't do.
          </p>
          
          <h3>Who can I talk to if I get stuck?</h3>
          
          <p>
              <a href="mailto:kenny@broadstreetads.com">kenny@broadstreetads.com</a>
          </p>
          
      </div>
      <div class="right_column">
          <?php Broadstreet_View::load('admin/global/sidebar') ?>
      </div>
</div>

<div class="clearfix"></div>