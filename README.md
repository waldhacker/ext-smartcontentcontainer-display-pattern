## TYPO3 "smart content container" display pattern expansion

The extension is currently in beta state and should serve as a showcase how the ["smart content container"](https://review.typo3.org/c/Packages/TYPO3.CMS/+/70680) content element can be extended and what possibilities open up.

This extension extends the "smart content container" content element with "display pattern" and "special content placement".

### Installation

Since the extension is not yet released on packagist (because the "smart content container" content element is currently still under development) it must be installed differently.

If you are using composer then the easiest way is to define a path repo in your root composer.json (if not already done), clone this extension into the defined path and then run `composer req waldhacker/typo3-smartcontentcontainer-display-pattern:*@dev`.

If you are not use composer than clone this extension and put the content under `typo3conf/ext/smartcontentcontainer_display_pattern/`.

After that the extension must be activated in the TYPO3 extension manager.

Don't forget to add the TypoScript configuration of this extension in your backend template.

### Usage

#### Display pattern

By default, the "smart content pool" content element allows sorting the content pool items based on

* the sorting of the content pool items
* the last edit date of the content pool items
* random

If you work a lot with categories, these sorting options may not be sufficient because in the content pool only the category is referenced and the sorting of the content elements assigned to the category is then no longer possible in the "smart content container".

In this case the display patterns could be interesting for you.  
With the display pattern rules it is possible to define more complex sorting behavior.  

With the display pattern you can create any number of rule sets defining how many items from the content pool of which type (based on content element type or category) should be displayed in which order.

For example, let's imagine that 2 categories are referenced in the content pool: "Blog posts" and "News articles" and the "smart content container" is configured to show a maximum of 20 content pool items.  
The category "Blog posts" contains 10 records of type 'tx_blog_post' and the category "News articles" contains 10 records of type 'tx_news_article'.  
The frontend would now output 20 content elements, 10 of type 'tx_blog_post' and 10 of type 'tx_news_article' in that order and the sorting of the 10 blog posts can only be sorted by the edit date or randomly and for the 10 news articles the same applies.  

Now you can create a display pattern rule which is configured to display 2 content pool items of type 'tx_blog_post'.  
You can create another display pattern rule which is configured to display 2 content pool items of type 'tx_news_article'.  
The frontend would now output 4 content elements, 2 of type 'tx_blog_post' and 2 of type 'tx_news_article'.  
Now you can change the display pattern configuration and define that the created rules should be applied repeatedly until all content pool items are displayed that match the content types defined in the rules.  
Now you see in the frontend alternately 2 contents of type 'tx_blog_post' and 2 of type 'tx_news_article'.

#### Special content placement

With "special content placement" it is possible to place content that is independent of the content pool at defined positions in the content list rendered by the "smart content container".

For example, let's assume the content pool contains a category record and 10 blog posts are assigned to this category.  
The "smart content container" will now display these 10 blog posts if configured accordingly.  
With the "special content placement" it is now possible to display a content of the category "advertisement" at every 3rd position.  
You can define if the blog post which would be displayed at the 3rd position is completely replaced or if this blog post move back one place in the list.  
If the blog post should move back one place according to the configuration and the "smart content container" is configured to display a maximum of 10 content pool items, then the former 10th blog post will disappear completely from the list.  
It is also possible to define that a "special content placement" should be placed exactly once at an exact position and not at every n-th position as in the example above.  
Any number of "special content placement" rules can be created.

### ToDo:

* add docs
* add tests
* add CI integration
* release on packagist
