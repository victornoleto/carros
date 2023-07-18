import json
import os
import glob

config = json.load(open('config.json', 'r'))

base_file = open('base.conf', 'r')
base_file_contents = base_file.read()

os.makedirs('conf', exist_ok=True)

for f in glob.glob('conf/*.conf'):
    os.remove(f)

project_path = config['project_path']

log_filename_base = config['log_filename'].replace('$project_path', project_path)

for item in config['queues']:

	name = item['name']
	log_filename = item['log_filename'] if 'log_filename' in item else log_filename_base.replace('$name', name)
	#tries = item['tries'] if 'tries' in item else config['tries']
	#timeout = item['timeout'] if 'timeout' in item else config['timeout']
	user = item['user'] if 'user' in item else config['user']
	numprocs = item['numprocs'] if 'numprocs' in item else config['numprocs']

	contents = base_file_contents.replace('$name', name)
	contents = contents.replace('$project_path', project_path)
	contents = contents.replace('$log_filename', log_filename)
	#contents = contents.replace('$tries', str(tries))
	#contents = contents.replace('$timeout', str(timeout))
	contents = contents.replace('$user', user)
	contents = contents.replace('$numprocs', str(numprocs))

	conf_filename = 'conf/carros-' + name + '-worker.conf'

	file = open(conf_filename, 'w')
	file.write(contents)
	file.close()