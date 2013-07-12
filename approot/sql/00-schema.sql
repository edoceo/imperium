--
-- PostgreSQL database dump
--

SET check_function_bodies = false;
SET client_encoding = 'UTF8';
SET client_min_messages = warning;
SET default_tablespace = '';
SET default_with_oids = false;
SET escape_string_warning = off;
SET search_path = public, pg_catalog;
SET standard_conforming_strings = off;

CREATE SEQUENCE account_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- TOC entry 1606 (class 1259 OID 42030)
-- Dependencies: 1954 6
-- Name: account; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE account (
    id integer DEFAULT nextval('account_id_seq'::regclass) NOT NULL,
    parent_id integer,
    account_tax_line_id integer,
    code integer,
    link_to integer,
    link_id integer,
    type_sort integer,
    kind_sort integer,
    flag integer,
    active boolean NOT NULL,
    full_code character varying(32),
    name character varying(128),
    full_name character varying(128),
    kind character varying(32),
    note text,
    balance numeric(19,2),
    bank_account character varying(32),
    bank_routing character varying(32),
    life character(1),
    type character varying(16)
);

--
-- TOC entry 1607 (class 1259 OID 42037)
-- Dependencies: 6
-- Name: account_journal_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE account_journal_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- TOC entry 1608 (class 1259 OID 42039)
-- Dependencies: 1955 6
-- Name: account_journal; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE account_journal (
    id integer DEFAULT nextval('account_journal_id_seq'::regclass) NOT NULL,
    auth_user_id integer NOT NULL,
    date date NOT NULL,
    note text,
    kind character(1)
);


--
-- TOC entry 1609 (class 1259 OID 42043)
-- Dependencies: 6
-- Name: account_ledger_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE account_ledger_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- TOC entry 1610 (class 1259 OID 42045)
-- Dependencies: 1956 6
-- Name: account_ledger; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE account_ledger (
    id integer DEFAULT nextval('account_ledger_id_seq'::regclass) NOT NULL,
    auth_user_id integer NOT NULL,
    account_id integer NOT NULL,
    account_journal_id integer NOT NULL,
    link_to integer,
    link_id integer,
    amount numeric(15,2) NOT NULL,
    balance numeric(15,2),
    note text
);


--
-- TOC entry 1685 (class 1259 OID 42425)
-- Dependencies: 6
-- Name: account_ledger_bind; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE account_ledger_bind (
    id integer NOT NULL,
    account_ledger_id integer NOT NULL,
    link character varying(64) NOT NULL
);


--
-- TOC entry 1684 (class 1259 OID 42423)
-- Dependencies: 6 1685
-- Name: account_ledger_bind_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE account_ledger_bind_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- TOC entry 2070 (class 0 OID 0)
-- Dependencies: 1684
-- Name: account_ledger_bind_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE account_ledger_bind_id_seq OWNED BY account_ledger_bind.id;


--
-- TOC entry 1613 (class 1259 OID 42054)
-- Dependencies: 6
-- Name: account_tax_form; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE account_tax_form (
    id integer NOT NULL,
    name character varying(128),
    url character varying(1024)
);


--
-- TOC entry 1614 (class 1259 OID 42060)
-- Dependencies: 1613 6
-- Name: account_tax_form_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE account_tax_form_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- TOC entry 2071 (class 0 OID 0)
-- Dependencies: 1614
-- Name: account_tax_form_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE account_tax_form_id_seq OWNED BY account_tax_form.id;


--
-- TOC entry 1615 (class 1259 OID 42062)
-- Dependencies: 1958 6
-- Name: account_tax_line; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE account_tax_line (
    id integer NOT NULL,
    account_tax_form_id integer,
    line character varying(3),
    name character varying(128) NOT NULL,
    note text,
    sort numeric(5,1) DEFAULT 0 NOT NULL
);


--
-- TOC entry 1616 (class 1259 OID 42069)
-- Dependencies: 6 1615
-- Name: account_tax_line_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE account_tax_line_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- TOC entry 2072 (class 0 OID 0)
-- Dependencies: 1616
-- Name: account_tax_line_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE account_tax_line_id_seq OWNED BY account_tax_line.id;


