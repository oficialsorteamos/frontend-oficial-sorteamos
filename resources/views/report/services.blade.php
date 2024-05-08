<html>
<head>
	<style>
		
		td{
			text-align: center;
		}
		th:nth-child(even){
			text-align: center;
            background-color: #E2E2E2;
		}
		table {
    	border-collapse: collapse;
		}

		table, th, td {
		    border: 1px solid black;
		}
	</style>
</head>
<body>
	@if(@isset($services)) <!-- Se a variável relatório foi iniciada, apresenta os dados -->
	<table>
		<tr>
			<th width="15" align="center" style="background-color: #E2E2E2; font-weight: bold">PROTOCOLO</th>
			<th width="30" align="center" style="background-color: #E2E2E2; font-weight: bold">CONTATO</th>
			<th width="15" align="center" style="background-color: #E2E2E2; font-weight: bold">Nº CONTATO</th>
			<th width="25" align="center" style="background-color: #E2E2E2; font-weight: bold">OPERADOR</th>
			<th width="25" align="center" style="background-color: #E2E2E2; font-weight: bold">DEPARTAMENTO</th>
			<th width="25" align="center" style="background-color: #E2E2E2; font-weight: bold">CANAL</th>
			<th width="25" align="center" style="background-color: #E2E2E2; font-weight: bold">CAMPANHA</th>
			<th width="15" align="center" style="background-color: #E2E2E2; font-weight: bold">STATUS</th>
			<th width="20" align="center" style="background-color: #E2E2E2; font-weight: bold">DATA INÍCIO</th>
			<th width="20" align="center" style="background-color: #E2E2E2; font-weight: bold">DATA DE ENCERRAMENTO</th>
			<th width="10" align="center" style="background-color: #E2E2E2; font-weight: bold">CEP</th>
			<th width="30" align="center" style="background-color: #E2E2E2; font-weight: bold">RUA</th>
			<th width="5" align="center" style="background-color: #E2E2E2; font-weight: bold">Nº</th>
			<th width="25" align="center" style="background-color: #E2E2E2; font-weight: bold">COMPLEMENTO</th>
			<th width="25" align="center" style="background-color: #E2E2E2; font-weight: bold">CIDADE</th>
			<th width="25" align="center" style="background-color: #E2E2E2; font-weight: bold">ESTADO</th>
			<th width="25" align="center" style="background-color: #E2E2E2; font-weight: bold">PAÍS</th>
			<th width="30" align="center" style="background-color: #E2E2E2; font-weight: bold">TAGS</th>
		</tr>
        
		<tbody>
			@forelse($services as $key => $service)
			<tr>
				<td style="text-align: center;">{{$service->ser_protocol_number}}</td>
				<td style="text-align: center;">{{$service->con_name}}</td>
				<td style="text-align: center;">{{$service->con_phone}}</td>
				<td style="text-align: center;">{{$service->name}}</td>
				<td style="text-align: center;">{{$service->dep_name}}</td>
				<td style="text-align: center;">{{$service->cha_name}}</td>
				<td style="text-align: center;">{{$service->cam_name}}</td>
				<td style="text-align: center;">{{$service->typ_description}}</td>
				<td style="text-align: center;">{{$service->created_at}}</td>
				<td style="text-align: center;">{{$service->ser_dt_end_service}}</td>
				<td style="text-align: center;">{{$service->add_zip_code}}</td>
				<td style="text-align: center;">{{$service->add_street}}</td>
				<td style="text-align: center;">{{$service->add_number}}</td>
				<td style="text-align: center;">{{$service->add_address_complement}}</td>
				<td style="text-align: center;">{{$service->add_city}}</td>
				<td style="text-align: center;">{{$service->sta_name}}</td>
				<td style="text-align: center;">{{$service->cou_name}}</td>
				<td style="text-align: center;">{{$service->tagsContact}}</td>
			</tr>
			@empty
			<tr>
				<td colspan="90px" class="text-center">Nenhum Resultado.</td>
			</tr>
			@endforelse
		</tbody>
	</table>
	@endif
</body>
</html>
