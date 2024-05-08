import { ref, computed, watch } from '@vue/composition-api'

export default function useCalendarEventHandler(props, emit) {
  // ------------------------------------------------
  // eventLocal
  // ------------------------------------------------
  const parameterLocal = ref(JSON.parse(JSON.stringify(props.company.value)))
  parameterLocal.value.charges = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]
  //console.log(props.company.value)
  
  props.company.value.charges.map(function(charge, key) {
    //console.log('[forEach]', charge, key);
    if(charge.type_parameter_id == 6) {
      parameterLocal.value.charges[1] = charge.com_value
      //Pega o parâmetro de COBRANÇA PROPORCIONAL da MENSALIDADE
      parameterLocal.value.charges[2] = charge.com_proportional_charge
    } 
    //Se o parâmetro for a cobrança por USUÁRIO
    else if (charge.type_parameter_id == 7) {
      parameterLocal.value.charges[3] = charge.com_value
      //Pega o parâmetro de COBRANÇA PROPORCIONAL da MENSALIDADE
      parameterLocal.value.charges[4] = charge.com_proportional_charge
    }//Se a cobraça for por CANAL OFICIAL
    else if (charge.type_parameter_id == 8) {
      parameterLocal.value.charges[5] = charge.com_value
      //Pega o parâmetro de COBRANÇA PROPORCIONAL da MENSALIDADE
      parameterLocal.value.charges[6] = charge.com_proportional_charge
    }//Se a cobraça for por CANAL NÃO OFICIAL
    else if (charge.type_parameter_id == 9) {
      parameterLocal.value.charges[7] = charge.com_value
      //Pega o parâmetro de COBRANÇA PROPORCIONAL da MENSALIDADE
      parameterLocal.value.charges[8] = charge.com_proportional_charge
    }//Se a cobrança for por ENVIO DE MENSAGEM VIA WHATSAPP EM UMA CAMPANHA
    else if (charge.type_parameter_id == 5) {
      parameterLocal.value.charges[9] = charge.com_value
    } //Se a cobrança for por ENVIO DE SMS
    else if (charge.type_parameter_id == 10) {
      parameterLocal.value.charges[10] = charge.com_value
    } //Se a cobrança for por RETORNO DE SMS
    else if (charge.type_parameter_id == 11) {
      parameterLocal.value.charges[11] = charge.com_value
    } //Se a cobrança for por LIGAÇÃO VIA WHATSAPP
    else if (charge.type_parameter_id == 12) {
      parameterLocal.value.charges[12] = charge.com_value
    }
  })
    
  const resetUserLocal = () => {
    parameterLocal.value = JSON.parse(JSON.stringify(props.company.value))
  }
  watch(props.company, () => {
    resetUserLocal()
  })

  const onSubmit = () => {
    const settingsData = JSON.parse(JSON.stringify(parameterLocal))
    //Manda os dados para serem processados
    emit('update-company-charges', settingsData.value)
    //Fecha o modal
    emit('hide-modal', 'modal-edit-user-information')
  }

  return {
    parameterLocal,

    resetUserLocal,

    onSubmit,
  }
}