--
-- TOC entry 1617 (class 1259 OID 42071)
-- Dependencies: 6
-- Name: account_wizard_journal; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE account_wizard_journal (
    id integer NOT NULL,
    auth_user_id integer NOT NULL,
    note text,
    kind character(1) NOT NULL
);


--
-- TOC entry 1618 (class 1259 OID 42074)
-- Dependencies: 6 1617
-- Name: account_wizard_journal_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE account_wizard_journal_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- TOC entry 2073 (class 0 OID 0)
-- Dependencies: 1618
-- Name: account_wizard_journal_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE account_wizard_journal_id_seq OWNED BY account_wizard_journal.id;


--
-- TOC entry 1619 (class 1259 OID 42076)
-- Dependencies: 6
-- Name: account_wizard_ledger; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE account_wizard_ledger (
    id integer NOT NULL,
    account_id integer NOT NULL,
    account_wizard_journal_id integer NOT NULL,
    amount numeric(15,2) NOT NULL,
    note text,
    link_to integer,
    link_id integer,
    side character(1) NOT NULL
);


--
-- TOC entry 1620 (class 1259 OID 42079)
-- Dependencies: 6 1619
-- Name: account_wizard_ledger_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE account_wizard_ledger_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- TOC entry 2074 (class 0 OID 0)
-- Dependencies: 1620
-- Name: account_wizard_ledger_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE account_wizard_ledger_id_seq OWNED BY account_wizard_ledger.id;


--
-- TOC entry 1621 (class 1259 OID 42081)
-- Dependencies: 6
-- Name: auth_hash; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE auth_hash (
    id integer NOT NULL,
    hash character varying(64) NOT NULL,
    link character varying(64) NOT NULL
);


--
-- TOC entry 1622 (class 1259 OID 42084)
-- Dependencies: 6 1621
-- Name: auth_hash_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE auth_hash_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- TOC entry 2075 (class 0 OID 0)
-- Dependencies: 1622
-- Name: auth_hash_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE auth_hash_id_seq OWNED BY auth_hash.id;


--
-- TOC entry 1623 (class 1259 OID 42086)
-- Dependencies: 6
-- Name: auth_role_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE auth_role_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- TOC entry 1624 (class 1259 OID 42088)
-- Dependencies: 1963 6
-- Name: auth_user; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE auth_user (
    id integer NOT NULL,
    username character varying(64) NOT NULL,
    password character(40) NOT NULL,
    active boolean DEFAULT false NOT NULL
);


--
-- TOC entry 1625 (class 1259 OID 42092)
-- Dependencies: 6 1624
-- Name: auth_user_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE auth_user_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- TOC entry 2076 (class 0 OID 0)
-- Dependencies: 1625
-- Name: auth_user_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE auth_user_id_seq OWNED BY auth_user.id;


--
-- TOC entry 1627 (class 1259 OID 42096)
-- Dependencies: 6
-- Name: auth_user_pref; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE auth_user_pref (
    id integer NOT NULL,
    auth_user_id integer NOT NULL,
    name character varying(128) NOT NULL,
    data text
);


--
-- TOC entry 1628 (class 1259 OID 42102)
-- Dependencies: 1627 6
-- Name: auth_user_pref_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE auth_user_pref_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- TOC entry 2078 (class 0 OID 0)
-- Dependencies: 1628
-- Name: auth_user_pref_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE auth_user_pref_id_seq OWNED BY auth_user_pref.id;


--
-- TOC entry 1630 (class 1259 OID 42110)
-- Dependencies: 6
-- Name: base_alert_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE base_alert_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- TOC entry 1631 (class 1259 OID 42112)
-- Dependencies: 6
-- Name: base_category_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE base_category_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- TOC entry 1634 (class 1259 OID 42119)
-- Dependencies: 1966 6
-- Name: base_diff; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE base_diff (
    id integer NOT NULL,
    auth_user_id integer NOT NULL,
    ctime timestamp without time zone DEFAULT now() NOT NULL,
    link character varying(64) NOT NULL,
    f character varying(64) NOT NULL,
    v0 text,
    v1 text
);


--
-- TOC entry 1635 (class 1259 OID 42126)
-- Dependencies: 1634 6
-- Name: base_diff_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE base_diff_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- TOC entry 2079 (class 0 OID 0)
-- Dependencies: 1635
-- Name: base_diff_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE base_diff_id_seq OWNED BY base_diff.id;


