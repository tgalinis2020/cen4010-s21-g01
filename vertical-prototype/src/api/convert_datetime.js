/**
 * In MySQL, the DATETIME data type returns dates using the "YYYY-MM-DD HH:II:SS"
 * format.
 * 
 * Also worth noting that dates are stored relative to UTC.
 * 
 * @param {string} datetimeString
 * @returns {Date} A Date object initialized using provided string.
 */
 export default function convert_datetime(datetime) {
    const [date, time] = datetime.split(' ')
    const [date, month, year] = date.split('-')
    const [hours, minutes, seconds] = time.split(':')

    return Date.UTC(year, month, date, hours, minutes, seconds)
}
