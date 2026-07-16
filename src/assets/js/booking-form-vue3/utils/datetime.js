/**
 * datetime.js — date-label formatting parity for the V3 booking form.
 *
 * The admin "Date format" setting (`bpa_front_date_fmt`) is a PHP `date()`
 * format string (e.g. `F j, Y`, `Y-m-d`, `d/m/Y`). StateBuilder.php plumbs it
 * to the frontend as `state.config.dateFormat` (via
 * `DateFormatService::date_format()`) precisely so display labels can honor it.
 *
 * The released vue2 form formatted the summary date through the
 * `| bookingpress_format_date` Vue filter
 * (class.bookingpress_appointment_bookings.php:5966). This is the V3
 * equivalent so the summary shows the configured format instead of the raw
 * canonical `YYYY-MM-DD` that gets stored in `selected_date` for backend
 * queries.
 *
 * Mirrors the server-side `DateFormatService::format_date()`, which uses
 * `gmdate()` — so month / weekday names are English (PHP's `gmdate()` is not
 * locale-aware). Supports the tokens BookingPress's date-format picker can
 * emit (`d D j l m M n F y Y`; see
 * `bookingpress_check_common_date_format`, class.bookingpress.php:9033) plus a
 * few other common PHP `date()` tokens. A backslash escapes the next char
 * (PHP convention); any other character passes through as a literal.
 */

const MONTHS_FULL = [
  'January', 'February', 'March', 'April', 'May', 'June',
  'July', 'August', 'September', 'October', 'November', 'December',
];
const MONTHS_SHORT = [
  'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
  'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec',
];
const DAYS_FULL = [
  'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday',
];
const DAYS_SHORT = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

/** Zero-pad to two digits. */
function pad2(n) {
  return n < 10 ? '0' + n : '' + n;
}

/** English ordinal suffix for a day-of-month (PHP `S`). */
function ordinalSuffix(day) {
  if (day >= 11 && day <= 13) return 'th';
  switch (day % 10) {
    case 1:  return 'st';
    case 2:  return 'nd';
    case 3:  return 'rd';
    default: return 'th';
  }
}

/**
 * Parse a canonical "YYYY-MM-DD" (optionally with a trailing time) into a
 * local-midnight Date, or null when unparseable.
 *
 * @param {string} ymd
 * @returns {Date|null}
 */
function parseYmd(ymd) {
  if (!ymd || typeof ymd !== 'string') return null;
  const m = /^(\d{4})-(\d{2})-(\d{2})/.exec(ymd);
  if (!m) return null;
  const d = new Date(Number(m[1]), Number(m[2]) - 1, Number(m[3]));
  return isNaN(d.getTime()) ? null : d;
}

/**
 * Format a canonical "YYYY-MM-DD" date using a PHP `date()` format string.
 *
 * @param {object|string} configOrFormat  `state.config` (reads `.dateFormat`)
 *                                         OR a PHP format string directly.
 * @param {string} ymd  Canonical "YYYY-MM-DD" (the value stored in
 *                       `selected_date`).
 * @returns {string} The formatted date, or the raw input when it can't be
 *                   parsed (so a malformed value is never silently blanked).
 */