--
-- TOC entry 1636 (class 1259 OID 42128)
-- Dependencies: 6
-- Name: base_enum; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE base_enum (
    id integer NOT NULL,
    code integer,
    sort integer,
    link character varying(64) NOT NULL,
    name character varying(64) NOT NULL
);


--
-- TOC entry 1637 (class 1259 OID 42131)
-- Dependencies: 6
-- Name: base_file; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE base_file (
    id integer NOT NULL,
    size integer,
    name character varying(128) NOT NULL,
    kind character varying(64) NOT NULL,
    star character varying(8),
    path character varying(256),
    hash character varying(64),
    link character varying(64)
);


--
-- TOC entry 1638 (class 1259 OID 42137)
-- Dependencies: 1637 6
-- Name: base_file_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE base_file_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- TOC entry 2080 (class 0 OID 0)
-- Dependencies: 1638
-- Name: base_file_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE base_file_id_seq OWNED BY base_file.id;


--
-- TOC entry 1640 (class 1259 OID 42141)
-- Dependencies: 6
-- Name: base_label_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE base_label_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- TOC entry 1641 (class 1259 OID 42143)
-- Dependencies: 6
-- Name: base_link; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE base_link (
    s character varying(64) NOT NULL,
    t character varying(64) NOT NULL
);


--
-- TOC entry 1642 (class 1259 OID 42146)
-- Dependencies: 6
-- Name: base_manual_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE base_manual_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- TOC entry 1643 (class 1259 OID 42148)
-- Dependencies: 6
-- Name: base_note_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE base_note_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- TOC entry 1644 (class 1259 OID 42150)
-- Dependencies: 1969 1970 1971 1972 6
-- Name: base_note; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE base_note (
    id integer DEFAULT nextval('base_note_id_seq'::regclass) NOT NULL,
    auth_user_id integer NOT NULL,
    cts timestamp without time zone DEFAULT now() NOT NULL,
    status character varying(16) DEFAULT 'New'::character varying NOT NULL,
    kind character varying(16) DEFAULT 'Note'::character varying NOT NULL,
    name character varying(256),
    star character varying(8) null,
    link character varying(64),
    data text
);


--
-- TOC entry 1645 (class 1259 OID 42160)
-- Dependencies: 6
-- Name: base_object; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE base_object (
    id integer NOT NULL,
    name character varying(64) NOT NULL,
    stub character varying(64) NOT NULL,
    note text,
    path character varying(64),
    link character varying(64)
);


--
-- TOC entry 1648 (class 1259 OID 42175)
-- Dependencies: 6
-- Name: base_timer; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE base_timer (
    id integer NOT NULL,
    auth_user_id integer NOT NULL,
    name character varying(64) NOT NULL,
    link_to integer,
    link_id integer,
    ats bigint,
    zts bigint,
    tag character varying(8),
    lap integer
);


--
-- TOC entry 1649 (class 1259 OID 42178)
-- Dependencies: 1648 6
-- Name: base_timer_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE base_timer_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- TOC entry 2081 (class 0 OID 0)
-- Dependencies: 1649
-- Name: base_timer_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE base_timer_id_seq OWNED BY base_timer.id;


--
-- TOC entry 1650 (class 1259 OID 42180)
-- Dependencies: 6
-- Name: base_unit; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE base_unit (
    id character varying(8) NOT NULL,
    name character varying(64) NOT NULL
);


--
-- TOC entry 1651 (class 1259 OID 42183)
-- Dependencies: 6
-- Name: base_zip_code; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE base_zip_code (
    zip_code character(5),
    lat numeric(10,6),
    lon numeric(10,6),
    city character varying(64),
    county character varying(64),
    state character(2),
    kind character varying(16)
);


--
-- TOC entry 1653 (class 1259 OID 42188)
-- Dependencies: 6
-- Name: client_channel_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE client_channel_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- TOC entry 1658 (class 1259 OID 42198)
-- Dependencies: 1975 6
-- Name: contact; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE contact (
    id SERIAL PRIMARY KEY,
    cts timestamp without time zone,
    ats timestamp without time zone,
    auth_user_id integer NOT NULL,
    parent_id integer,
    contact character varying(64),
    company character varying(64),
    name character varying(64),
    sound_code character varying(32),
    phone character varying(32),
    email character varying(64),
    url character varying(128),
    title character varying(64),
    spouse character varying(32),
    tags character varying(64),
    first_name character varying(64),
    last_name character varying(64),
    kind character varying(32),
    status character varying(32)
);


