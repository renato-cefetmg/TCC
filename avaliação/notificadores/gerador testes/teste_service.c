#define FILEPATH "/usr/local/nagios/etc/utils/nagios_send_service.php"
#include <stdio.h>

int main() {
    FILE* fp;
    int i,j,k;
    fp = fopen("testes_service.sh","w+");
    fprintf(fp,"# !/bin/bash\n");
    for(i=1;i<=2;i++){
        for(j=1;j<=4;j++){
	    	for(k=1;k<=3;k++){
				fprintf(fp,"/usr/bin/php %s ",FILEPATH);
				fprintf(fp,"PAYLOAD%d ",k+3*(j-1)+12*(i-1));
				fprintf(fp,"\"device | SSH\" ");
				switch(j){
				   case 1: fprintf(fp, "OK "); break;
				   case 2: fprintf(fp, "WARNING "); break;
				   case 3: fprintf(fp, "UNKNOWN "); break;
				   case 4: fprintf(fp, "CRITICAL "); break;	   
				}
				switch(i){
				   case 1: fprintf(fp, "SOFT "); break;
				   case 2: fprintf(fp, "HARD "); break;   
				}
				fprintf(fp, "%i\n",k);
	   		}
		}
    }
    fclose(fp);
}
