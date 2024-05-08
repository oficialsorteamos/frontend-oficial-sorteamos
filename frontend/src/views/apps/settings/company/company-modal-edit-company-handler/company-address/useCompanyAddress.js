import { ref, computed, watch } from '@vue/composition-api'

export default function useCalendarEventHandler(props, emit) {
  // ------------------------------------------------
  // eventLocal
  // ------------------------------------------------
  const companyLocal = ref(JSON.parse(JSON.stringify(props.company.value)))
  
  const resetContactLocal = () => {
    companyLocal.value = JSON.parse(JSON.stringify(props.company.value))
  }

  const onSubmit = () => {
    const companyData = JSON.parse(JSON.stringify(companyLocal))

    //Manda os dados para serem processados
    if(props.addressId.value) {
      companyData.value.id = props.addressId.value
      emit('update-contact-address', companyData.value)
    }
    else {
      emit('add-contact-address', companyData.value)
    }
  }

  return {
    companyLocal,

    resetContactLocal,

    onSubmit,
  }
}