--
-- TOC entry 1659 (class 1259 OID 42205)
-- Dependencies: 6
-- Name: contact_address; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE contact_address (
    id integer NOT NULL,
    auth_user_id integer NOT NULL,
    contact_id integer NOT NULL,
    kind character varying(32) NOT NULL,
    address character varying(256),
    city character varying(64),
    state character varying(64),
    post_code character varying(16),
    country character(3),
    rcpt character varying(64),
    lat numeric(12,8),
    lon numeric(12,8)
);


--
-- TOC entry 1660 (class 1259 OID 42211)
-- Dependencies: 6 1659
-- Name: contact_address_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE contact_address_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- TOC entry 2082 (class 0 OID 0)
-- Dependencies: 1660
-- Name: contact_address_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE contact_address_id_seq OWNED BY contact_address.id;


--
-- TOC entry 1661 (class 1259 OID 42213)
-- Dependencies: 6
-- Name: contact_channel_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE contact_channel_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- TOC entry 1662 (class 1259 OID 42215)
-- Dependencies: 1977 6
-- Name: contact_channel; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE contact_channel (
    id integer DEFAULT nextval('contact_channel_id_seq'::regclass) NOT NULL,
    auth_user_id integer NOT NULL,
    contact_id integer NOT NULL,
    kind integer NOT NULL,
    name character varying(32),
    data character varying(512)
);


--
-- TOC entry 1663 (class 1259 OID 42222)
-- Dependencies: 6
-- Name: full_text_search_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE full_text_search_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- TOC entry 1665 (class 1259 OID 42229)
-- Dependencies: 6
-- Name: inventory; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE inventory (
    id integer NOT NULL,
    make character varying(64) NOT NULL,
    name character varying(128) NOT NULL,
    cost numeric(15,3),
    list numeric(15,3),
    quantity integer
);


--
-- TOC entry 1666 (class 1259 OID 42232)
-- Dependencies: 1665 6
-- Name: inventory_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE inventory_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- TOC entry 2083 (class 0 OID 0)
-- Dependencies: 1666
-- Name: inventory_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE inventory_id_seq OWNED BY inventory.id;


--
-- TOC entry 1667 (class 1259 OID 42234)
-- Dependencies: 6
-- Name: invoice_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE invoice_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- TOC entry 1668 (class 1259 OID 42236)
-- Dependencies: 1979 1980 1981 6
-- Name: invoice; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE invoice (
    id integer DEFAULT nextval('invoice_id_seq'::regclass) NOT NULL,
    auth_user_id integer NOT NULL,
    contact_id integer NOT NULL,
    bill_address_id integer,
    ship_address_id integer,
    date date DEFAULT ('now'::text)::date NOT NULL,
    sub_total numeric(12,4),
    tax_total numeric(12,4),
    bill_amount numeric(12,2),
    paid_amount numeric(12,2),
    note text,
    net smallint DEFAULT 15,
    star character varying(8),
    hash character varying(64),
    status character varying(32)
);


--
-- TOC entry 1669 (class 1259 OID 42245)
-- Dependencies: 6
-- Name: invoice_item_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE invoice_item_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- TOC entry 1670 (class 1259 OID 42247)
-- Dependencies: 1982 6
-- Name: invoice_item; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE invoice_item (
    id integer DEFAULT nextval('invoice_item_id_seq'::regclass) NOT NULL,
    auth_user_id integer NOT NULL,
    invoice_id integer NOT NULL,
    workorder_item_id integer,
    line integer,
    name character varying(64),
    note text,
    quantity real,
    unit character varying(8),
    rate numeric(19,4),
    tax_rate numeric(8,4)
);


--
-- TOC entry 1671 (class 1259 OID 42254)
-- Dependencies: 1983 6
-- Name: object_history; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE object_history (
    id integer NOT NULL,
    auth_user_id integer NOT NULL,
    cts timestamp without time zone DEFAULT now() NOT NULL,
    message text,
    link character varying(64)
);


