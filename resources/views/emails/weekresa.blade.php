<!DOCTYPE html><html><head>
</head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Les réservations de la semaine</title>
<body style="margin: 0; font-family: Helvetica, Arial, Sans-Serif; font-size: 12px; background: #f3f3f3; color: #393939">
    
	<table id="container" style="border-spacing: 0; width: 100%; border: 0">
		<tbody>
			<tr><td align="center" style="padding:0">
				<p style="width:600px" align="left">Les réservations à retirer cette semaine :<p>
			</td></tr>
			<tr><td align="center" style="padding:0">
				<table id="content" style="border-spacing:0;background-color:#fff;width:600px; border:thick double #32a1ce">

					<thead><tr style="background-color :#daf7a6">
						<th>Référence</th><th>Outil</th><th>Nom</th><th>Début</th>
					</tr></thead>
                    <tbody>
                        @foreach($reservations as $resa)
                        <tr>
                            <td>{{ $resa->reference }}</td>
                            <td>{{ $resa->nomoutil }}</td>
                            <td>{{ $resa->nom }}</td>
                            <td>{{ $resa->debut }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
			</td></tr>
			<tr><td align="center" style="padding:0">
				<p style="width:600px" align="left">Les réservations à récupérer cette semaine :<p>
			</td></tr>
			<tr><td align="center" style="padding:0">
				<table id="content" style="border-spacing:0;background-color:#fff;width:600px; border:thick double #32a1ce">

					<thead><tr style="background-color :#daf7a6">
						<th>Référence</th><th>Outil</th><th>Nom</th><th>Début</th>
					</tr></thead>
                    <tbody>
                        @foreach($retours as $resa)
                        <tr>
                            <td>{{ $resa->reference }}</td>
                            <td>{{ $resa->nomoutil }}</td>
                            <td>{{ $resa->nom }}</td>
                            <td>{{ $resa->debut }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
			</td></tr>


        </tbody>
</body>
</html>