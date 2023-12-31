
with open('main', 'r') as f:
    lis = f.readlines()

for item in lis:
    if '<img src="data:' in item:
        for this_item in item:
            if this_item == "<":
                start_index = item.index('<')
                end_index = item.find('"', item.find('"')+1)
                lis[lis.index(item)] = f'{item[:start_index]}<img src="https://sosthenes.me"{item[end_index+1:]}'

print(lis)
