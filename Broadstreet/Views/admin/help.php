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
              and we'll get you set up.
          </p>
          
          <h2>Quick Start</h2>
          
          <p>
              If you're in a rush, this short video will go over the basics of
              getting your business directory up and running quickly.
          </p>
          
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
              
          </p>
          
          <h2>Placing Broadstreet Ads</h2>
            
          <p>You can place Broadstreet ads in one of two ways:</p>
          
          <ol>
              <li>Within a post, with a special shortcode</li>
              <li>In your sidebar, with the built-in Broadstreet ad zone widget</li>
          </ol>
          
          <h3>Placing ad zones via a widget</h3>
          
          <h2>FAQs</h2>
          
          <p>Here are some answers to frequently asked questions regarding
              our plugin. If you don't see the answer you're looking for,
              reach out to <a href="mailto:kenny@broadstreetads.com">kenny@broadstreetads.com</a>.
          </p>
          
          <h3>Why do I need an account with Broadstreet to use this plugin?</h3>
          
      </div>
      <div class="right_column">
          <?php Broadstreet_View::load('admin/global/sidebar') ?>
      </div>
</div>

<div class="clearfix"></div>