--
-- TOC entry 1672 (class 1259 OID 42261)
-- Dependencies: 6 1671
-- Name: object_history_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE object_history_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- TOC entry 2084 (class 0 OID 0)
-- Dependencies: 1672
-- Name: object_history_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE object_history_id_seq OWNED BY object_history.id;


--
-- TOC entry 1673 (class 1259 OID 42263)
-- Dependencies: 1671 6
-- Name: object_history_id_seq1; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE object_history_id_seq1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- TOC entry 2085 (class 0 OID 0)
-- Dependencies: 1673
-- Name: object_history_id_seq1; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE object_history_id_seq1 OWNED BY object_history.id;


--
-- TOC entry 1676 (class 1259 OID 42269)
-- Dependencies: 1985 6
-- Name: timesheet; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE timesheet (
    id integer NOT NULL,
    auth_user_id integer NOT NULL,
    status_id integer DEFAULT 0 NOT NULL,
    duration interval,
    time_alpha timestamp without time zone,
    time_omega timestamp without time zone,
    name character varying(64)
);


--
-- TOC entry 1677 (class 1259 OID 42273)
-- Dependencies: 6 1676
-- Name: timesheet_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE timesheet_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- TOC entry 2086 (class 0 OID 0)
-- Dependencies: 1677
-- Name: timesheet_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE timesheet_id_seq OWNED BY timesheet.id;


--
-- TOC entry 1678 (class 1259 OID 42275)
-- Dependencies: 6
-- Name: workorder_item_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE workorder_item_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- TOC entry 1679 (class 1259 OID 42277)
-- Dependencies: 1987 1988 1989 1990 6
-- Name: workorder_item; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE workorder_item (
    id integer DEFAULT nextval('workorder_item_id_seq'::regclass) NOT NULL,
    auth_user_id integer NOT NULL,
    workorder_id integer NOT NULL,
    invoice_id integer,
    date date,
    time_alpha time without time zone,
    time_omega time without time zone,
    e_quantity numeric(15,2),
    e_unit character varying(2),
    e_rate numeric(15,2),
    e_tax_rate numeric(5,3),
    a_quantity numeric(15,2) DEFAULT 1,
    a_unit character varying(2) DEFAULT 'ea'::character varying NOT NULL,
    a_rate numeric(15,2) NOT NULL,
    a_tax_rate numeric(5,3) DEFAULT 0,
    kind character varying(16) NOT NULL,
    status character varying(64) NOT NULL,
    name character varying(64) NOT NULL,
    note text
);


--
-- TOC entry 1681 (class 1259 OID 42289)
-- Dependencies: 1991 6
-- Name: workorder; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE workorder (
    id serial primary key,
    auth_user_id integer NOT NULL,
    contact_id integer NOT NULL,
    kind_id integer NOT NULL,
    date date DEFAULT ('now'::text)::date NOT NULL,
    requester character varying(128),
    summary character varying(256),
    note text,
    location character varying(256),
    base_rate numeric(5,2),
    base_unit character(2),
    bill_amount numeric(15,2),
    star character varying(8),
    hash character varying(64),
    open_amount numeric(15,2),
    status character varying(32)
);

CREATE TABLE workorder_contact (
    workorder_id int not null references workorder(id),
    contact_id int not null references contact(id)
);

--
-- TOC entry 1993 (class 2604 OID 42428)
-- Dependencies: 1684 1685 1685
-- Name: id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE account_ledger_bind ALTER COLUMN id SET DEFAULT nextval('account_ledger_bind_id_seq'::regclass);


--
-- TOC entry 1957 (class 2604 OID 42302)
-- Dependencies: 1614 1613
-- Name: id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE account_tax_form ALTER COLUMN id SET DEFAULT nextval('account_tax_form_id_seq'::regclass);


--
-- TOC entry 1959 (class 2604 OID 42303)
-- Dependencies: 1616 1615
-- Name: id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE account_tax_line ALTER COLUMN id SET DEFAULT nextval('account_tax_line_id_seq'::regclass);


--
-- TOC entry 1960 (class 2604 OID 42304)
-- Dependencies: 1618 1617
-- Name: id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE account_wizard_journal ALTER COLUMN id SET DEFAULT nextval('account_wizard_journal_id_seq'::regclass);


