<?php
if (rex::isBackend() && is_object(rex::getUser())) {
    rex_perm::register('notalone[]');
}

if ( rex::isBackend() && rex::getUser() ) {

        rex_extension::register('META_NAVI', function (rex_extension_point $ep) {
            // DELETE OLD ENTRYS OF SAME USER
            $page = rex_be_controller::getCurrentPage();
            // credits page ist used every 5 minutes for keepAliveInterval via ajax > so exclude this
            if ($page !== 'credits') {
                $sql = rex_sql::factory();
                $sql->setDebug(false);
                $sql->setTable(rex::getTable('notalone'));
                $sql->setWhere(['be_user' => rex::getUser()->getLogin()]);
                $sql->delete();
            }

            // DELETE OLD ENTRYS OF OTHER USERS WITHOUT ACTIVE SESSION
            $qry = "SELECT * FROM `rex_user` WHERE session_id=:session_id";
            $sql = rex_sql::factory();
            $sql->setDebug(false);
            $sql->setQuery($qry, [':session_id' => '']);
            if ($results = $sql->getArray()) {
                foreach ($results as $result) {
                    $sql = rex_sql::factory();
                    $sql->setDebug(false);
                    $sql->setTable(rex::getTable('notalone'));
                    $sql->setWhere(['be_user' => $result['login']]);
                    $sql->delete();
                }
            }

        }, \rex_extension::EARLY);

        rex_extension::register('STRUCTURE_CONTENT_BEFORE_SLICES', function (rex_extension_point $ep) {

            $params = $ep->getParams();
            // Params: 'article_id', 'clang', 'slice_id', 'page', 'ctype', 'category_id', 'article_revision', 'slice_revision'

            // CHECK IF EXISTS
            $qry = "SELECT * FROM `rex_notalone` WHERE article_id=:article_id && category_id=:category_id && clang=:clang LIMIT 1";
            $sql = rex_sql::factory();
            $sql->setDebug(false);
            $sql->setQuery($qry, [':article_id' => $params['article_id'],':category_id' => $params['category_id'],':clang' => $params['clang']]);
            if (1 == $sql->getRows())
            {
                // if not the same user: show warning
                if (rex::getUser()->getLogin() !== $sql->getValue('be_user')) {
                    $time_passed = intval(date('i', time() - $sql->getValue('timestamp')));

                    $error = $this->i18n('notalone_hinweis_part1').' '.$sql->getValue('be_user').' '.$sql->getValue('notalone_hinweis_part2').' '.$time_passed.' '.$this->i18n('notalone_hinweis_part3');
                    echo rex_view::error($error);
                }
            }
            else {
                // INSERT
                $sql = rex_sql::factory();
                $sql->setDebug(false);
                $sql->setTable(rex::getTable('notalone'));
                $sql->setValue('timestamp', time());
                $sql->setValue('be_user', rex::getUser()->getLogin());
                $sql->setValue('article_id', $params['article_id']);
                $sql->setValue('category_id', $params['category_id']);
                $sql->setValue('clang', $params['clang']);
                $sql->setValue('slice_id', $params['slice_id']);

                try {
                    $sql->insert();
                    //echo rex_view::success('Seite gesperrt');
                } catch (Exception $e) {
                    //echo rex_view::error('Fehler beim Sperren.');
                }
            }


        }, \rex_extension::LATE);
}