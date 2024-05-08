import { isToday } from './utils'

export const kFormatter = num => (num > 999 ? `${(num / 1000).toFixed(1)}k` : num)

export const title = (value, replacer = ' ') => {
  if (!value) return ''
  const str = value.toString()

  const arr = str.split(replacer)
  const capitalizedArray = []
  arr.forEach(word => {
    const capitalized = word.charAt(0).toUpperCase() + word.slice(1)
    capitalizedArray.push(capitalized)
  })
  return capitalizedArray.join(' ')
}

export const avatarText = value => {
  if (!value) return ''
  const nameArray = value.split(' ')
  return nameArray.map(word => word.charAt(0).toUpperCase()).join('')
}

/**
 * Format and return date in Humanize format
 * Intl docs: https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Intl/DateTimeFormat/format
 * Intl Constructor: https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Intl/DateTimeFormat/DateTimeFormat
 * @param {String} value date to format
 * @param {Object} formatting Intl object to format with
 */
export const formatDate = (value, formatting = { month: 'short', day: 'numeric', year: 'numeric' }) => {
  if (!value) return value
  return new Intl.DateTimeFormat('pt-BR', formatting).format(new Date(value))
}

export const formatDateOnlyNumber = (value, formatting = { month: 'numeric', day: 'numeric', year: 'numeric' }) => {
  if (!value) return value
  return new Intl.DateTimeFormat('pt-BR', formatting).format(new Date(value))
}

export const formatDateTime = (value, formatting = { month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: 'numeric', second: 'numeric', hour12: false,
timeZone: 'America/Sao_Paulo'}) => {
  if (!value) return value
  return new Intl.DateTimeFormat('pt-BR', formatting).format(new Date(value))
}

export const formatDateTimeOnlyNumber = (value, formatting = { month: 'numeric', day: 'numeric', year: 'numeric', hour: 'numeric', minute: 'numeric', second: 'numeric', hour12: false,
timeZone: 'America/Sao_Paulo'}) => {
  if (!value) return value
  return new Intl.DateTimeFormat('pt-BR', formatting).format(new Date(value))
}

/**
 * Return short human friendly month representation of date
 * Can also convert date to only time if date is of today (Better UX)
 * @param {String} value date to format
 * @param {Boolean} toTimeForCurrentDay Shall convert to time if day is today/current
 */
export const formatDateToMonthShort = (value, toTimeForCurrentDay = true) => {
  const date = new Date(value)
  let formatting = { month: 'short', day: 'numeric' }

  if (toTimeForCurrentDay && isToday(date)) {
    formatting = { hour: 'numeric', minute: 'numeric' }
  }

  return new Intl.DateTimeFormat('pt-BR', formatting).format(new Date(value))
}

export const formatDateToMonthShortWithTime = (value, toTimeForCurrentDay = true) => {
  const date = new Date(value)
  let formatting = { month: 'short', day: 'numeric', hour: 'numeric', minute: 'numeric' }

  if (toTimeForCurrentDay && isToday(date)) {
    formatting = { hour: 'numeric', minute: 'numeric' }
  }

  return new Intl.DateTimeFormat('pt-BR', formatting).format(new Date(value))
}

// Strip all the tags from markup and return plain text
export const filterTags = value => value.replace(/<\/?[^>]+(>|$)/g, '')

//Retorna número do cartão de crédito ou débito com máscara, exibindo apenas os últimos 4 dígitos
export const maskCardNumber = value => value.replace(/\d(?=\d{4})/g, "*")