--
-- TOC entry 1961 (class 2604 OID 42305)
-- Dependencies: 1620 1619
-- Name: id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE account_wizard_ledger ALTER COLUMN id SET DEFAULT nextval('account_wizard_ledger_id_seq'::regclass);


--
-- TOC entry 1962 (class 2604 OID 42306)
-- Dependencies: 1622 1621
-- Name: id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE auth_hash ALTER COLUMN id SET DEFAULT nextval('auth_hash_id_seq'::regclass);


--
-- TOC entry 1964 (class 2604 OID 42307)
-- Dependencies: 1625 1624
-- Name: id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE auth_user ALTER COLUMN id SET DEFAULT nextval('auth_user_id_seq'::regclass);


--
-- TOC entry 1965 (class 2604 OID 42308)
-- Dependencies: 1628 1627
-- Name: id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE auth_user_pref ALTER COLUMN id SET DEFAULT nextval('auth_user_pref_id_seq'::regclass);


--
-- TOC entry 1967 (class 2604 OID 42309)
-- Dependencies: 1635 1634
-- Name: id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE base_diff ALTER COLUMN id SET DEFAULT nextval('base_diff_id_seq'::regclass);


--
-- TOC entry 1968 (class 2604 OID 42310)
-- Dependencies: 1638 1637
-- Name: id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE base_file ALTER COLUMN id SET DEFAULT nextval('base_file_id_seq'::regclass);


--
-- TOC entry 1974 (class 2604 OID 42311)
-- Dependencies: 1649 1648
-- Name: id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE base_timer ALTER COLUMN id SET DEFAULT nextval('base_timer_id_seq'::regclass);


--
-- TOC entry 1976 (class 2604 OID 42312)
-- Dependencies: 1660 1659
-- Name: id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE contact_address ALTER COLUMN id SET DEFAULT nextval('contact_address_id_seq'::regclass);


--
-- TOC entry 1978 (class 2604 OID 42313)
-- Dependencies: 1666 1665
-- Name: id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE inventory ALTER COLUMN id SET DEFAULT nextval('inventory_id_seq'::regclass);


--
-- TOC entry 1984 (class 2604 OID 42314)
-- Dependencies: 1672 1671
-- Name: id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE object_history ALTER COLUMN id SET DEFAULT nextval('object_history_id_seq'::regclass);


--
-- TOC entry 1986 (class 2604 OID 42315)
-- Dependencies: 1677 1676
-- Name: id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE timesheet ALTER COLUMN id SET DEFAULT nextval('timesheet_id_seq'::regclass);


--
-- TOC entry 1992 (class 2604 OID 42316)
-- Dependencies: 1682 1681
-- Name: id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE workorder ALTER COLUMN id SET DEFAULT nextval('workorder_id_seq'::regclass);


--
-- TOC entry 2056 (class 2606 OID 42430)
-- Dependencies: 1685 1685
-- Name: account_ledger_bind_pkey; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace: 
--

ALTER TABLE ONLY account_ledger_bind
    ADD CONSTRAINT account_ledger_bind_pkey PRIMARY KEY (id);


--
-- TOC entry 1999 (class 2606 OID 42322)
-- Dependencies: 1610 1610
-- Name: account_ledger_pkey; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace: 
--

ALTER TABLE ONLY account_ledger
    ADD CONSTRAINT account_ledger_pkey PRIMARY KEY (id);


--
-- TOC entry 2003 (class 2606 OID 42326)
-- Dependencies: 1613 1613
-- Name: account_tax_form_pkey; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace: 
--

ALTER TABLE ONLY account_tax_form
    ADD CONSTRAINT account_tax_form_pkey PRIMARY KEY (id);


--
-- TOC entry 2005 (class 2606 OID 42328)
-- Dependencies: 1615 1615
-- Name: account_tax_line_pkey; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace: 
--

ALTER TABLE ONLY account_tax_line
    ADD CONSTRAINT account_tax_line_pkey PRIMARY KEY (id);


--
-- TOC entry 2007 (class 2606 OID 42330)
-- Dependencies: 1617 1617
-- Name: account_wizard_journal_pkey; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace: 
--

ALTER TABLE ONLY account_wizard_journal
    ADD CONSTRAINT account_wizard_journal_pkey PRIMARY KEY (id);


