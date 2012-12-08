        // Collect Search Term
        $term = null;
        if (!empty($_GET['q'])) {
            $term = trim($_GET['q']);
        } else {
            $term = $this->_s->SearchTerm;
        }
        if (strlen($term)==0) {
            $this->view->title = array('Search','No Term Submitted');
            unset($this->_s->SearchTerm);
            return(0);
        }

        $this->_s->SearchTerm = $term;
        $this->view->SearchTerm = $term;

        // Check for ID Specific Queries
        switch (strtok(strtolower($term),':')) {
        case 'co':
            $this->redirect('/contact/view?c=' . strtok(':'));
        case 'iv':
            $this->redirect('/invoice/view?i=' . strtok(':'));
        case 'je':
            $this->_redirect('/account/transaction?id=' . strtok(':'));
        case 'wo':
            $this->redirect('/workorder/view?w=' . strtok(':'));
        }

        // PostgreSQL Full Text Search
        $sql = 'SELECT link_to,link_id,name, ';
        $sql.= $this->_d->quoteInto(' ts_headline(ft,plainto_tsquery(?)) as snip, ',$term);
        $sql.= $this->_d->quoteInto(' ts_rank_cd(tv,plainto_tsquery(?)) as sort ',$term);
        $sql.= ' FROM full_text ';
        $sql.= $this->_d->quoteInto(' WHERE tv @@ plainto_tsquery(?) ',$term);
        $sql.= ' ORDER BY sort DESC, name';

        $this->view->SearchList = $this->_d->fetchAll($sql);
        $c = count($this->view->SearchList);
        $this->view->title = array('Search',$term, ($c==1 ? '1 result' : $c . ' results') );

