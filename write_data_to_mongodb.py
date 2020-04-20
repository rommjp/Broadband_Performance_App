'''
Script to process broadband speed data file, transform and load into a MongoDB

Author: Rommel Poggenberg
Date created: 18th April 2020
'''

import os
import sys
import csv
import pprint
import xlrd
from datetime import datetime, time
import pymongo

pp = pprint.PrettyPrinter(indent=4)
				  
book = xlrd.open_workbook("all_data.xlsx")
sheet_names=book.sheet_names()

complete_dataset={}

for tab in range(len(sheet_names)):
	all_data=[]
	headers={}
	print(tab, sheet_names[tab])
	sheet = book.sheet_by_index(sheet_names.index(sheet_names[tab]))
	for value, row_index in enumerate(range(sheet.nrows)):
		e_row = sheet.row(row_index)
		data={}
		#print(e_row)
		if value == 0:
			for value2, row_index2 in enumerate(e_row): 
				data_type = str(row_index2).split(':')[0]
				#headers[int(value2)]=str(row_index2).split(':')[1].replace('\'', '')
				rec_value = ""
				if data_type=='text':
					rec_value = str(row_index2).split(':')[1].replace('\'', '')
				elif data_type=='number':
					rec_value = str(float(str(row_index2).split(':')[1].replace('\'', '')))
				elif data_type=='xldate':
					date_tuple =  xlrd.xldate_as_tuple(int(float(str(row_index2).split(':')[1])),0)
					rec_value = datetime(*date_tuple).strftime('%Y-%m-%d')					
				elif data_type=='empty':
					rec_value = None	
				try:
					headers[int(value2)]=rec_value.strip()
				except:
					headers[int(value2)]=rec_value
		else:
			for value2, row_index2 in enumerate(e_row): 			
				data_type = str(row_index2).split(':')[0]
				rec_value = ""
				if data_type=='text':
					rec_value = str(row_index2).split(':')[1].replace('\'', '').strip()
				elif data_type=='number':
					rec_value = str(float(str(row_index2).split(':')[1].replace('\'', ''))).strip()
				elif data_type=='xldate':
					date_tuple =  xlrd.xldate_as_tuple(float(str(row_index2).split(':')[1]),0)
					try:
						rec_value = datetime(*date_tuple).strftime('%Y-%m-%d')
					except ValueError:
						timeObj = time(*date_tuple[3:])
						rec_value = timeObj.strftime('%H:%M:%S')
				elif data_type=='empty':
					rec_value = None
				
				data[headers[int(value2)]] = rec_value
		
		if len(data.keys()) > 0:
			all_data.append(data)
			
			
		#pp.pprint(all_data)
	
	complete_dataset[sheet_names[tab]]=all_data	
		
graph_data={}

for d_tab in complete_dataset.keys():
	for dataset in complete_dataset[d_tab]:
		for key in dataset:
			RSP=None
			month=None
			category=None
			value=None	
			print(d_tab, key, key.split(' - '),dataset[key])
			if d_tab=='speeds':
				if key.split(' - ')[0]!='RSP':
					RSP=dataset['RSP']
					month=key.split(' - ')[2]
					category=key.split(' - ')[0]+':'+key.split(' - ')[1]
					try:
						value=float(dataset[key])*100
					except:
						value=0
			elif d_tab=='outages':
				if key.split(' - ')[0]!='RSP':
					RSP=dataset['RSP']
					month=key.split(' - ')[0]
					category=key.split(' - ')[1]
					try:
						value=float(dataset[key])
					except:
						value=0					
			elif d_tab=='technology - nbn': 	
				if key.split(' - ')[0]!='Technology':
					RSP=dataset['Technology']
					month=key.split(' - ')[0]
					category=key.split(' - ')[1]
					try:
						value=float(dataset[key])*100		
					except:
						value=0					
			elif d_tab=='avg_dl_speed': 	
				if key.split(' - ')[0]!='Plan':
					RSP=dataset['Plan']
					month=key.split(' - ')[0]
					category=key.split(' - ')[1]
					try:
						value=float(dataset[key])
					except:
						value=0					
			elif d_tab=='hour_of_day': 	
				if key.split(' - ')[0]!='Time':
					RSP = dataset['Time']					
					month=key.split(' - ')[0]
					category=key.split(' - ')[1]
					try:
						value=float(dataset[key])	
					except:
						value=0					
			elif d_tab=='distribution_of_speed':
				if key.split(' - ')[0]!='Interval':
					RSP = dataset['Interval']					
					month=key.split(' - ')[1]
					category=key.split(' - ')[0]
					try:
						value=float(dataset[key])*100	
					except:
						value=0					
			elif d_tab=='webpage_load':
				if key.split(' - ')[0]!='RSP':
					RSP = dataset['RSP']					
					month=key.split(' - ')[1]
					category=key.split(' - ')[0]
					try:
						value=float(dataset[key])
					except:
						value=0					
			elif d_tab=='latency':
				if key.split(' - ')[0]!='RSP':
					RSP = dataset['RSP']					
					month=key.split(' - ')[1]
					category=key.split(' - ')[0]
					try:
						value=float(dataset[key])	
					except:
						value=0
			elif d_tab=='packet_loss':
				if key.split(' - ')[0]!='Packet loss (%)':
					RSP = dataset['Packet loss (%)'].replace('.','_')					
					month=key.split(' - ')[0]
					category=key.split(' - ')[1]
					try:
						value=float(dataset[key])*100	
					except:
						value=0							
					
			if RSP!=None and month!=None and category!=None and value!=None:
				if d_tab in graph_data.keys():
					if month in graph_data[d_tab].keys():
						if category in graph_data[d_tab][month].keys(): 
							graph_data[d_tab][month][category][RSP]=value
						else:
							graph_data[d_tab][month][category]={}
							graph_data[d_tab][month][category][RSP]=value
					else:
						graph_data[d_tab][month]={}
						graph_data[d_tab][month][category]={}
						graph_data[d_tab][month][category][RSP]=value
				else:
					graph_data[d_tab]={}
					graph_data[d_tab][month]={}
					graph_data[d_tab][month][category]={}
					graph_data[d_tab][month][category][RSP]=value