export function formatDate(configOrFormat, ymd) {
  const fmt = (configOrFormat && typeof configOrFormat === 'object')
    ? String(configOrFormat.dateFormat || 'F j, Y')
    : String(configOrFormat || 'F j, Y');

  const d = parseYmd(ymd);
  if (!d) return String(ymd == null ? '' : ymd);

  const day   = d.getDate();
  const month = d.getMonth(); // 0-11
  const year  = d.getFullYear();
  const dow   = d.getDay();   // 0 (Sun) - 6 (Sat)

  let out = '';
  for (let i = 0; i < fmt.length; i++) {
    const ch = fmt[i];
    if (ch === '\\') {            // PHP escape: emit the next char literally.
      i++;
      if (i < fmt.length) out += fmt[i];
      continue;
    }
    switch (ch) {
      case 'd': out += pad2(day); break;            // 01-31
      case 'j': out += day; break;                  // 1-31
      case 'S': out += ordinalSuffix(day); break;   // st/nd/rd/th
      case 'D': out += DAYS_SHORT[dow]; break;      // Mon
      case 'l': out += DAYS_FULL[dow]; break;       // Monday
      case 'N': out += (dow === 0 ? 7 : dow); break; // 1 (Mon) - 7 (Sun)
      case 'w': out += dow; break;                  // 0 (Sun) - 6 (Sat)
      case 'm': out += pad2(month + 1); break;      // 01-12
      case 'n': out += (month + 1); break;          // 1-12
      case 'M': out += MONTHS_SHORT[month]; break;  // Jan
      case 'F': out += MONTHS_FULL[month]; break;   // January
      case 'y': out += pad2(year % 100); break;     // 26
      case 'Y': out += year; break;                 // 2026
      default:  out += ch; break;                   // literal separator/char
    }
  }
  return out;
}

/**
 * Format a canonical "HH:MM" (or "HH:MM:SS") time using a PHP `date()` time
 * format string.
 *
 * Mirrors the server-side `DateFormatService::format_time()`, which runs the
 * raw slot time through `gmdate( php_time_format() )` — the same format
 * StateBuilder plumbs to the frontend as `state.config.phpTimeFormat`
 * (`'H:i'`, `'g:i a'`, `'g:i A'`, or an arbitrary WP-inherited string).
 * Timeslot buttons show the server-baked `formatted_start_time`, but the
 * picked slot is stored in `selected_start_time`/`selected_end_time` as raw
 * 24-hour "HH:MM" (that's what the backend booking pipeline parses), so any
 * client-built display label (Summary step) must re-apply the admin format.
 *
 * Supports the PHP time tokens `g G h H i s a A`, backslash escapes, and
 * passes any other character through as a literal — same convention as
 * `formatDate()` above.
 *
 * @param {object|string} configOrFormat  `state.config` (reads
 *                                         `.phpTimeFormat`, falling back to
 *                                         `.timeFormat` '12'/'24') OR a PHP
 *                                         format string directly.
 * @param {string} hhmm  Raw "HH:MM" / "HH:MM:SS" 24-hour time (the value
 *                        stored in `selected_start_time`).
 * @returns {string} The formatted time, or the raw input when it can't be
 *                   parsed (so a malformed value is never silently blanked).
 */
export function formatTime(configOrFormat, hhmm) {
  const fmt = (configOrFormat && typeof configOrFormat === 'object')
    ? String(
        configOrFormat.phpTimeFormat
        || (String(configOrFormat.timeFormat) === '24' ? 'H:i' : 'g:i a')
      )
    : String(configOrFormat || 'g:i a');

  const raw = String(hhmm == null ? '' : hhmm);
  const m = /^(\d{1,2}):(\d{2})(?::(\d{2}))?$/.exec(raw.trim());
  if (!m) return raw;
  const hour24 = Number(m[1]);
  const minute = Number(m[2]);
  const second = Number(m[3] || 0);
  if (hour24 > 23 || minute > 59 || second > 59) return raw;

  const hour12 = (hour24 % 12) === 0 ? 12 : hour24 % 12;
  const ampm   = hour24 < 12 ? 'am' : 'pm';

  let out = '';
  for (let i = 0; i < fmt.length; i++) {
    const ch = fmt[i];
    if (ch === '\\') {            // PHP escape: emit the next char literally.
      i++;
      if (i < fmt.length) out += fmt[i];
      continue;
    }
    switch (ch) {
      case 'g': out += hour12; break;               // 1-12
      case 'h': out += pad2(hour12); break;         // 01-12
      case 'G': out += hour24; break;               // 0-23
      case 'H': out += pad2(hour24); break;         // 00-23
      case 'i': out += pad2(minute); break;         // 00-59
      case 's': out += pad2(second); break;         // 00-59
      case 'a': out += ampm; break;                 // am/pm
      case 'A': out += ampm.toUpperCase(); break;   // AM/PM
      default:  out += ch; break;                   // literal separator/char
    }
  }
  return out;
}
