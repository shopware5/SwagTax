INSERT INTO `s_core_tax` (`id`, `tax`, `description`)
VALUES (5, '10.00', '10%');


INSERT INTO `s_plugin_custom_products_template` (`id`, `internal_name`, `display_name`, `description`, `media_id`,
                                                 `step_by_step_configurator`, `active`, `confirm_input`,
                                                 `variants_on_top`)
VALUES (1, 'InternalName', 'DisplayName', '', NULL, 0, 1, 0, 0);

INSERT INTO `s_plugin_custom_products_template_product_relation` (`template_id`, `article_id`)
VALUES (1, 213);

INSERT INTO `s_plugin_custom_products_option` (`id`, `template_id`, `name`, `description`, `ordernumber`, `required`,
                                               `type`, `position`, `default_value`, `placeholder`, `is_once_surcharge`,
                                               `max_text_length`, `min_value`, `max_value`, `max_file_size`, `min_date`,
                                               `max_date`, `max_files`, `interval`, `could_contain_values`,
                                               `allows_multiple_selection`)
VALUES (3, 1, 'Textfeld', '', 'Textfeld', 0, 'textfield', 0, '', '', 0, NULL, NULL, NULL, 3145728, NULL, NULL, 1, 1, 0,
        0),
       (4, 1, 'Farbauswahl', '', 'Farbauswahl', 0, 'colorselect', 1, '', '', 0, NULL, NULL, NULL, 3145728, NULL, NULL,
        1, 1, 1, 0),
       (5, 1, 'Nummernfeld', '', 'Nummernfeld', 0, 'numberfield', 2, '', '', 0, NULL, NULL, NULL, 3145728, NULL, NULL,
        1, 1, 0, 0);

INSERT INTO `s_plugin_custom_products_value` (`id`, `option_id`, `name`, `ordernumber`, `value`, `is_default_value`,
                                              `position`, `is_once_surcharge`, `media_id`, `seo_title`)
VALUES (6, 4, '#FFC528', '#FFC528', '#FFC528', 0, 0, 0, NULL, NULL),
       (7, 4, '#F72000', '#F72000', '#F72000', 0, 1, 0, NULL, NULL),
       (8, 4, '#0E63FF', '#0E63FF', '#0E63FF', 0, 2, 0, NULL, NULL),
       (9, 4, '#14FF9C', '#14FF9C', '#14FF9C', 0, 3, 0, NULL, NULL),
       (10, 4, '#D8FF62', '#D8FF62', '#D8FF62', 0, 4, 0, NULL, NULL);

INSERT INTO `s_plugin_custom_products_price` (`id`, `option_id`, `value_id`, `surcharge`, `percentage`,
                                              `is_percentage_surcharge`, `tax_id`, `customer_group_name`,
                                              `customer_group_id`)
VALUES (8, 3, NULL, 8.4033613445378, 0, 0, 1, 'Shopkunden', 1),
       (9, NULL, 6, 4.2016806722689, 0, 0, 1, 'Shopkunden', 1),
       (10, NULL, 7, 2.5210084033613, 0, 0, 1, 'Shopkunden', 1),
       (11, NULL, 8, 8.4033613445378, 0, 0, 1, 'Shopkunden', 1),
       (12, NULL, 9, 0.93457943925234, 0, 0, 4, 'Shopkunden', 1),
       (13, NULL, 10, 0, 5, 1, 1, 'Shopkunden', 1),
       (14, 4, NULL, 0.84033613445378, 0, 0, 1, 'Shopkunden', 1),
       (15, 5, NULL, 0, 10, 1, 1, 'Shopkunden', 1);


INSERT INTO `s_articles_bundles` (`id`, `articleID`, `name`, `show_name`, `active`, `description`, `rab_type`, `taxID`,
                                  `ordernumber`, `max_quantity_enable`, `display_global`, `display_delivery`,
                                  `max_quantity`, `valid_from`, `valid_to`, `datum`, `sells`, `bundle_type`,
                                  `bundle_position`)
VALUES (2, 272, 'New Bundle', 0, 1, '', 'abs', NULL, 'lkjhgfd', 0, 0, 1, 0, NULL, NULL, '2020-12-09 11:28:52', 0, 1, 0),
       (1, 178, 'New Bundle', 0, 1, '', 'abs', NULL, 'sdfghjkl', 0, 0, 1, 0, NULL, NULL, '2020-12-09 11:28:04', 0, 1,
        0);

INSERT INTO `s_articles_bundles_articles` (`id`, `bundle_id`, `article_detail_id`, `quantity`, `configurable`,
                                           `bundle_group_id`, `position`)
VALUES (1, 1, 4, 1, 0, NULL, 1),
       (2, 1, 6, 1, 0, NULL, 1),
       (3, 2, 252, 1, 0, NULL, 1),
       (4, 2, 255, 1, 0, NULL, 1);

INSERT INTO `s_articles_bundles_customergroups` (`id`, `bundle_id`, `customer_group_id`)
VALUES (1, 1, 1),
       (2, 2, 1);

INSERT INTO `s_articles_bundles_prices` (`id`, `bundle_id`, `customer_group_id`, `price`)
VALUES (2, 2, '1', 42.981308411215),
       (1, 1, '1', 50.411764705882);
