--
-- PostgreSQL database dump
--

-- Dumped from database version 14.5
-- Dumped by pg_dump version 14.5

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: cache; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.cache (
    key character varying(255) NOT NULL,
    value text NOT NULL,
    expiration integer NOT NULL
);


ALTER TABLE public.cache OWNER TO postgres;

--
-- Name: cache_locks; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.cache_locks (
    key character varying(255) NOT NULL,
    owner character varying(255) NOT NULL,
    expiration integer NOT NULL
);


ALTER TABLE public.cache_locks OWNER TO postgres;

--
-- Name: current_stocks; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.current_stocks (
    id bigint NOT NULL,
    product_id bigint NOT NULL,
    unit_id bigint NOT NULL,
    quantity bigint DEFAULT '0'::bigint NOT NULL,
    base_quantity bigint DEFAULT '0'::bigint NOT NULL,
    slug character varying(255) NOT NULL,
    month smallint NOT NULL,
    year integer NOT NULL,
    status_running character varying(255) DEFAULT 'SEDANG_PROSES'::character varying NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    CONSTRAINT current_stocks_status_running_check CHECK (((status_running)::text = ANY ((ARRAY['MASTER_BARU'::character varying, 'SEDANG_PROSES'::character varying, 'SEDANG_BERJALAN'::character varying, 'STOCK_OPNAME'::character varying, 'SUDAH_BERAKHIR'::character varying])::text[])))
);


ALTER TABLE public.current_stocks OWNER TO postgres;

--
-- Name: current_stocks_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.current_stocks_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.current_stocks_id_seq OWNER TO postgres;

--
-- Name: current_stocks_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.current_stocks_id_seq OWNED BY public.current_stocks.id;


