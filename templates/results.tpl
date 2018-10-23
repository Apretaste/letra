<!DOCTYPE html>
<html>
<head>
	<title></title>
</head>
<body>
	<center>
		<h1>Resultados</h1>
	</center>
	{if isset($title)}
	
	
	<center>
		<p>Cancion:<b>{$title}</b></p>
		<table>
			<tr>
				<th>Autor</th>
			</tr>
		{foreach $datos as $key=>$value}
			<tr>
				<td>{$key}</td>
				<td>{button caption="Ver letra" href="LETRA BUSCAR {$value}" desc="Ver letra" popup="false" size="small"}</td>
			</tr>
		{/foreach}
		</table>
		{space10}
		{button caption="Inicio" href="LETRA"  popup="false"}
	</center>
	{elseif isset($nombre)}
	
	<center>
		<table>
			
		{foreach $datos as $key=>$value}
			<tr>
				<td>{$key}</td>
				<td>{button caption="Canciones" href="LETRA BUSCAR_CANCIONES {$value}" desc="Ver letra" popup="false" size="small"}</td>
			</tr>
		{/foreach}
		</table>
		{space10}
		{button caption="Inicio" href="LETRA"  popup="false"}
	</center>
	{else}
	
	<center>
		<h2>{$author}</h2>
		<table>
			
		{foreach $datos as $key=>$value}
			<tr>
				<td>{$key}</td>
				<td>{button caption="Ver letra" href="LETRA BUSCAR {$value}" desc="Ver letra" popup="false" size="small"}</td>
			</tr>
		{/foreach}
		</table>
	</center>

	{/if}

</body>
</html>