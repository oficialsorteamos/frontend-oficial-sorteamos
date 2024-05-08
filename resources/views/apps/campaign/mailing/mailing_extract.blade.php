<table>
    <thead>
    <tr>
        <th>Nome</th>
        <th>Telefone</th>
    </tr>
    </thead>
    <tbody>
    @foreach($mailing as $contact)
        <tr>
            <td>{{ $contact->campaign_id }}</td>
            <td>{{ $contact->contact_id }}</td>
        </tr>
    @endforeach
    </tbody>
</table>