#pp.pprint(graph_data)
#sys.exit(0)					
					
month_to_datetime={
'Aug2019':'2019-08-01',
'Feb2019':'2019-02-01',
'Feb2020':'2020-02-01',
'July2018':'2018-07-01',
'March2018':'2018-03-01',
'Mar2018':'2018-03-01',
'May2019':'2019-05-01',
'Nov2018':'2018-11-01',
'Nov2019':'2019-11-01'
}					
					
monthly_data={}	
for chart_type in graph_data.keys():					
	for month in graph_data[chart_type].keys():
		for category in graph_data[chart_type][month].keys():
			for RSP in graph_data[chart_type][month][category].keys():
				if chart_type not in ['hour_of_day']:
					if chart_type in monthly_data.keys():
						if category in monthly_data[chart_type].keys():
							if RSP in monthly_data[chart_type][category].keys():
								if month_to_datetime[month] in monthly_data[chart_type][category][RSP].keys(): 
									continue
								else:
									monthly_data[chart_type][category][RSP][month_to_datetime[month]]=graph_data[chart_type][month][category][RSP]
							else:
								monthly_data[chart_type][category][RSP]={}
								monthly_data[chart_type][category][RSP][month_to_datetime[month]]=graph_data[chart_type][month][category][RSP]	
						else:
							monthly_data[chart_type][category]={}
							monthly_data[chart_type][category][RSP]={}
							monthly_data[chart_type][category][RSP][month_to_datetime[month]]=graph_data[chart_type][month][category][RSP]
					else:
						monthly_data[chart_type]={}
						monthly_data[chart_type][category]={}
						monthly_data[chart_type][category][RSP]={}
						monthly_data[chart_type][category][RSP][month_to_datetime[month]]=graph_data[chart_type][month][category][RSP]
				else:
					if chart_type in monthly_data.keys():
						if category in monthly_data[chart_type].keys():
							if month in monthly_data[chart_type][category].keys():
								if RSP in monthly_data[chart_type][category][month].keys(): 
									continue
								else:
									monthly_data[chart_type][category][month][RSP]=graph_data[chart_type][month][category][RSP]
							else:
								monthly_data[chart_type][category][month]={}
								monthly_data[chart_type][category][month][RSP]=graph_data[chart_type][month][category][RSP]	
						else:
							monthly_data[chart_type][category]={}
							monthly_data[chart_type][category][month]={}
							monthly_data[chart_type][category][month][RSP]=graph_data[chart_type][month][category][RSP]
					else:
						monthly_data[chart_type]={}
						monthly_data[chart_type][category]={}
						monthly_data[chart_type][category][month]={}
						monthly_data[chart_type][category][month][RSP]=graph_data[chart_type][month][category][RSP]				
			

#pp.pprint(monthly_data['hour_of_day'])			
			
client = pymongo.MongoClient("mongodb://localhost:27017/")
db = client["performance_data"]
col = db["charts"]

for filter in graph_data.keys():
	for month in graph_data[filter].keys():
		print(filter, month, graph_data[filter][month])
		col.insert_one({'chart_type':filter,'month':month,'results':graph_data[filter][month]})		

		
for filter in monthly_data.keys(): 	
	print(filter, monthly_data[filter])
	col.insert_one({'chart_type':filter,'month':'all','results':monthly_data[filter]})			
			