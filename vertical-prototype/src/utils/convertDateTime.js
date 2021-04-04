/**
 * In MySQL, the DATETIME data type returns dates using the "YYYY-MM-DD HH:II:SS"
 * format.
 * 
 * Also worth noting that dates are stored relative to UTC.
 * 
 * @param {string} datetimeString
 * @returns {Date} A Date object initialized using provided string.
 */
function convertDateTime(datetime) {
    const [datePart, timePart] = datetime.split(' ')
    const [date, month, year] = datePart.split('-')
    const [hours, minutes, seconds] = timePart.split(':')

    return Date.UTC(year, month, date, hours, minutes, seconds)
}

export default convertDateTime
