<?php

use Phinx\Migration\AbstractMigration;

class ApplicationTranslateConfigs extends AbstractMigration
{
    public function up()
    {
        $this->execute("
            update configs set display_name = 'application.config.currency.name' where name = 'currency';
            update configs set description = 'application.config.currency.description' where name = 'currency';
            
            update configs set display_name = 'application.config.site_title.name' where name = 'site_title';
            update configs set description = 'application.config.site_title.description' where name = 'site_title';
            
            update configs set display_name = 'application.config.site_description.name' where name = 'site_description';
            update configs set description = 'application.config.site_description.description' where name = 'site_description';
            
            update configs set display_name = 'application.config.site_url.name' where name = 'site_url';
            update configs set description = 'application.config.site_url.description' where name = 'site_url';
            
            update configs set display_name = 'application.config.cms_url.name' where name = 'cms_url';
            update configs set description = 'application.config.cms_url.description' where name = 'cms_url';
            
            update configs set display_name = 'application.config.contact_email.name' where name = 'contact_email';
            update configs set description = 'application.config.contact_email.description' where name = 'contact_email';
            
            update configs set display_name = 'application.config.default_route.name' where name = 'default_route';
            update configs set description = 'application.config.default_route.description' where name = 'default_route';
            
            update configs set display_name = 'application.config.home_route.name' where name = 'home_route';
            update configs set description = 'application.config.home_route.description' where name = 'home_route';
            
            update configs set display_name = 'application.config.not_logged_in_route.name' where name = 'not_logged_in_route';
            update configs set description = 'application.config.not_logged_in_route.description' where name = 'not_logged_in_route';
            
            update configs set display_name = 'application.config.layout_name.name' where name = 'layout_name';
            update configs set description = 'application.config.layout_name.description' where name = 'layout_name';
            
            update configs set display_name = 'application.config.og_image.name' where name = 'og_image';
            update configs set description = 'application.config.og_image.description' where name = 'og_image';
            
            update configs set display_name = 'application.config.header_block.name' where name = 'header_block';
            update configs set description = 'application.config.header_block.description' where name = 'header_block';
        ");
    }

    public function down()
    {

    }
}
