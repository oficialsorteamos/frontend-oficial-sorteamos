import { ref, computed, watch } from '@vue/composition-api'

export default function useCalendarEventHandler(props, emit) {
  // ------------------------------------------------
  // eventLocal
  // ------------------------------------------------
  const settingsLocal = ref(JSON.parse(JSON.stringify(props.company.value)))
  //settingsLocal.value.fees = [0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0 ]
  //console.log(props.company.value)
  console.log('settingsLocal fees')
  console.log(props.company.value)
  var i = 0;
  //Para a quantidade total de taxas existentes até o momento na plataforma (8 Taxas)
  while ( i < 8) {
    //Caso não exista uma determinada taxa cadastrada
    if(typeof settingsLocal.value.fees && typeof settingsLocal.value.fees[i] === 'undefined') {
      //Coloca a mesma como 0.0
      settingsLocal.value.fees[i+1] = 0.0
    } //Se já existir taxa cadastrada
    else {
      //Preenche o campo com a referida da taxa
      settingsLocal.value.fees[i+1] = props.company.value.fees[i].par_value
    }
    i++
  }
  
    
  const resetUserLocal = () => {
    settingsLocal.value = JSON.parse(JSON.stringify(props.company.value))
  }
  watch(props.company, () => {
    resetUserLocal()
  })

  const onSubmit = () => {
    const settingsData = JSON.parse(JSON.stringify(settingsLocal))
    //Manda os dados para serem processados
    emit('update-partner-fees', settingsData.value)
  }

  return {
    settingsLocal,

    resetUserLocal,

    onSubmit,
  }
}
