<?php

// Create tables
rex_sql_table::get(rex::getTable('notalone'))
    ->ensurePrimaryIdColumn()
    ->ensureColumn(new rex_sql_column('timestamp', 'bigint(20)', true))
    ->ensureColumn(new rex_sql_column('be_user', 'varchar(191)'))
    ->ensureColumn(new rex_sql_column('article_id', 'int(11)', true))
    ->ensureColumn(new rex_sql_column('category_id', 'int(11)', true))
    ->ensureColumn(new rex_sql_column('clang', 'int(11)', true))
    ->ensureColumn(new rex_sql_column('slice_id', 'int(11)', true))
    ->ensureColumn(new rex_sql_column('table_id', 'int(11)', true))
    ->ensureColumn(new rex_sql_column('data_id', 'int(11)', true))
    ->ensure();