--
-- TOC entry 2009 (class 2606 OID 42332)
-- Dependencies: 1619 1619
-- Name: account_wizard_ledger_pkey; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace: 
--

ALTER TABLE ONLY account_wizard_ledger
    ADD CONSTRAINT account_wizard_ledger_pkey PRIMARY KEY (id);


--
-- TOC entry 2011 (class 2606 OID 42334)
-- Dependencies: 1621 1621
-- Name: auth_hash_pkey; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace: 
--

ALTER TABLE ONLY auth_hash
    ADD CONSTRAINT auth_hash_pkey PRIMARY KEY (id);


--
-- TOC entry 2013 (class 2606 OID 42336)
-- Dependencies: 1624 1624
-- Name: auth_user_pkey; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace: 
--

ALTER TABLE ONLY auth_user
    ADD CONSTRAINT auth_user_pkey PRIMARY KEY (id);


--
-- TOC entry 2015 (class 2606 OID 42338)
-- Dependencies: 1627 1627
-- Name: auth_user_pref_pkey; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace: 
--

ALTER TABLE ONLY auth_user_pref
    ADD CONSTRAINT auth_user_pref_pkey PRIMARY KEY (id);


--
-- TOC entry 2021 (class 2606 OID 42342)
-- Dependencies: 1634 1634
-- Name: base_diff_pkey; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace: 
--

ALTER TABLE ONLY base_diff
    ADD CONSTRAINT base_diff_pkey PRIMARY KEY (id);


--
-- TOC entry 2023 (class 2606 OID 42344)
-- Dependencies: 1637 1637
-- Name: base_file_pkey; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace: 
--

ALTER TABLE ONLY base_file
    ADD CONSTRAINT base_file_pkey PRIMARY KEY (id);


--
-- TOC entry 2026 (class 2606 OID 42346)
-- Dependencies: 1644 1644
-- Name: base_note_pkey; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace: 
--

ALTER TABLE ONLY base_note
    ADD CONSTRAINT base_note_pkey PRIMARY KEY (id);


--
-- TOC entry 2028 (class 2606 OID 42348)
-- Dependencies: 1645 1645
-- Name: base_object_pkey; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace: 
--

ALTER TABLE ONLY base_object
    ADD CONSTRAINT base_object_pkey PRIMARY KEY (id);


--
-- TOC entry 2032 (class 2606 OID 42350)
-- Dependencies: 1648 1648
-- Name: base_timer_pkey; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace: 
--

ALTER TABLE ONLY base_timer
    ADD CONSTRAINT base_timer_pkey PRIMARY KEY (id);


--
-- TOC entry 2034 (class 2606 OID 42354)
-- Dependencies: 1650 1650
-- Name: base_unit_pkey; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace: 
--

ALTER TABLE ONLY base_unit
    ADD CONSTRAINT base_unit_pkey PRIMARY KEY (id);


--
-- TOC entry 2038 (class 2606 OID 42356)
-- Dependencies: 1659 1659
-- Name: contact_address_pkey; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace: 
--

ALTER TABLE ONLY contact_address
    ADD CONSTRAINT contact_address_pkey PRIMARY KEY (id);


--
-- TOC entry 2042 (class 2606 OID 42362)
-- Dependencies: 1665 1665
-- Name: inventory_pkey; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace: 
--

ALTER TABLE ONLY inventory
    ADD CONSTRAINT inventory_pkey PRIMARY KEY (id);


--
-- TOC entry 2046 (class 2606 OID 42364)
-- Dependencies: 1670 1670
-- Name: invoice_item_pkey; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace: 
--

ALTER TABLE ONLY invoice_item
    ADD CONSTRAINT invoice_item_pkey PRIMARY KEY (id);


--
-- TOC entry 2048 (class 2606 OID 42366)
-- Dependencies: 1671 1671
-- Name: object_history_pkey; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace: 
--

ALTER TABLE ONLY object_history
    ADD CONSTRAINT object_history_pkey PRIMARY KEY (id);


--
-- TOC entry 1995 (class 2606 OID 42368)
-- Dependencies: 1606 1606
-- Name: pk_account; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace: 
--

ALTER TABLE ONLY account
    ADD CONSTRAINT pk_account PRIMARY KEY (id);


