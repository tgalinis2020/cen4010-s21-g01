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
    const [year, month, date] = datePart.split('-')
    const [hours, minutes, seconds] = timePart.split(':')

    // Note: months start at zero, so Jan = 0, Feb = 1, and so on.
    return new Date(Date.UTC(year, month - 1, date, hours, minutes, seconds))
}

export default convertDateTime
