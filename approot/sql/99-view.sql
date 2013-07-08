--


CREATE VIEW workorder_item_invoice_item AS
    SELECT woi.id,
        woi.auth_user_id,
        woi.workorder_id,
        ivi.invoice_id,
        woi.status,
        woi.kind,
        woi.date,
        woi.name,
        woi.note,
        woi.a_quantity AS quantity,
        woi.a_unit AS unit,
        woi.a_rate AS rate 
        FROM
            workorder_item woi
            LEFT JOIN invoice_item ivi ON woi.id = ivi.workorder_item_id;


--


CREATE VIEW general_ledger AS
    SELECT a.id AS account_id,
    a.parent_id AS parent_id,
    c.id AS account_journal_id,
    b.id AS account_ledger_id,
    a.code AS account_code,
    a.full_code AS account_full_code,
    a.name AS account_name,
    a.full_name AS account_full_name,
    c.date, b.amount,
    c.kind,
    c.note,
    b.link_to,
    b.link_id 
    FROM account a 
        JOIN account_ledger b ON a.id = b.account_id
        JOIN account_journal c ON c.id = b.account_journal_id;

