<?php

namespace Paganini\Constants;

class ResponseConstant
{
    /* =========================
     * Base
     * ========================= */

    public const RET_OK = 0;                 // success
    public const RET_ERR = 1;                // generic error
    public const RET_UNKNOWN = 2;            // unknown error
    public const RET_NOT_IMPLEMENTED = 3;    // feature not implemented


    /* =========================
     * Request & Parameters (100–199)
     * ========================= */

    public const RET_INVALID_PARAM = 100;        // invalid parameter
    public const RET_MISSING_PARAM = 101;        // missing required parameter
    public const RET_PARAM_FORMAT_ERROR = 102;   // parameter format error
    public const RET_PARAM_OUT_OF_RANGE = 103;   // parameter out of range
    public const RET_JSON_PARSE_ERROR = 104;     // json parse error


    /* =========================
     * Auth & Permission (200–299)
     * ========================= */

    public const RET_UNAUTHORIZED = 200;         // unauthorized
    public const RET_FORBIDDEN = 201;            // forbidden
    public const RET_LOGIN_REQUIRED = 202;       // login required

    public const RET_TOKEN_MISSING = 210;        // token missing
    public const RET_TOKEN_INVALID = 211;        // token invalid
    public const RET_TOKEN_EXPIRED = 212;        // token expired
    public const RET_TOKEN_REVOKED = 213;        // token revoked


    /* =========================
     * Business Logic (300–399)
     * ========================= */

    public const RET_BUSINESS_ERROR = 300;       // generic business error
    public const RET_RESOURCE_NOT_FOUND = 301;  // resource not found
    public const RET_RESOURCE_EXISTS = 302;     // resource already exists
    public const RET_INVALID_STATE = 303;       // invalid state
    public const RET_DUPLICATE_REQUEST = 304;   // duplicate request
    public const RET_OPERATION_NOT_ALLOWED = 305; // operation not allowed


    /* =========================
     * External Dependency (400–499)
     * ========================= */

    public const RET_DEPENDENCY_ERROR = 400;     // dependency error

    public const RET_HTTP_TIMEOUT = 401;         // http request timeout
    public const RET_HTTP_5XX = 402;             // http 5xx error
    public const RET_HTTP_RESPONSE_INVALID = 403; // invalid http response

    public const RET_MQ_ERROR = 410;             // message queue error
    public const RET_REDIS_ERROR = 420;          // redis error
    public const RET_THIRD_PARTY_ERROR = 430;    // third-party service error


    /* =========================
     * Data & Storage (500–599)
     * ========================= */

    public const RET_DB_ERROR = 500;              // database error
    public const RET_DB_CONNECTION_FAILED = 501; // database connection failed
    public const RET_DB_QUERY_FAILED = 502;      // database query failed
    public const RET_DB_DUPLICATE_KEY = 503;     // duplicate key
    public const RET_DB_TRANSACTION_FAILED = 504; // transaction failed

    public const RET_FILE_IO_ERROR = 520;         // file io error
    public const RET_OBJECT_STORAGE_ERROR = 530;  // object storage error


    /* =========================
     * Concurrency & Protection (600–699)
     * ========================= */

    public const RET_CONCURRENCY_ERROR = 600;    // concurrency error
    public const RET_LOCK_FAILED = 601;          // lock acquire failed
    public const RET_IDEMPOTENT_CONFLICT = 602;  // idempotent conflict

    public const RET_RATE_LIMITED = 610;         // rate limited
    public const RET_CIRCUIT_OPEN = 611;         // circuit breaker open


    /* =========================
     * AI / Agent (700–799)
     * ========================= */

    public const RET_AI_ERROR = 700;              // generic ai error
    public const RET_PROMPT_PARSE_FAILED = 701;  // prompt parse failed
    public const RET_PLAN_GENERATION_FAILED = 702; // execution plan generation failed
    public const RET_TOOL_NOT_FOUND = 703;       // tool not found
    public const RET_TOOL_EXECUTION_FAILED = 704; // tool execution failed
    public const RET_MODEL_RESPONSE_INVALID = 705; // invalid model response
    public const RET_AI_POLICY_VIOLATION = 706;  // ai policy violation


    /* =========================
     * Ops & Environment (800–899)
     * ========================= */

    public const RET_CONFIG_ERROR = 800;         // configuration error
    public const RET_ENV_NOT_READY = 801;        // environment not ready
    public const RET_SERVICE_UNAVAILABLE = 802; // service unavailable
    public const RET_DEPLOYMENT_ERROR = 803;    // deployment error
}