--
-- TOC entry 1997 (class 2606 OID 42370)
-- Dependencies: 1608 1608
-- Name: pk_account_journal_id; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace: 
--

ALTER TABLE ONLY account_journal
    ADD CONSTRAINT pk_account_journal_id PRIMARY KEY (id);


--
-- TOC entry 2040 (class 2606 OID 42372)
-- Dependencies: 1662 1662
-- Name: pk_contact_channel; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace: 
--

ALTER TABLE ONLY contact_channel
    ADD CONSTRAINT pk_contact_channel PRIMARY KEY (id);


--
-- TOC entry 2044 (class 2606 OID 42374)
-- Dependencies: 1668 1668
-- Name: pk_invoice; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace: 
--

ALTER TABLE ONLY invoice
    ADD CONSTRAINT pk_invoice PRIMARY KEY (id);


--
-- TOC entry 2050 (class 2606 OID 42376)
-- Dependencies: 1676 1676
-- Name: timesheet_pkey; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace: 
--

ALTER TABLE ONLY timesheet
    ADD CONSTRAINT timesheet_pkey PRIMARY KEY (id);


--
-- TOC entry 2052 (class 2606 OID 42378)
-- Dependencies: 1679 1679
-- Name: work_order_item_pkey; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace: 
--

ALTER TABLE ONLY workorder_item
    ADD CONSTRAINT work_order_item_pkey PRIMARY KEY (id);


--
-- TOC entry 2024 (class 1259 OID 42381)
-- Dependencies: 1641
-- Name: ix_base_link_s; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX ix_base_link_s ON base_link USING btree (s);


--
-- TOC entry 2065 (class 2606 OID 42431)
-- Dependencies: 1610 1998 1685
-- Name: account_ledger_bind_account_ledger_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY account_ledger_bind
    ADD CONSTRAINT account_ledger_bind_account_ledger_id_fkey FOREIGN KEY (account_ledger_id) REFERENCES account_ledger(id) initially deferred;


--
-- TOC entry 2060 (class 2606 OID 42382)
-- Dependencies: 2012 1627 1624
-- Name: auth_user_pref_auth_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY auth_user_pref
    ADD CONSTRAINT auth_user_pref_auth_user_id_fkey FOREIGN KEY (auth_user_id) REFERENCES auth_user(id) initially deferred;


--
-- TOC entry 2058 (class 2606 OID 42392)
-- Dependencies: 1610 1606 1994
-- Name: fk_account_ledger_account_id; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY account_ledger
    ADD CONSTRAINT fk_account_ledger_account_id FOREIGN KEY (account_id) REFERENCES account(id) initially deferred;


--
-- TOC entry 2059 (class 2606 OID 42397)
-- Dependencies: 1610 1608 1996
-- Name: fk_account_ledger_account_journal_id; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY account_ledger
    ADD CONSTRAINT fk_account_ledger_account_journal_id FOREIGN KEY (account_journal_id) REFERENCES account_journal(id) initially deferred;


--
-- TOC entry 2061 (class 2606 OID 42402)
-- Dependencies: 1658 1668 2035
-- Name: fk_invoice_contact_id; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY invoice
    ADD CONSTRAINT fk_invoice_contact_id FOREIGN KEY (contact_id) REFERENCES contact(id) initially deferred;


--
-- TOC entry 2062 (class 2606 OID 42407)
-- Dependencies: 1668 1670 2043
-- Name: fk_invoice_id; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY invoice_item
    ADD CONSTRAINT fk_invoice_id FOREIGN KEY (invoice_id) REFERENCES invoice(id) initially deferred;


--
-- TOC entry 2064 (class 2606 OID 42412)
-- Dependencies: 1658 2035 1681
-- Name: fk_work_order_contact_id; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY workorder
    ADD CONSTRAINT fk_work_order_contact_id FOREIGN KEY (contact_id) REFERENCES contact(id) initially deferred;


--
-- TOC entry 2063 (class 2606 OID 42417)
-- Dependencies: 1681 1679 2053
-- Name: fk_work_order_item_work_order_id; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY workorder_item
    ADD CONSTRAINT fk_work_order_item_work_order_id FOREIGN KEY (workorder_id) REFERENCES workorder(id) initially deferred;
