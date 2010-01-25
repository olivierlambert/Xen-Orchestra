/**
 * Display a CPU_max list of a dom0
 * 
 *
 * @param cpus_max Maximum amout of CPU installed on a Dom0
 */
function html_cpu_select(cpus_max)
{
	result = '<select>';
	for (var i = 1; i <= cpus_max; i++)
	{
		result += '<option>'+i+'</option>';
	}
	return result += '</select>';
}

/**
 * Display CPU meters in function of their load
 * (green/yellow/orange/red)
 *
 * @param cpus Table of cpus with their respective load
 */
function html_cpu_meters(cpus)
{
	var n = cpus.length;
	if (n === 0)
	{
		return '&nbsp;';
	}

	var result = '';
	for (var i = 0; i < n; i++)
	{
		result += '<img title="'+cpus[i]+'" src="img/c';
		if (cpus[i] < 25)
		{
			result += 'green';
		}
		else if (cpus[i] < 50)
		{
			result += 'yellow';
		}
		else if (cpus[i] < 75)
		{
			result += 'orange';
		}
		else
		{
			result += 'red';
		}
		result += '.png">';
	}
	return result;
}

/**
 * Display CPU values in digital
 *
 * @param cpus Table of cpus with their respective load
 */
function html_cpu_values(cpus)
{
	var n = cpus.length;
	if (n === 0)
	{
		return '&nbsp;';
	}
	result=' ';
	for (var i = 0; i < n; i++)
	{
		result += cpus[i]+'% ';
	}
	return result;
}