--
-- Name: customers; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.customers (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    slug character varying(255) NOT NULL,
    phone character varying(255),
    is_active boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.customers OWNER TO postgres;

--
-- Name: customers_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.customers_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.customers_id_seq OWNER TO postgres;

--
-- Name: customers_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.customers_id_seq OWNED BY public.customers.id;


--
-- Name: failed_jobs; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.failed_jobs (
    id bigint NOT NULL,
    uuid character varying(255) NOT NULL,
    connection text NOT NULL,
    queue text NOT NULL,
    payload text NOT NULL,
    exception text NOT NULL,
    failed_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


ALTER TABLE public.failed_jobs OWNER TO postgres;

--
-- Name: failed_jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.failed_jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.failed_jobs_id_seq OWNER TO postgres;

--
-- Name: failed_jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.failed_jobs_id_seq OWNED BY public.failed_jobs.id;


--
-- Name: good_receipt_details; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.good_receipt_details (
    id bigint NOT NULL,
    good_receipt_id bigint NOT NULL,
    purchase_order_detail_id bigint NOT NULL,
    product_id bigint NOT NULL,
    unit_id bigint NOT NULL,
    quantity integer DEFAULT 0 NOT NULL,
    base_quantity bigint DEFAULT '0'::bigint NOT NULL,
    price bigint DEFAULT '0'::bigint NOT NULL,
    subtotal bigint DEFAULT '0'::bigint NOT NULL,
    received_quantity bigint DEFAULT '0'::bigint NOT NULL,
    received_base_quantity bigint DEFAULT '0'::bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.good_receipt_details OWNER TO postgres;

--
-- Name: good_receipt_details_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.good_receipt_details_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.good_receipt_details_id_seq OWNER TO postgres;

--
-- Name: good_receipt_details_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.good_receipt_details_id_seq OWNED BY public.good_receipt_details.id;


--
-- Name: good_receipts; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.good_receipts (
    id bigint NOT NULL,
    receipt_number character varying(255) NOT NULL,
    slug character varying(255) NOT NULL,
    purchase_order_id bigint NOT NULL,
    supplier_id bigint NOT NULL,
    user_id bigint NOT NULL,
    receipt_date timestamp(0) without time zone NOT NULL,
    user_ack_id bigint,
    ack_date timestamp(0) without time zone,
    user_reject_id bigint,
    reject_date timestamp(0) without time zone,
    subtotal bigint DEFAULT '0'::bigint NOT NULL,
    tax bigint DEFAULT '0'::bigint NOT NULL,
    discount bigint DEFAULT '0'::bigint NOT NULL,
    total_net bigint DEFAULT '0'::bigint NOT NULL,
    status_receipt character varying(255) DEFAULT 'PROSESS'::character varying NOT NULL,
    notes text,
    is_active boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    CONSTRAINT good_receipts_status_receipt_check CHECK (((status_receipt)::text = ANY ((ARRAY['PROSESS'::character varying, 'RECEIVED'::character varying, 'CANCELED'::character varying])::text[])))
);


ALTER TABLE public.good_receipts OWNER TO postgres;

--
-- Name: good_receipts_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.good_receipts_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.good_receipts_id_seq OWNER TO postgres;

--
-- Name: good_receipts_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.good_receipts_id_seq OWNED BY public.good_receipts.id;


--
-- Name: job_batches; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.job_batches (
    id character varying(255) NOT NULL,
    name character varying(255) NOT NULL,
    total_jobs integer NOT NULL,
    pending_jobs integer NOT NULL,
    failed_jobs integer NOT NULL,
    failed_job_ids text NOT NULL,
    options text,
    cancelled_at integer,
    created_at integer NOT NULL,
    finished_at integer
);


ALTER TABLE public.job_batches OWNER TO postgres;

--
-- Name: jobs; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.jobs (
    id bigint NOT NULL,
    queue character varying(255) NOT NULL,
    payload text NOT NULL,
    attempts smallint NOT NULL,
    reserved_at integer,
    available_at integer NOT NULL,
    created_at integer NOT NULL
);


ALTER TABLE public.jobs OWNER TO postgres;

--
-- Name: jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.jobs_id_seq OWNER TO postgres;

--
-- Name: jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.jobs_id_seq OWNED BY public.jobs.id;


--
-- Name: migrations; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.migrations (
    id integer NOT NULL,
    migration character varying(255) NOT NULL,
    batch integer NOT NULL
);


ALTER TABLE public.migrations OWNER TO postgres;

--
-- Name: migrations_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.migrations_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.migrations_id_seq OWNER TO postgres;

--
-- Name: migrations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.migrations_id_seq OWNED BY public.migrations.id;


--
-- Name: password_reset_tokens; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.password_reset_tokens (
    email character varying(255) NOT NULL,
    token character varying(255) NOT NULL,
    created_at timestamp(0) without time zone
);


ALTER TABLE public.password_reset_tokens OWNER TO postgres;

--
-- Name: products; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.products (
    id bigint NOT NULL,
    code character varying(255) NOT NULL,
    name character varying(255) NOT NULL,
    slug character varying(255) NOT NULL,
    variant_name character varying(255) NOT NULL,
    default_unit_id bigint NOT NULL,
    purchase_price bigint DEFAULT '0'::bigint NOT NULL,
    selling_price bigint DEFAULT '0'::bigint NOT NULL,
    description text,
    is_active boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.products OWNER TO postgres;

--
-- Name: products_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.products_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.products_id_seq OWNER TO postgres;

--
-- Name: products_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.products_id_seq OWNED BY public.products.id;


--
-- Name: purchase_order_details; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.purchase_order_details (
    id bigint NOT NULL,
    purchase_order_id bigint NOT NULL,
    product_id bigint NOT NULL,
    unit_id bigint NOT NULL,
    quantity integer DEFAULT 0 NOT NULL,
    base_quantity bigint DEFAULT '0'::bigint NOT NULL,
    price bigint DEFAULT '0'::bigint NOT NULL,
    subtotal bigint DEFAULT '0'::bigint NOT NULL,
    received_quantity bigint DEFAULT '0'::bigint NOT NULL,
    received_base_quantity bigint DEFAULT '0'::bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.purchase_order_details OWNER TO postgres;

--
-- Name: purchase_order_details_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.purchase_order_details_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.purchase_order_details_id_seq OWNER TO postgres;

--
-- Name: purchase_order_details_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.purchase_order_details_id_seq OWNED BY public.purchase_order_details.id;


--
-- Name: purchase_orders; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.purchase_orders (
    id bigint NOT NULL,
    po_number character varying(255) NOT NULL,
    slug character varying(255) NOT NULL,
    supplier_id bigint NOT NULL,
    user_id bigint NOT NULL,
    po_date timestamp(0) without time zone NOT NULL,
    expected_date timestamp(0) without time zone,
    user_ack_id bigint,
    ack_date timestamp(0) without time zone,
    user_reject_id bigint,
    reject_date timestamp(0) without time zone,
    subtotal bigint DEFAULT '0'::bigint NOT NULL,
    tax bigint DEFAULT '0'::bigint NOT NULL,
    discount bigint DEFAULT '0'::bigint NOT NULL,
    total_net bigint DEFAULT '0'::bigint NOT NULL,
    status character varying(255) DEFAULT 'PROSESS'::character varying NOT NULL,
    notes text,
    is_active boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    CONSTRAINT purchase_orders_status_check CHECK (((status)::text = ANY ((ARRAY['PROSESS'::character varying, 'RECEIVED'::character varying, 'CANCELED'::character varying])::text[])))
);


ALTER TABLE public.purchase_orders OWNER TO postgres;

--
-- Name: purchase_orders_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.purchase_orders_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.purchase_orders_id_seq OWNER TO postgres;

--
-- Name: purchase_orders_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.purchase_orders_id_seq OWNED BY public.purchase_orders.id;


--
-- Name: sessions; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.sessions (
    id character varying(255) NOT NULL,
    user_id bigint,
    ip_address character varying(45),
    user_agent text,
    payload text NOT NULL,
    last_activity integer NOT NULL
);


ALTER TABLE public.sessions OWNER TO postgres;

--
-- Name: stock_card_details; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.stock_card_details (
    id bigint NOT NULL,
    stock_card_id bigint NOT NULL,
    reference_id bigint NOT NULL,
    reference_type character varying(255) NOT NULL,
    reference_status character varying(255) NOT NULL,
    unit_id bigint NOT NULL,
    transaction_date timestamp(0) without time zone NOT NULL,
    movement_type character varying(255) NOT NULL,
    quantity bigint NOT NULL,
    base_quantity bigint NOT NULL,
    balance_quantity bigint NOT NULL,
    balance_base_quantity bigint NOT NULL,
    notes character varying(255) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    CONSTRAINT stock_card_details_movement_type_check CHECK (((movement_type)::text = ANY ((ARRAY['MASTER_BARU'::character varying, 'PROSESS'::character varying, 'MASUK'::character varying, 'KELUAR'::character varying])::text[]))),
    CONSTRAINT stock_card_details_reference_status_check CHECK (((reference_status)::text = ANY ((ARRAY['MASTER_BARU'::character varying, 'PEMBELIAN_BARANG'::character varying, 'PENJUALAN'::character varying, 'PENERIMAAN_BARANG'::character varying, 'PENGELUARAN_BARANG'::character varying, 'STOCK_OPNAME'::character varying, 'TUTUP_PERIODE'::character varying])::text[])))
);


ALTER TABLE public.stock_card_details OWNER TO postgres;

--
-- Name: stock_card_details_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.stock_card_details_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.stock_card_details_id_seq OWNER TO postgres;

--
-- Name: stock_card_details_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.stock_card_details_id_seq OWNED BY public.stock_card_details.id;


--
-- Name: stock_cards; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.stock_cards (
    id bigint NOT NULL,
    product_id bigint NOT NULL,
    beginning_balance bigint DEFAULT '0'::bigint NOT NULL,
    in_balance bigint DEFAULT '0'::bigint NOT NULL,
    out_balance bigint DEFAULT '0'::bigint NOT NULL,
    ending_balance bigint DEFAULT '0'::bigint NOT NULL,
    beginning_base_balance bigint DEFAULT '0'::bigint NOT NULL,
    in_base_balance bigint DEFAULT '0'::bigint NOT NULL,
    out_base_balance bigint DEFAULT '0'::bigint NOT NULL,
    ending_base_balance bigint DEFAULT '0'::bigint NOT NULL,
    slug character varying(255) NOT NULL,
    month smallint NOT NULL,
    year integer NOT NULL,
    status_running character varying(255) DEFAULT 'SEDANG_PROSES'::character varying NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    CONSTRAINT stock_cards_status_running_check CHECK (((status_running)::text = ANY ((ARRAY['MASTER_BARU'::character varying, 'SEDANG_PROSES'::character varying, 'SEDANG_BERJALAN'::character varying, 'STOCK_OPNAME'::character varying, 'SUDAH_BERAKHIR'::character varying])::text[])))
);


ALTER TABLE public.stock_cards OWNER TO postgres;

--
-- Name: stock_cards_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.stock_cards_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.stock_cards_id_seq OWNER TO postgres;

--
-- Name: stock_cards_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.stock_cards_id_seq OWNED BY public.stock_cards.id;


--
-- Name: stock_opname_details; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.stock_opname_details (
    id bigint NOT NULL,
    stock_opname_id bigint NOT NULL,
    product_id bigint NOT NULL,
    unit_id bigint NOT NULL,
    system_stock integer DEFAULT 0 NOT NULL,
    system_stock_base integer DEFAULT 0 NOT NULL,
    physical_stock integer DEFAULT 0 NOT NULL,
    physical_stock_base integer DEFAULT 0 NOT NULL,
    difference_stock integer DEFAULT 0 NOT NULL,
    difference_stock_base integer DEFAULT 0 NOT NULL,
    price integer DEFAULT 0 NOT NULL,
    subtotal integer DEFAULT 0 NOT NULL,
    status character varying(255) DEFAULT 'MATCH'::character varying NOT NULL,
    notes character varying(255),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    CONSTRAINT stock_opname_details_status_check CHECK (((status)::text = ANY ((ARRAY['MATCH'::character varying, 'SHORTAGE'::character varying, 'OVERSTOCK'::character varying])::text[])))
);


ALTER TABLE public.stock_opname_details OWNER TO postgres;

--
-- Name: stock_opname_details_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.stock_opname_details_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.stock_opname_details_id_seq OWNER TO postgres;

--
-- Name: stock_opname_details_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.stock_opname_details_id_seq OWNED BY public.stock_opname_details.id;


--
-- Name: stock_opnames; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.stock_opnames (
    id bigint NOT NULL,
    so_number character varying(255) NOT NULL,
    month smallint NOT NULL,
    year integer NOT NULL,
    slug character varying(255) NOT NULL,
    user_id bigint NOT NULL,
    so_date timestamp(0) without time zone NOT NULL,
    user_validator_id bigint,
    validator_date timestamp(0) without time zone,
    user_ack_id bigint,
    ack_date timestamp(0) without time zone,
    user_reject_id bigint,
    reject_date timestamp(0) without time zone,
    subtotal integer DEFAULT 0 NOT NULL,
    tax integer DEFAULT 0 NOT NULL,
    discount integer DEFAULT 0 NOT NULL,
    total_net integer DEFAULT 0 NOT NULL,
    status character varying(255) DEFAULT 'DRAFT'::character varying NOT NULL,
    notes text,
    is_locked boolean DEFAULT false NOT NULL,
    is_active boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    CONSTRAINT stock_opnames_status_check CHECK (((status)::text = ANY ((ARRAY['DRAFT'::character varying, 'VALIDATED'::character varying, 'COMPLETED'::character varying, 'CANCELED'::character varying])::text[])))
);


ALTER TABLE public.stock_opnames OWNER TO postgres;

--
-- Name: stock_opnames_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.stock_opnames_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.stock_opnames_id_seq OWNER TO postgres;

--
-- Name: stock_opnames_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.stock_opnames_id_seq OWNED BY public.stock_opnames.id;


--
-- Name: stock_out_details; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.stock_out_details (
    id bigint NOT NULL,
    stock_out_id bigint NOT NULL,
    product_id bigint NOT NULL,
    unit_id bigint NOT NULL,
    quantity integer DEFAULT 0 NOT NULL,
    base_quantity bigint DEFAULT '0'::bigint NOT NULL,
    price bigint DEFAULT '0'::bigint NOT NULL,
    subtotal bigint DEFAULT '0'::bigint NOT NULL,
    received_quantity bigint DEFAULT '0'::bigint NOT NULL,
    received_base_quantity bigint DEFAULT '0'::bigint NOT NULL,
    notes_detail character varying(255) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.stock_out_details OWNER TO postgres;

--
-- Name: stock_out_details_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.stock_out_details_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.stock_out_details_id_seq OWNER TO postgres;

--
-- Name: stock_out_details_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.stock_out_details_id_seq OWNED BY public.stock_out_details.id;


--
-- Name: stock_outs; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.stock_outs (
    id bigint NOT NULL,
    stock_out_number character varying(255) NOT NULL,
    slug character varying(255) NOT NULL,
    supplier_id bigint,
    user_id bigint NOT NULL,
    out_date timestamp(0) without time zone NOT NULL,
    user_ack_id bigint,
    ack_date timestamp(0) without time zone,
    user_reject_id bigint,
    reject_date timestamp(0) without time zone,
    subtotal bigint DEFAULT '0'::bigint NOT NULL,
    tax bigint DEFAULT '0'::bigint NOT NULL,
    discount bigint DEFAULT '0'::bigint NOT NULL,
    total_net bigint DEFAULT '0'::bigint NOT NULL,
    status_notes character varying(255) NOT NULL,
    status character varying(255) NOT NULL,
    notes text NOT NULL,
    is_active boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    CONSTRAINT stock_outs_status_check CHECK (((status)::text = ANY ((ARRAY['PROSESS'::character varying, 'RECEIVED'::character varying, 'CANCELED'::character varying])::text[]))),
    CONSTRAINT stock_outs_status_notes_check CHECK (((status_notes)::text = ANY ((ARRAY['BARANG_RUSAK'::character varying, 'PEMAKAIAN_INTERNAL'::character varying, 'PEMAKAIAN_EKSTERNAL'::character varying])::text[])))
);


ALTER TABLE public.stock_outs OWNER TO postgres;

--
-- Name: stock_outs_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.stock_outs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.stock_outs_id_seq OWNER TO postgres;

--
-- Name: stock_outs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.stock_outs_id_seq OWNED BY public.stock_outs.id;


--
-- Name: suppliers; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.suppliers (
    id bigint NOT NULL,
    code character varying(255) NOT NULL,
    slug character varying(255) NOT NULL,
    name character varying(255) NOT NULL,
    phone character varying(255),
    email character varying(255),
    address text,
    is_active boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.suppliers OWNER TO postgres;

--
-- Name: suppliers_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.suppliers_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.suppliers_id_seq OWNER TO postgres;

--
-- Name: suppliers_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.suppliers_id_seq OWNED BY public.suppliers.id;


--
-- Name: unit_conversions; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.unit_conversions (
    id bigint NOT NULL,
    product_id bigint NOT NULL,
    from_unit_id bigint NOT NULL,
    to_unit_id bigint NOT NULL,
    slug character varying(255) NOT NULL,
    conversion_factor bigint DEFAULT '0'::bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.unit_conversions OWNER TO postgres;

--
-- Name: unit_conversions_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.unit_conversions_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.unit_conversions_id_seq OWNER TO postgres;

--
-- Name: unit_conversions_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.unit_conversions_id_seq OWNED BY public.unit_conversions.id;


--
-- Name: units; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.units (
    id bigint NOT NULL,
    code character varying(255) NOT NULL,
    slug character varying(255) NOT NULL,
    name character varying(255) NOT NULL,
    is_active boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


ALTER TABLE public.units OWNER TO postgres;

--
-- Name: units_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.units_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.units_id_seq OWNER TO postgres;

--
-- Name: units_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.units_id_seq OWNED BY public.units.id;


--
-- Name: users; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.users (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    email character varying(255) NOT NULL,
    email_verified_at timestamp(0) without time zone,
    password character varying(255) NOT NULL,
    remember_token character varying(100),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.users OWNER TO postgres;

--
-- Name: users_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.users_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.users_id_seq OWNER TO postgres;

--
-- Name: users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.users_id_seq OWNED BY public.users.id;


--
-- Name: current_stocks id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.current_stocks ALTER COLUMN id SET DEFAULT nextval('public.current_stocks_id_seq'::regclass);


--
-- Name: customers id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.customers ALTER COLUMN id SET DEFAULT nextval('public.customers_id_seq'::regclass);


--
-- Name: failed_jobs id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.failed_jobs ALTER COLUMN id SET DEFAULT nextval('public.failed_jobs_id_seq'::regclass);


--
-- Name: good_receipt_details id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.good_receipt_details ALTER COLUMN id SET DEFAULT nextval('public.good_receipt_details_id_seq'::regclass);


--
-- Name: good_receipts id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.good_receipts ALTER COLUMN id SET DEFAULT nextval('public.good_receipts_id_seq'::regclass);


--
-- Name: jobs id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.jobs ALTER COLUMN id SET DEFAULT nextval('public.jobs_id_seq'::regclass);


--
-- Name: migrations id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.migrations ALTER COLUMN id SET DEFAULT nextval('public.migrations_id_seq'::regclass);


--
-- Name: products id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.products ALTER COLUMN id SET DEFAULT nextval('public.products_id_seq'::regclass);


--
-- Name: purchase_order_details id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.purchase_order_details ALTER COLUMN id SET DEFAULT nextval('public.purchase_order_details_id_seq'::regclass);


--
-- Name: purchase_orders id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.purchase_orders ALTER COLUMN id SET DEFAULT nextval('public.purchase_orders_id_seq'::regclass);


--
-- Name: stock_card_details id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.stock_card_details ALTER COLUMN id SET DEFAULT nextval('public.stock_card_details_id_seq'::regclass);


--
-- Name: stock_cards id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.stock_cards ALTER COLUMN id SET DEFAULT nextval('public.stock_cards_id_seq'::regclass);


--
-- Name: stock_opname_details id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.stock_opname_details ALTER COLUMN id SET DEFAULT nextval('public.stock_opname_details_id_seq'::regclass);


--
-- Name: stock_opnames id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.stock_opnames ALTER COLUMN id SET DEFAULT nextval('public.stock_opnames_id_seq'::regclass);


--
-- Name: stock_out_details id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.stock_out_details ALTER COLUMN id SET DEFAULT nextval('public.stock_out_details_id_seq'::regclass);


--
-- Name: stock_outs id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.stock_outs ALTER COLUMN id SET DEFAULT nextval('public.stock_outs_id_seq'::regclass);


--
-- Name: suppliers id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.suppliers ALTER COLUMN id SET DEFAULT nextval('public.suppliers_id_seq'::regclass);


--
-- Name: unit_conversions id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.unit_conversions ALTER COLUMN id SET DEFAULT nextval('public.unit_conversions_id_seq'::regclass);


--
-- Name: units id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.units ALTER COLUMN id SET DEFAULT nextval('public.units_id_seq'::regclass);


--
-- Name: users id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users ALTER COLUMN id SET DEFAULT nextval('public.users_id_seq'::regclass);


--
-- Data for Name: cache; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.cache (key, value, expiration) FROM stdin;
\.


--
-- Data for Name: cache_locks; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.cache_locks (key, owner, expiration) FROM stdin;
\.


--
-- Data for Name: current_stocks; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.current_stocks (id, product_id, unit_id, quantity, base_quantity, slug, month, year, status_running, created_at, updated_at, deleted_at) FROM stdin;
1	1	9	17	850	current-stock-nrm-may-2025	5	2025	SEDANG_BERJALAN	2025-05-07 06:52:16	2025-05-07 06:58:18	\N
2	2	9	17	850	current-stock-ge-may-2025	5	2025	SEDANG_BERJALAN	2025-05-07 06:52:16	2025-05-07 06:58:18	\N
3	3	9	17	850	current-stock-sdr-may-2025	5	2025	SEDANG_BERJALAN	2025-05-07 06:52:16	2025-05-07 06:58:18	\N
\.


--
-- Data for Name: customers; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.customers (id, name, slug, phone, is_active, created_at, updated_at, deleted_at) FROM stdin;
\.


--
-- Data for Name: failed_jobs; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.failed_jobs (id, uuid, connection, queue, payload, exception, failed_at) FROM stdin;
\.


--
-- Data for Name: good_receipt_details; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.good_receipt_details (id, good_receipt_id, purchase_order_detail_id, product_id, unit_id, quantity, base_quantity, price, subtotal, received_quantity, received_base_quantity, created_at, updated_at, deleted_at) FROM stdin;
1	1	1	1	9	20	1000	45000	900000	20	1000	2025-05-07 06:56:23	2025-05-07 06:56:23	\N
2	1	2	2	9	20	1000	55000	1100000	20	1000	2025-05-07 06:56:23	2025-05-07 06:56:23	\N
3	1	3	3	9	20	1000	50000	1000000	20	1000	2025-05-07 06:56:23	2025-05-07 06:56:23	\N
\.


--
-- Data for Name: good_receipts; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.good_receipts (id, receipt_number, slug, purchase_order_id, supplier_id, user_id, receipt_date, user_ack_id, ack_date, user_reject_id, reject_date, subtotal, tax, discount, total_net, status_receipt, notes, is_active, created_at, updated_at, deleted_at) FROM stdin;
1	INV/GRM/20250507/00001	invgrm2025050700001-1	1	1	1	2025-05-07 06:56:23	1	2025-05-07 06:56:34	\N	\N	3000000	0	0	3000000	RECEIVED	\N	t	2025-05-07 06:56:23	2025-05-07 06:56:35	\N
\.


--
-- Data for Name: job_batches; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.job_batches (id, name, total_jobs, pending_jobs, failed_jobs, failed_job_ids, options, cancelled_at, created_at, finished_at) FROM stdin;
\.


--
-- Data for Name: jobs; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.jobs (id, queue, payload, attempts, reserved_at, available_at, created_at) FROM stdin;
\.


--
-- Data for Name: migrations; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.migrations (id, migration, batch) FROM stdin;
353	0001_01_01_000000_create_users_table	1
354	0001_01_01_000001_create_cache_table	1
355	0001_01_01_000002_create_jobs_table	1
356	2025_04_18_080359_create_units_table	1
357	2025_04_19_080329_create_products_table	1
358	2025_04_19_083041_create_unit_conversions_table	1
359	2025_04_26_095117_create_suppliers_table	1
360	2025_04_26_105845_create_customers_table	1
361	2025_04_26_151127_create_purchase_orders_table	1
362	2025_04_26_151138_create_purchase_order_details_table	1
363	2025_04_29_163632_create_stock_cards_table	1
364	2025_04_30_010131_create_stock_card_details_table	1
365	2025_04_30_015650_create_current_stocks_table	1
366	2025_05_01_120721_create_good_receipts_table	1
367	2025_05_01_122428_create_good_receipt_details_table	1
368	2025_05_03_144413_create_stock_outs_table	1
369	2025_05_04_020422_create_stock_out_details_table	1
370	2025_05_05_095631_create_stock_opnames_table	1
371	2025_05_05_095642_create_stock_opname_details_table	1
\.


--
-- Data for Name: password_reset_tokens; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.password_reset_tokens (email, token, created_at) FROM stdin;
\.


--
-- Data for Name: products; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.products (id, code, name, slug, variant_name, default_unit_id, purchase_price, selling_price, description, is_active, created_at, updated_at, deleted_at) FROM stdin;
1	NRM	Nurma	nrm-nurma-black-opium	Black Opium	9	45000	50000	Parfume Nurma Best Seller	t	2025-05-07 06:52:16	2025-05-07 06:52:16	\N
2	GE	Gee	ge-gee-ysl	YSL	9	55000	60000	Parfume Gee Best Seller	t	2025-05-07 06:52:16	2025-05-07 06:52:16	\N
3	SDR	Sandra	sdr-sandra-ysl-axe	YSL Axe	9	50000	55000	Sandra Best Seller	t	2025-05-07 06:52:16	2025-05-07 06:54:38	\N
\.


--
-- Data for Name: purchase_order_details; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.purchase_order_details (id, purchase_order_id, product_id, unit_id, quantity, base_quantity, price, subtotal, received_quantity, received_base_quantity, created_at, updated_at, deleted_at) FROM stdin;
1	1	1	9	20	1000	45000	900000	20	1000	2025-05-07 06:55:55	2025-05-07 06:55:55	\N
2	1	2	9	20	1000	55000	1100000	20	1000	2025-05-07 06:55:55	2025-05-07 06:55:55	\N
3	1	3	9	20	1000	50000	1000000	20	1000	2025-05-07 06:55:55	2025-05-07 06:55:55	\N
\.


--
-- Data for Name: purchase_orders; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.purchase_orders (id, po_number, slug, supplier_id, user_id, po_date, expected_date, user_ack_id, ack_date, user_reject_id, reject_date, subtotal, tax, discount, total_net, status, notes, is_active, created_at, updated_at, deleted_at) FROM stdin;
1	INV/PO/20250507/00001	invpo2025050700001-1	1	1	2025-05-07 06:55:54	2025-05-07 06:55:54	1	2025-05-07 06:56:07	\N	\N	3000000	0	0	3000000	RECEIVED	\N	t	2025-05-07 06:55:55	2025-05-07 06:56:07	\N
\.


--
-- Data for Name: sessions; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.sessions (id, user_id, ip_address, user_agent, payload, last_activity) FROM stdin;
7IQ7XLw8JDjnw0eFcXkhz18M8sufSdTljT3GXcxy	1	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36	YTo0OntzOjY6Il90b2tlbiI7czo0MDoiMmNpSElnZjNLTkpreVNleVBlZ0FHYlZ1WDJJRnU4WDZsWWFROUhnYSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6OTE6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9hZG1pbi9pbnZlbnRvcnkvc2VhcmNoX2J5X25vX3BvP3BvX251bWJlcj1JTlYlMkZQTyUyRjIwMjUwNTA3JTJGMDAwMDEiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO30=	1746601113
\.


--
-- Data for Name: stock_card_details; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.stock_card_details (id, stock_card_id, reference_id, reference_type, reference_status, unit_id, transaction_date, movement_type, quantity, base_quantity, balance_quantity, balance_base_quantity, notes, created_at, updated_at, deleted_at) FROM stdin;
1	1	1	App\\Models\\Product	MASTER_BARU	9	2025-05-07 06:52:16	MASTER_BARU	0	0	0	0	Pembuatan Master Produk Baru	2025-05-07 06:52:16	2025-05-07 06:52:16	\N
2	2	2	App\\Models\\Product	MASTER_BARU	9	2025-05-07 06:52:16	MASTER_BARU	0	0	0	0	Pembuatan Master Produk Baru	2025-05-07 06:52:16	2025-05-07 06:52:16	\N
3	3	3	App\\Models\\Product	MASTER_BARU	9	2025-05-07 06:52:16	MASTER_BARU	0	0	0	0	Pembuatan Master Produk Baru	2025-05-07 06:52:16	2025-05-07 06:52:16	\N
4	1	1	App\\Models\\PurchaseOrderDetail	PEMBELIAN_BARANG	9	2025-05-07 06:56:07	PROSESS	0	0	0	0	PO @INV/PO/20250507/00001	2025-05-07 06:56:07	2025-05-07 06:56:07	\N
5	2	2	App\\Models\\PurchaseOrderDetail	PEMBELIAN_BARANG	9	2025-05-07 06:56:07	PROSESS	0	0	0	0	PO @INV/PO/20250507/00001	2025-05-07 06:56:07	2025-05-07 06:56:07	\N
6	3	3	App\\Models\\PurchaseOrderDetail	PEMBELIAN_BARANG	9	2025-05-07 06:56:07	PROSESS	0	0	0	0	PO @INV/PO/20250507/00001	2025-05-07 06:56:07	2025-05-07 06:56:07	\N
7	1	1	App\\Models\\GoodReceiptDetail	PENERIMAAN_BARANG	9	2025-05-07 06:56:34	MASUK	20	1000	20	1000	GRM @INV/GRM/20250507/00001	2025-05-07 06:56:35	2025-05-07 06:56:35	\N
8	2	2	App\\Models\\GoodReceiptDetail	PENERIMAAN_BARANG	9	2025-05-07 06:56:34	MASUK	20	1000	20	1000	GRM @INV/GRM/20250507/00001	2025-05-07 06:56:35	2025-05-07 06:56:35	\N
9	3	3	App\\Models\\GoodReceiptDetail	PENERIMAAN_BARANG	9	2025-05-07 06:56:34	MASUK	20	1000	20	1000	GRM @INV/GRM/20250507/00001	2025-05-07 06:56:35	2025-05-07 06:56:35	\N
10	1	1	App\\Models\\StockOutDetail	PENGELUARAN_BARANG	9	2025-05-07 06:58:18	KELUAR	3	150	17	850	GDN @INV/GDN/20250507/00001	2025-05-07 06:58:18	2025-05-07 06:58:18	\N
11	2	2	App\\Models\\StockOutDetail	PENGELUARAN_BARANG	9	2025-05-07 06:58:18	KELUAR	3	150	17	850	GDN @INV/GDN/20250507/00001	2025-05-07 06:58:18	2025-05-07 06:58:18	\N
12	3	3	App\\Models\\StockOutDetail	PENGELUARAN_BARANG	9	2025-05-07 06:58:18	KELUAR	3	150	17	850	GDN @INV/GDN/20250507/00001	2025-05-07 06:58:18	2025-05-07 06:58:18	\N
\.


--
-- Data for Name: stock_cards; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.stock_cards (id, product_id, beginning_balance, in_balance, out_balance, ending_balance, beginning_base_balance, in_base_balance, out_base_balance, ending_base_balance, slug, month, year, status_running, created_at, updated_at, deleted_at) FROM stdin;
1	1	0	20	3	17	0	1000	150	850	card-stock-nrm-may-2025	5	2025	SEDANG_BERJALAN	2025-05-07 06:52:16	2025-05-07 06:58:18	\N
2	2	0	20	3	17	0	1000	150	850	card-stock-ge-may-2025	5	2025	SEDANG_BERJALAN	2025-05-07 06:52:16	2025-05-07 06:58:18	\N
3	3	0	20	3	17	0	1000	150	850	card-stock-sdr-may-2025	5	2025	SEDANG_BERJALAN	2025-05-07 06:52:16	2025-05-07 06:58:18	\N
\.


--
-- Data for Name: stock_opname_details; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.stock_opname_details (id, stock_opname_id, product_id, unit_id, system_stock, system_stock_base, physical_stock, physical_stock_base, difference_stock, difference_stock_base, price, subtotal, status, notes, created_at, updated_at, deleted_at) FROM stdin;
\.


--
-- Data for Name: stock_opnames; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.stock_opnames (id, so_number, month, year, slug, user_id, so_date, user_validator_id, validator_date, user_ack_id, ack_date, user_reject_id, reject_date, subtotal, tax, discount, total_net, status, notes, is_locked, is_active, created_at, updated_at, deleted_at) FROM stdin;
\.


--
-- Data for Name: stock_out_details; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.stock_out_details (id, stock_out_id, product_id, unit_id, quantity, base_quantity, price, subtotal, received_quantity, received_base_quantity, notes_detail, created_at, updated_at, deleted_at) FROM stdin;
1	1	1	9	3	150	45000	135000	3	150	Dipakai sendiri	2025-05-07 06:58:06	2025-05-07 06:58:06	\N
2	1	2	9	3	150	55000	165000	3	150	Dipakai sendiri	2025-05-07 06:58:06	2025-05-07 06:58:06	\N
3	1	3	9	3	150	50000	150000	3	150	Dipakai sendiri	2025-05-07 06:58:06	2025-05-07 06:58:06	\N
\.


--
-- Data for Name: stock_outs; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.stock_outs (id, stock_out_number, slug, supplier_id, user_id, out_date, user_ack_id, ack_date, user_reject_id, reject_date, subtotal, tax, discount, total_net, status_notes, status, notes, is_active, created_at, updated_at, deleted_at) FROM stdin;
1	INV/GDN/20250507/00001	invgdn2025050700001	\N	1	2025-05-07 06:58:05	1	2025-05-07 06:58:18	\N	\N	450000	0	0	450000	PEMAKAIAN_INTERNAL	RECEIVED	Dipakai sendiri	t	2025-05-07 06:58:05	2025-05-07 06:58:18	\N
\.


--
-- Data for Name: suppliers; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.suppliers (id, code, slug, name, phone, email, address, is_active, created_at, updated_at, deleted_at) FROM stdin;
1	SU01	su01-pt-sumber-makmur	PT Sumber Makmur	081234567890	makmur@example.com	Jl. Merdeka No. 1, Jakarta	t	2025-05-07 06:52:16	2025-05-07 06:52:16	\N
2	SU02	su02-cv-jaya-abadi	CV Jaya Abadi	082345678901	jayaabadi@example.com	Jl. Sudirman No. 45, Bandung	t	2025-05-07 06:52:16	2025-05-07 06:52:16	\N
3	SU03	su03-ud-sentosa	UD Sentosa	083456789012	sentosa@example.com	Jl. Pahlawan No. 10, Surabaya	f	2025-05-07 06:52:16	2025-05-07 06:52:16	\N
\.


--
-- Data for Name: unit_conversions; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.unit_conversions (id, product_id, from_unit_id, to_unit_id, slug, conversion_factor, created_at, updated_at, deleted_at) FROM stdin;
1	1	9	1	convert1-9-1	50	2025-05-07 06:54:48	2025-05-07 06:54:48	\N
2	2	9	1	convert2-9-1	50	2025-05-07 06:54:59	2025-05-07 06:54:59	\N
3	3	9	1	convert3-9-1	50	2025-05-07 06:55:07	2025-05-07 06:55:07	\N
\.


--
-- Data for Name: units; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.units (id, code, slug, name, is_active, created_at, updated_at, deleted_at) FROM stdin;
1	ml	milliliter	Milliliter	t	2025-05-07 06:52:15	2025-05-07 06:52:15	\N
2	l	liter	Liter	t	2025-05-07 06:52:15	2025-05-07 06:52:15	\N
3	g	gram	Gram	t	2025-05-07 06:52:15	2025-05-07 06:52:15	\N
4	kg	kilogram	Kilogram	t	2025-05-07 06:52:15	2025-05-07 06:52:15	\N
5	pcs	pieces	Pieces	t	2025-05-07 06:52:15	2025-05-07 06:52:15	\N
6	box	box	Box	t	2025-05-07 06:52:15	2025-05-07 06:52:15	\N
7	pack	pack	Pack	t	2025-05-07 06:52:15	2025-05-07 06:52:15	\N
8	set	set	Set	t	2025-05-07 06:52:15	2025-05-07 06:52:15	\N
9	btl	bottle	Bottle	t	2025-05-07 06:52:15	2025-05-07 06:52:15	\N
10	tube	tube	Tube	t	2025-05-07 06:52:15	2025-05-07 06:52:15	\N
11	sct	sachet	Sachet	t	2025-05-07 06:52:15	2025-05-07 06:52:15	\N
12	ctn	carton	Carton	t	2025-05-07 06:52:15	2025-05-07 06:52:15	\N
13	can	can	Can	t	2025-05-07 06:52:15	2025-05-07 06:52:15	\N
14	jar	jar	Jar	t	2025-05-07 06:52:15	2025-05-07 06:52:15	\N
15	unit	unit	Unit	t	2025-05-07 06:52:15	2025-05-07 06:52:15	\N
\.


--
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.users (id, name, email, email_verified_at, password, remember_token, created_at, updated_at) FROM stdin;
1	ADMIN LAVILLE	admin@gmail.com	2025-05-07 06:52:15	$2y$12$cIudwVhAWqBTvu0oTtAtlOIFX3gmpUzVrUYkh93r8pCAeLi/EP.OG	Vis2gYSBDs	2025-05-07 06:52:16	2025-05-07 06:52:16
\.


--
-- Name: current_stocks_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.current_stocks_id_seq', 3, true);


--
-- Name: customers_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.customers_id_seq', 1, false);


--
-- Name: failed_jobs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.failed_jobs_id_seq', 1, false);


--
-- Name: good_receipt_details_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.good_receipt_details_id_seq', 3, true);


--
-- Name: good_receipts_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.good_receipts_id_seq', 1, true);


--
-- Name: jobs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.jobs_id_seq', 1, false);


--
-- Name: migrations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.migrations_id_seq', 371, true);


--
-- Name: products_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.products_id_seq', 3, true);


--
-- Name: purchase_order_details_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.purchase_order_details_id_seq', 3, true);


--
-- Name: purchase_orders_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.purchase_orders_id_seq', 1, true);


--
-- Name: stock_card_details_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.stock_card_details_id_seq', 12, true);


--
-- Name: stock_cards_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.stock_cards_id_seq', 3, true);


--
-- Name: stock_opname_details_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.stock_opname_details_id_seq', 1, false);


--
-- Name: stock_opnames_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.stock_opnames_id_seq', 1, false);


--
-- Name: stock_out_details_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.stock_out_details_id_seq', 3, true);


--
-- Name: stock_outs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.stock_outs_id_seq', 1, true);


--
-- Name: suppliers_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.suppliers_id_seq', 3, true);


--
-- Name: unit_conversions_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.unit_conversions_id_seq', 3, true);


--
-- Name: units_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.units_id_seq', 15, true);


--
-- Name: users_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.users_id_seq', 1, true);


--
-- Name: cache_locks cache_locks_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cache_locks
    ADD CONSTRAINT cache_locks_pkey PRIMARY KEY (key);


--
-- Name: cache cache_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cache
    ADD CONSTRAINT cache_pkey PRIMARY KEY (key);


--
-- Name: current_stocks current_stocks_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.current_stocks
    ADD CONSTRAINT current_stocks_pkey PRIMARY KEY (id);


--
-- Name: customers customers_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.customers
    ADD CONSTRAINT customers_pkey PRIMARY KEY (id);


--
-- Name: failed_jobs failed_jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_pkey PRIMARY KEY (id);


--
-- Name: failed_jobs failed_jobs_uuid_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_uuid_unique UNIQUE (uuid);


--
-- Name: good_receipt_details good_receipt_details_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.good_receipt_details
    ADD CONSTRAINT good_receipt_details_pkey PRIMARY KEY (id);


--
-- Name: good_receipts good_receipts_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.good_receipts
    ADD CONSTRAINT good_receipts_pkey PRIMARY KEY (id);


--
-- Name: good_receipts good_receipts_receipt_number_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.good_receipts
    ADD CONSTRAINT good_receipts_receipt_number_unique UNIQUE (receipt_number);


--
-- Name: job_batches job_batches_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.job_batches
    ADD CONSTRAINT job_batches_pkey PRIMARY KEY (id);


--
-- Name: jobs jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.jobs
    ADD CONSTRAINT jobs_pkey PRIMARY KEY (id);


--
-- Name: migrations migrations_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.migrations
    ADD CONSTRAINT migrations_pkey PRIMARY KEY (id);


--
-- Name: password_reset_tokens password_reset_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.password_reset_tokens
    ADD CONSTRAINT password_reset_tokens_pkey PRIMARY KEY (email);


--
-- Name: products products_code_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.products
    ADD CONSTRAINT products_code_unique UNIQUE (code);


--
-- Name: products products_name_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.products
    ADD CONSTRAINT products_name_unique UNIQUE (name);


--
-- Name: products products_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.products
    ADD CONSTRAINT products_pkey PRIMARY KEY (id);


--
-- Name: purchase_order_details purchase_order_details_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.purchase_order_details
    ADD CONSTRAINT purchase_order_details_pkey PRIMARY KEY (id);


--
-- Name: purchase_orders purchase_orders_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.purchase_orders
    ADD CONSTRAINT purchase_orders_pkey PRIMARY KEY (id);


--
-- Name: purchase_orders purchase_orders_po_number_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.purchase_orders
    ADD CONSTRAINT purchase_orders_po_number_unique UNIQUE (po_number);


--
-- Name: sessions sessions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sessions
    ADD CONSTRAINT sessions_pkey PRIMARY KEY (id);


--
-- Name: stock_card_details stock_card_details_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.stock_card_details
    ADD CONSTRAINT stock_card_details_pkey PRIMARY KEY (id);


--
-- Name: stock_cards stock_cards_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.stock_cards
    ADD CONSTRAINT stock_cards_pkey PRIMARY KEY (id);


--
-- Name: stock_opname_details stock_opname_details_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.stock_opname_details
    ADD CONSTRAINT stock_opname_details_pkey PRIMARY KEY (id);


--
-- Name: stock_opnames stock_opnames_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.stock_opnames
    ADD CONSTRAINT stock_opnames_pkey PRIMARY KEY (id);


--
-- Name: stock_opnames stock_opnames_so_number_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.stock_opnames
    ADD CONSTRAINT stock_opnames_so_number_unique UNIQUE (so_number);


--
-- Name: stock_out_details stock_out_details_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.stock_out_details
    ADD CONSTRAINT stock_out_details_pkey PRIMARY KEY (id);


--
-- Name: stock_outs stock_outs_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.stock_outs
    ADD CONSTRAINT stock_outs_pkey PRIMARY KEY (id);


--
-- Name: stock_outs stock_outs_stock_out_number_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.stock_outs
    ADD CONSTRAINT stock_outs_stock_out_number_unique UNIQUE (stock_out_number);


--
-- Name: suppliers suppliers_code_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.suppliers
    ADD CONSTRAINT suppliers_code_unique UNIQUE (code);


--
-- Name: suppliers suppliers_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.suppliers
    ADD CONSTRAINT suppliers_pkey PRIMARY KEY (id);


--
-- Name: unit_conversions unit_conversions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.unit_conversions
    ADD CONSTRAINT unit_conversions_pkey PRIMARY KEY (id);


--
-- Name: units units_code_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.units
    ADD CONSTRAINT units_code_unique UNIQUE (code);


--
-- Name: units units_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.units
    ADD CONSTRAINT units_pkey PRIMARY KEY (id);


--
-- Name: users users_email_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_email_unique UNIQUE (email);


--
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- Name: current_stocks_product_id_unit_id_month_year_status_running_ind; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX current_stocks_product_id_unit_id_month_year_status_running_ind ON public.current_stocks USING btree (product_id, unit_id, month, year, status_running);


--
-- Name: good_receipt_details_good_receipt_id_purchase_order_detail_id_p; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX good_receipt_details_good_receipt_id_purchase_order_detail_id_p ON public.good_receipt_details USING btree (good_receipt_id, purchase_order_detail_id, product_id, unit_id);


--
-- Name: good_receipts_receipt_number_purchase_order_id_supplier_id_rece; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX good_receipts_receipt_number_purchase_order_id_supplier_id_rece ON public.good_receipts USING btree (receipt_number, purchase_order_id, supplier_id, receipt_date, status_receipt);


--
-- Name: jobs_queue_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX jobs_queue_index ON public.jobs USING btree (queue);


--
-- Name: sessions_last_activity_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX sessions_last_activity_index ON public.sessions USING btree (last_activity);


--
-- Name: sessions_user_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX sessions_user_id_index ON public.sessions USING btree (user_id);


--
-- Name: stock_card_details_stock_card_id_reference_id_reference_type_un; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX stock_card_details_stock_card_id_reference_id_reference_type_un ON public.stock_card_details USING btree (stock_card_id, reference_id, reference_type, unit_id, transaction_date, reference_type, movement_type);


--
-- Name: stock_cards_product_id_month_year_status_running_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX stock_cards_product_id_month_year_status_running_index ON public.stock_cards USING btree (product_id, month, year, status_running);


--
-- Name: stock_opnames_so_number_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX stock_opnames_so_number_index ON public.stock_opnames USING btree (so_number);


--
-- Name: stock_outs_stock_out_number_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX stock_outs_stock_out_number_index ON public.stock_outs USING btree (stock_out_number);


--
-- Name: current_stocks current_stocks_product_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.current_stocks
    ADD CONSTRAINT current_stocks_product_id_foreign FOREIGN KEY (product_id) REFERENCES public.products(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: current_stocks current_stocks_unit_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.current_stocks
    ADD CONSTRAINT current_stocks_unit_id_foreign FOREIGN KEY (unit_id) REFERENCES public.units(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: good_receipt_details good_receipt_details_good_receipt_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.good_receipt_details
    ADD CONSTRAINT good_receipt_details_good_receipt_id_foreign FOREIGN KEY (good_receipt_id) REFERENCES public.good_receipts(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: good_receipt_details good_receipt_details_product_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.good_receipt_details
    ADD CONSTRAINT good_receipt_details_product_id_foreign FOREIGN KEY (product_id) REFERENCES public.products(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: good_receipt_details good_receipt_details_purchase_order_detail_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.good_receipt_details
    ADD CONSTRAINT good_receipt_details_purchase_order_detail_id_foreign FOREIGN KEY (purchase_order_detail_id) REFERENCES public.purchase_order_details(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: good_receipt_details good_receipt_details_unit_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.good_receipt_details
    ADD CONSTRAINT good_receipt_details_unit_id_foreign FOREIGN KEY (unit_id) REFERENCES public.units(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: good_receipts good_receipts_purchase_order_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.good_receipts
    ADD CONSTRAINT good_receipts_purchase_order_id_foreign FOREIGN KEY (purchase_order_id) REFERENCES public.purchase_orders(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: good_receipts good_receipts_supplier_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.good_receipts
    ADD CONSTRAINT good_receipts_supplier_id_foreign FOREIGN KEY (supplier_id) REFERENCES public.suppliers(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: good_receipts good_receipts_user_ack_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.good_receipts
    ADD CONSTRAINT good_receipts_user_ack_id_foreign FOREIGN KEY (user_ack_id) REFERENCES public.users(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: good_receipts good_receipts_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.good_receipts
    ADD CONSTRAINT good_receipts_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: good_receipts good_receipts_user_reject_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.good_receipts
    ADD CONSTRAINT good_receipts_user_reject_id_foreign FOREIGN KEY (user_reject_id) REFERENCES public.users(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: products products_default_unit_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.products
    ADD CONSTRAINT products_default_unit_id_foreign FOREIGN KEY (default_unit_id) REFERENCES public.units(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: purchase_order_details purchase_order_details_product_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.purchase_order_details
    ADD CONSTRAINT purchase_order_details_product_id_foreign FOREIGN KEY (product_id) REFERENCES public.products(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: purchase_order_details purchase_order_details_purchase_order_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.purchase_order_details
    ADD CONSTRAINT purchase_order_details_purchase_order_id_foreign FOREIGN KEY (purchase_order_id) REFERENCES public.purchase_orders(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: purchase_order_details purchase_order_details_unit_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.purchase_order_details
    ADD CONSTRAINT purchase_order_details_unit_id_foreign FOREIGN KEY (unit_id) REFERENCES public.units(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: purchase_orders purchase_orders_supplier_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.purchase_orders
    ADD CONSTRAINT purchase_orders_supplier_id_foreign FOREIGN KEY (supplier_id) REFERENCES public.suppliers(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: purchase_orders purchase_orders_user_ack_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.purchase_orders
    ADD CONSTRAINT purchase_orders_user_ack_id_foreign FOREIGN KEY (user_ack_id) REFERENCES public.users(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: purchase_orders purchase_orders_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.purchase_orders
    ADD CONSTRAINT purchase_orders_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: purchase_orders purchase_orders_user_reject_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.purchase_orders
    ADD CONSTRAINT purchase_orders_user_reject_id_foreign FOREIGN KEY (user_reject_id) REFERENCES public.users(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: stock_card_details stock_card_details_stock_card_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.stock_card_details
    ADD CONSTRAINT stock_card_details_stock_card_id_foreign FOREIGN KEY (stock_card_id) REFERENCES public.stock_cards(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: stock_card_details stock_card_details_unit_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.stock_card_details
    ADD CONSTRAINT stock_card_details_unit_id_foreign FOREIGN KEY (unit_id) REFERENCES public.units(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: stock_cards stock_cards_product_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.stock_cards
    ADD CONSTRAINT stock_cards_product_id_foreign FOREIGN KEY (product_id) REFERENCES public.products(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: stock_opname_details stock_opname_details_product_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.stock_opname_details
    ADD CONSTRAINT stock_opname_details_product_id_foreign FOREIGN KEY (product_id) REFERENCES public.products(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: stock_opname_details stock_opname_details_stock_opname_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.stock_opname_details
    ADD CONSTRAINT stock_opname_details_stock_opname_id_foreign FOREIGN KEY (stock_opname_id) REFERENCES public.stock_opnames(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: stock_opname_details stock_opname_details_unit_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.stock_opname_details
    ADD CONSTRAINT stock_opname_details_unit_id_foreign FOREIGN KEY (unit_id) REFERENCES public.units(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: stock_opnames stock_opnames_user_ack_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.stock_opnames
    ADD CONSTRAINT stock_opnames_user_ack_id_foreign FOREIGN KEY (user_ack_id) REFERENCES public.users(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: stock_opnames stock_opnames_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.stock_opnames
    ADD CONSTRAINT stock_opnames_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: stock_opnames stock_opnames_user_reject_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.stock_opnames
    ADD CONSTRAINT stock_opnames_user_reject_id_foreign FOREIGN KEY (user_reject_id) REFERENCES public.users(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: stock_opnames stock_opnames_user_validator_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.stock_opnames
    ADD CONSTRAINT stock_opnames_user_validator_id_foreign FOREIGN KEY (user_validator_id) REFERENCES public.users(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: stock_out_details stock_out_details_product_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.stock_out_details
    ADD CONSTRAINT stock_out_details_product_id_foreign FOREIGN KEY (product_id) REFERENCES public.products(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: stock_out_details stock_out_details_stock_out_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.stock_out_details
    ADD CONSTRAINT stock_out_details_stock_out_id_foreign FOREIGN KEY (stock_out_id) REFERENCES public.stock_outs(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: stock_out_details stock_out_details_unit_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.stock_out_details
    ADD CONSTRAINT stock_out_details_unit_id_foreign FOREIGN KEY (unit_id) REFERENCES public.units(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: stock_outs stock_outs_supplier_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.stock_outs
    ADD CONSTRAINT stock_outs_supplier_id_foreign FOREIGN KEY (supplier_id) REFERENCES public.suppliers(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: stock_outs stock_outs_user_ack_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.stock_outs
    ADD CONSTRAINT stock_outs_user_ack_id_foreign FOREIGN KEY (user_ack_id) REFERENCES public.users(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: stock_outs stock_outs_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.stock_outs
    ADD CONSTRAINT stock_outs_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: stock_outs stock_outs_user_reject_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.stock_outs
    ADD CONSTRAINT stock_outs_user_reject_id_foreign FOREIGN KEY (user_reject_id) REFERENCES public.users(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: unit_conversions unit_conversions_from_unit_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.unit_conversions
    ADD CONSTRAINT unit_conversions_from_unit_id_foreign FOREIGN KEY (from_unit_id) REFERENCES public.units(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: unit_conversions unit_conversions_product_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.unit_conversions
    ADD CONSTRAINT unit_conversions_product_id_foreign FOREIGN KEY (product_id) REFERENCES public.products(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: unit_conversions unit_conversions_to_unit_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.unit_conversions
    ADD CONSTRAINT unit_conversions_to_unit_id_foreign FOREIGN KEY (to_unit_id) REFERENCES public.units(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- PostgreSQL database dump complete
--

