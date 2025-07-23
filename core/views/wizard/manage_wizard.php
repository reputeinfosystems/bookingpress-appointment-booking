<?php
    global $bookingpress_ajaxurl;
?>
<el-main class="bpa-fullscreen-wizard-setup">
	<div class="bpa-fws__header">
		<div class="bpa-fws__head-logo">
			<svg width="240" height="60" viewBox="0 0 240 60" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M35.4312 29.8239C35.4312 29.8239 36.5989 28.6486 36.5989 25.1229C36.5989 19.2541 33.0881 18.0789 28.447 18.0789H17.8848V42.7366H24.9063V49.7806H28.447V42.7366C32.5192 42.7366 35.3488 42.7366 37.4298 40.1316C38.4267 38.8491 38.9549 37.2643 38.927 35.6402C38.9387 34.8662 38.8378 34.0946 38.6275 33.3496C38.111 32.0246 37.7667 30.9916 35.4312 29.8239ZM31.9204 39.2184H28.447V35.6926C28.45 34.9989 28.6584 34.3216 29.046 33.7462C29.4335 33.1709 29.9829 32.7232 30.6246 32.4598C31.2664 32.1964 31.9718 32.129 32.6519 32.2661C33.3319 32.4033 33.9561 32.7388 34.4455 33.2304C34.935 33.722 35.2679 34.3475 35.4022 35.0282C35.5364 35.7088 35.466 36.4139 35.1999 37.0545C34.9337 37.6952 34.4837 38.2426 33.9067 38.6277C33.3297 39.0128 32.6515 39.2184 31.9578 39.2184H31.9204ZM29.0758 29.487C28.4768 29.7746 27.9076 30.1206 27.3766 30.52C26.5571 31.0957 25.8994 31.8725 25.4668 32.7757C25.0342 33.6789 24.8412 34.6783 24.9063 35.6776V39.2034H21.3955V21.6046H28.447C31.2467 21.6046 33.1256 22.7724 33.1256 25.1229C33.0881 27.2787 31.4787 28.3118 29.0758 29.502V29.487Z" fill="#12D488"/>
				<path d="M56.1425 9.5092V54.1312C56.1444 55.6845 55.5299 57.1749 54.4337 58.2753C53.3375 59.3757 51.8494 59.996 50.2962 60H5.8463C4.2931 59.996 2.80497 59.3757 1.70879 58.2753C0.612609 57.1749 -0.00198392 55.6845 4.81144e-06 54.1312V9.5092C-0.00198392 7.95599 0.612609 6.46549 1.70879 5.3651C2.80497 4.26471 4.2931 3.6444 5.8463 3.64044H14.3874V1.87382C14.3718 1.63366 14.4055 1.39283 14.4866 1.16624C14.5677 0.93965 14.6945 0.732112 14.859 0.55647C15.0236 0.380828 15.2224 0.240818 15.4432 0.145102C15.664 0.0493859 15.9022 0 16.1428 0C16.3835 0 16.6216 0.0493859 16.8424 0.145102C17.0633 0.240818 17.2621 0.380828 17.4266 0.55647C17.5912 0.732112 17.7179 0.93965 17.799 1.16624C17.8802 1.39283 17.9139 1.63366 17.8982 1.87382V8.91783C17.9139 9.15799 17.8802 9.39882 17.799 9.62541C17.7179 9.85201 17.5912 10.0595 17.4266 10.2352C17.2621 10.4108 17.0633 10.5508 16.8424 10.6466C16.6216 10.7423 16.3835 10.7917 16.1428 10.7917C15.9022 10.7917 15.664 10.7423 15.4432 10.6466C15.2224 10.5508 15.0236 10.4108 14.859 10.2352C14.6945 10.0595 14.5677 9.85201 14.4866 9.62541C14.4055 9.39882 14.3718 9.15799 14.3874 8.91783V7.1587H7.01407C6.08227 7.16266 5.19009 7.53611 4.53331 8.19711C3.87653 8.8581 3.50879 9.75264 3.51078 10.6845V52.956C3.5098 53.417 3.59963 53.8738 3.77515 54.3001C3.95068 54.7264 4.20845 55.114 4.53377 55.4407C4.85908 55.7674 5.24555 56.0268 5.67112 56.2041C6.09669 56.3815 6.55303 56.4733 7.01407 56.4743H49.1209C49.5826 56.4743 50.0397 56.3832 50.4662 56.2063C50.8926 56.0294 51.2799 55.7701 51.606 55.4433C51.9322 55.1165 52.1906 54.7286 52.3666 54.3018C52.5426 53.875 52.6327 53.4177 52.6317 52.956V10.6845C52.6327 10.2224 52.5426 9.76473 52.3667 9.3375C52.1908 8.91027 51.9325 8.52186 51.6065 8.19447C51.2805 7.86707 50.8932 7.60709 50.4667 7.42938C50.0402 7.25166 49.5829 7.15968 49.1209 7.1587H41.755V8.91783C41.7707 9.15799 41.7369 9.39882 41.6558 9.62541C41.5747 9.85201 41.448 10.0595 41.2834 10.2352C41.1189 10.4108 40.9201 10.5508 40.6992 10.6466C40.4784 10.7423 40.2403 10.7917 39.9996 10.7917C39.759 10.7917 39.5208 10.7423 39.3 10.6466C39.0792 10.5508 38.8804 10.4108 38.7158 10.2352C38.5513 10.0595 38.4245 9.85201 38.3434 9.62541C38.2623 9.39882 38.2285 9.15799 38.2442 8.91783V7.1587H31.582C31.1155 7.1587 30.668 6.97336 30.3381 6.64346C30.0082 6.31356 29.8229 5.86612 29.8229 5.39957C29.8229 5.16856 29.8684 4.93981 29.9568 4.72638C30.0452 4.51295 30.1748 4.31903 30.3381 4.15568C30.5015 3.99233 30.6954 3.86275 30.9088 3.77435C31.1222 3.68594 31.351 3.64044 31.582 3.64044H38.2442V1.87382C38.2285 1.63366 38.2623 1.39283 38.3434 1.16624C38.4245 0.93965 38.5513 0.732112 38.7158 0.55647C38.8804 0.380828 39.0792 0.240818 39.3 0.145102C39.5208 0.0493859 39.759 0 39.9996 0C40.2403 0 40.4784 0.0493859 40.6992 0.145102C40.9201 0.240818 41.1189 0.380828 41.2834 0.55647C41.448 0.732112 41.5747 0.93965 41.6558 1.16624C41.7369 1.39283 41.7707 1.63366 41.755 1.87382V3.64044H50.2962C51.8494 3.6444 53.3375 4.26471 54.4337 5.3651C55.5299 6.46549 56.1444 7.95599 56.1425 9.5092Z" fill="#12D488"/>
				<path d="M72.1252 38.246C71.0997 38.246 70.0816 38.246 69.0636 38.1486C67.996 38.081 66.9351 37.9334 65.8896 37.707V19.5767C66.828 19.403 67.7751 19.2806 68.7267 19.2099C69.7523 19.1276 70.7029 19.0901 71.5862 19.0901C72.6869 19.0819 73.7862 19.1671 74.8724 19.3447C75.7773 19.4857 76.651 19.782 77.455 20.2205C78.1444 20.6013 78.7244 21.1529 79.1393 21.8224C79.5689 22.5839 79.7812 23.4487 79.7531 24.3226C79.7519 25.0544 79.5495 25.7718 79.1679 26.3963C78.7864 27.0208 78.2404 27.5283 77.5897 27.8633C78.5917 28.1685 79.4534 28.8181 80.0226 29.6973C80.4574 30.5014 80.679 31.4033 80.6664 32.3173C80.7276 33.1858 80.5617 34.0552 80.185 34.84C79.8083 35.6249 79.2338 36.2982 78.518 36.7937C77.0757 37.7619 74.9448 38.246 72.1252 38.246ZM69.9918 26.6282H72.0504C73.0034 26.6953 73.9581 26.5229 74.8275 26.1267C75.1027 25.9624 75.3268 25.725 75.4748 25.4408C75.6229 25.1566 75.689 24.8369 75.6659 24.5173C75.6931 24.1966 75.6261 23.8749 75.4732 23.5917C75.3203 23.3085 75.0881 23.076 74.8051 22.9228C74.0068 22.5734 73.1374 22.4169 72.2674 22.4662H71.1146C70.7029 22.4662 70.3661 22.5036 70.0068 22.5336L69.9918 26.6282ZM69.9918 29.8396V34.6753C70.2987 34.6753 70.6356 34.7427 70.9949 34.7576C71.3542 34.7726 71.7434 34.7576 72.1851 34.7576C73.2299 34.7975 74.2712 34.6137 75.2393 34.2187C75.6151 34.0436 75.9279 33.7573 76.1355 33.3984C76.343 33.0395 76.4352 32.6255 76.3995 32.2125C76.429 31.8438 76.3532 31.4743 76.1809 31.147C76.0086 30.8197 75.747 30.548 75.4264 30.3636C74.5592 29.9487 73.6017 29.7582 72.6417 29.8096L69.9918 29.8396Z" fill="#202C45"/>
				<path d="M97.0743 30.8651C97.0854 31.9082 96.9209 32.9457 96.5877 33.9342C96.2883 34.8165 95.8091 35.627 95.1804 36.3147C94.5613 36.9641 93.8111 37.4745 92.9796 37.8118C91.1358 38.5302 89.0894 38.5302 87.2456 37.8118C86.4141 37.4745 85.6639 36.9641 85.0448 36.3147C84.4119 35.6262 83.9255 34.8164 83.6151 33.9342C83.2607 32.9503 83.0858 31.9108 83.0986 30.8651C83.0869 29.8231 83.2671 28.7878 83.63 27.8109C83.9502 26.9373 84.4442 26.1377 85.0823 25.4604C85.7207 24.8132 86.485 24.3037 87.328 23.9633C88.2129 23.5975 89.1626 23.4142 90.1201 23.4243C91.0874 23.4128 92.0472 23.5961 92.9422 23.9633C93.788 24.2983 94.5534 24.8085 95.1879 25.4604C95.795 26.1473 96.2628 26.9456 96.5653 27.8109C96.917 28.7902 97.0894 29.8247 97.0743 30.8651ZM92.9647 30.8651C93.0195 29.836 92.7583 28.8147 92.2161 27.9382C91.9687 27.6067 91.6474 27.3376 91.2776 27.1522C90.9079 26.9668 90.5 26.8702 90.0864 26.8702C89.6728 26.8702 89.2649 26.9668 88.8952 27.1522C88.5255 27.3376 88.2041 27.6067 87.9567 27.9382C87.4114 28.8133 87.15 29.8356 87.2082 30.8651C87.1472 31.9161 87.4083 32.9607 87.9567 33.8594C88.1993 34.1978 88.5189 34.4736 88.8893 34.6638C89.2597 34.8541 89.6701 34.9533 90.0864 34.9533C90.5028 34.9533 90.9132 34.8541 91.2835 34.6638C91.6539 34.4736 91.9736 34.1978 92.2161 33.8594C92.7606 32.9591 93.0214 31.9157 92.9647 30.8651Z" fill="#202C45"/>
				<path d="M113.475 30.865C113.486 31.9081 113.322 32.9456 112.988 33.9341C112.692 34.8177 112.212 35.6288 111.581 36.3146C110.962 36.9641 110.212 37.4744 109.38 37.8117C107.539 38.5304 105.495 38.5304 103.654 37.8117C102.819 37.4764 102.066 36.9658 101.446 36.3146C100.818 35.6224 100.332 34.8136 100.016 33.9341C99.664 32.9498 99.4916 31.9103 99.5068 30.865C99.4927 29.8235 99.6704 28.7882 100.031 27.8109C100.368 26.9351 100.877 26.1358 101.528 25.4604C102.166 24.8125 102.93 24.3029 103.774 23.9632C104.661 23.5974 105.613 23.4141 106.573 23.4243C107.541 23.4136 108.5 23.5968 109.395 23.9632C110.224 24.3049 110.974 24.8146 111.596 25.4604C112.203 26.1448 112.666 26.9441 112.959 27.8109C113.313 28.7896 113.488 29.8242 113.475 30.865ZM109.365 30.865C109.42 29.8359 109.159 28.8146 108.617 27.9381C108.385 27.5936 108.068 27.3144 107.697 27.127C107.326 26.9396 106.914 26.8504 106.498 26.8677C106.08 26.8515 105.665 26.9411 105.291 27.1282C104.916 27.3153 104.595 27.5939 104.357 27.9381C103.812 28.8132 103.551 29.8355 103.609 30.865C103.548 31.916 103.809 32.9606 104.357 33.8593C104.591 34.2092 104.911 34.4933 105.286 34.6847C105.66 34.876 106.078 34.9681 106.498 34.9522C106.923 34.9636 107.343 34.8631 107.716 34.6607C108.089 34.4583 108.402 34.1613 108.624 33.7994C109.155 32.9155 109.412 31.8948 109.365 30.865Z" fill="#202C45"/>
				<path d="M120.608 28.8907L121.836 27.5508C122.262 27.0867 122.667 26.6226 123.063 26.166L124.186 24.8635L125.092 23.7855H129.875C128.925 24.8859 127.989 25.9339 127.083 26.9295C126.177 27.9251 125.174 28.9581 124.089 30.0211C124.682 30.5665 125.239 31.149 125.758 31.7652C126.335 32.4389 126.888 33.1426 127.427 33.8612C127.966 34.5798 128.468 35.3059 128.925 36.0246C129.381 36.7432 129.755 37.4094 130.062 38.0008H125.429C125.144 37.5367 124.815 37.0127 124.448 36.4438C124.081 35.8749 123.7 35.3059 123.273 34.737C122.846 34.1681 122.42 33.6217 121.963 33.0977C121.549 32.6164 121.093 32.173 120.601 31.7727V38.0008H116.573V17.6847L120.601 17.0334L120.608 28.8907Z" fill="#202C45"/>
				<path d="M136.635 19.682C136.646 20.0097 136.584 20.3357 136.454 20.637C136.325 20.9384 136.131 21.2077 135.887 21.4261C135.418 21.8313 134.819 22.0542 134.199 22.0542C133.579 22.0542 132.98 21.8313 132.511 21.4261C132.051 20.9621 131.793 20.3353 131.793 19.682C131.793 19.0287 132.051 18.4018 132.511 17.9378C132.978 17.5283 133.578 17.3025 134.199 17.3025C134.82 17.3025 135.42 17.5283 135.887 17.9378C136.134 18.1543 136.329 18.4233 136.459 18.7251C136.588 19.0269 136.649 19.3538 136.635 19.682ZM136.239 37.9994H132.211V23.7766H136.239V37.9994Z" fill="#202C45"/>
				<path d="M140.042 24.2679C140.912 24.0323 141.794 23.8473 142.685 23.714C143.812 23.5435 144.951 23.4584 146.091 23.4595C147.109 23.4324 148.124 23.5923 149.085 23.9311C149.826 24.203 150.481 24.6652 150.987 25.271C151.468 25.8727 151.81 26.573 151.99 27.3221C152.195 28.1902 152.296 29.0799 152.289 29.972V37.9966H148.262V30.4586C148.335 29.5109 148.157 28.5606 147.745 27.7039C147.524 27.414 147.23 27.1869 146.894 27.0452C146.558 26.9034 146.191 26.8518 145.829 26.8954C145.537 26.8954 145.23 26.8954 144.908 26.8954C144.586 26.8954 144.294 26.9553 144.04 26.9927V37.9966H140.02L140.042 24.2679Z" fill="#202C45"/>
				<path d="M168.224 36.2132C168.224 38.5437 167.635 40.2679 166.457 41.3858C165.279 42.5037 163.453 43.0676 160.977 43.0776C160.109 43.0783 159.242 43.0007 158.387 42.8455C157.571 42.709 156.768 42.5063 155.985 42.2392L156.681 38.8631C157.325 39.1196 157.992 39.3175 158.672 39.4545C159.45 39.61 160.243 39.6828 161.037 39.6716C161.474 39.7176 161.915 39.675 162.334 39.5464C162.753 39.4177 163.142 39.2057 163.478 38.923C163.725 38.6643 163.919 38.3591 164.047 38.025C164.176 37.6909 164.237 37.3346 164.226 36.9767V36.4677C163.797 36.6629 163.348 36.8109 162.886 36.9094C162.394 37.0115 161.892 37.0616 161.389 37.0591C160.559 37.1091 159.728 36.9791 158.953 36.6781C158.178 36.3771 157.477 35.9121 156.898 35.3149C155.784 33.9441 155.225 32.2048 155.333 30.4418C155.318 29.4692 155.483 28.5021 155.82 27.5897C156.118 26.7447 156.601 25.9773 157.235 25.344C157.867 24.7351 158.619 24.2636 159.443 23.9592C160.423 23.6111 161.457 23.4412 162.497 23.4576C162.996 23.4576 163.495 23.4801 163.994 23.525C164.493 23.5699 164.992 23.6298 165.491 23.7047C165.993 23.772 166.472 23.8619 166.936 23.9592C167.4 24.0565 167.804 24.1613 168.164 24.2661L168.224 36.2132ZM159.383 30.4343C159.383 32.68 160.296 33.8103 162.115 33.8103C162.508 33.8144 162.899 33.7589 163.276 33.6456C163.595 33.5557 163.904 33.4302 164.196 33.2713V26.8337C164.002 26.8337 163.762 26.7663 163.493 26.7438C163.179 26.7117 162.864 26.6967 162.549 26.6989C162.1 26.6706 161.651 26.7519 161.24 26.9358C160.829 27.1197 160.47 27.4006 160.191 27.7544C159.635 28.5347 159.351 29.4764 159.383 30.4343Z" fill="#202C45"/>
				<path d="M177.897 19.0603C180.686 19.0603 182.832 19.5593 184.334 20.5574C185.831 21.5455 186.58 23.1475 186.58 25.3857C186.58 27.6239 185.831 29.2633 184.334 30.2589C182.837 31.2544 180.659 31.756 177.852 31.756H176.527V38.0215H172.312V19.5768C173.274 19.3909 174.247 19.2634 175.224 19.195C176.227 19.0977 177.126 19.0603 177.897 19.0603ZM178.174 22.6534C177.867 22.6534 177.56 22.6534 177.268 22.6534L176.519 22.7133V28.118H177.844C178.974 28.1815 180.103 27.9763 181.138 27.5191C181.525 27.2971 181.837 26.9649 182.034 26.565C182.231 26.1651 182.305 25.7154 182.246 25.2734C182.261 24.8247 182.163 24.3793 181.961 23.9784C181.773 23.6349 181.493 23.35 181.153 23.155C180.755 22.9303 180.318 22.783 179.865 22.7208C179.299 22.6516 178.728 22.6291 178.159 22.6534H178.174Z" fill="#202C45"/>
				<path d="M197.982 27.3541C197.623 27.2643 197.196 27.167 196.71 27.0697C196.194 26.9685 195.67 26.9183 195.145 26.92C194.842 26.9235 194.54 26.946 194.239 26.9873C193.969 27.0175 193.701 27.0676 193.438 27.1371V37.9988H189.389V24.5246C190.225 24.2278 191.077 23.9779 191.941 23.776C193.021 23.5417 194.123 23.4287 195.227 23.4391C195.445 23.4391 195.707 23.4391 196.013 23.4766C196.32 23.514 196.627 23.5439 196.934 23.5889L197.847 23.7461C198.116 23.8006 198.379 23.8782 198.633 23.9781L197.982 27.3541Z" fill="#202C45"/>
				<path d="M199.866 30.9971C199.843 29.8667 200.041 28.7426 200.45 27.6885C200.791 26.8102 201.299 26.0067 201.947 25.323C202.566 24.6997 203.308 24.2127 204.125 23.8932C204.928 23.5732 205.784 23.4081 206.648 23.4067C207.531 23.36 208.413 23.5007 209.238 23.8194C210.062 24.138 210.81 24.6275 211.432 25.2556C212.609 26.4883 213.196 28.3048 213.191 30.7052C213.191 30.9373 213.191 31.1918 213.191 31.4538C213.191 31.7158 213.153 31.9778 213.138 32.2023H204.006C204.042 32.5959 204.164 32.9765 204.365 33.317C204.565 33.6574 204.839 33.9491 205.166 34.1711C205.983 34.7029 206.947 34.9647 207.921 34.9196C208.652 34.9206 209.381 34.8529 210.099 34.7175C210.697 34.6139 211.282 34.4457 211.843 34.216L212.382 37.4872C212.102 37.619 211.812 37.7267 211.514 37.8091C211.121 37.9273 210.721 38.0223 210.316 38.0936C209.874 38.1759 209.403 38.2433 208.894 38.2957C208.397 38.3487 207.897 38.3762 207.397 38.378C206.258 38.4036 205.124 38.2105 204.058 37.8091C203.171 37.4693 202.369 36.9405 201.708 36.2596C201.081 35.5902 200.61 34.7899 200.33 33.9166C200.019 32.9749 199.862 31.989 199.866 30.9971ZM209.328 29.5C209.313 29.1598 209.255 28.8228 209.156 28.4969C209.057 28.1808 208.9 27.8862 208.692 27.6286C208.474 27.3709 208.207 27.1596 207.906 27.0073C207.536 26.8508 207.139 26.7702 206.738 26.7702C206.337 26.7702 205.94 26.8508 205.57 27.0073C205.26 27.1484 204.984 27.3553 204.762 27.6136C204.543 27.873 204.373 28.1697 204.26 28.4894C204.143 28.8199 204.058 29.1608 204.006 29.5075L209.328 29.5Z" fill="#202C45"/>
				<path d="M220.406 35.1104C220.939 35.1457 221.474 35.0719 221.978 34.8934C222.13 34.8167 222.256 34.6953 222.337 34.5455C222.419 34.3957 222.453 34.2246 222.435 34.055C222.419 33.8735 222.355 33.6995 222.25 33.5508C222.145 33.4021 222.002 33.2842 221.836 33.2091C221.262 32.8765 220.658 32.5983 220.032 32.3782C219.445 32.16 218.87 31.9101 218.31 31.6296C217.829 31.3976 217.387 31.092 217 30.7239C216.638 30.366 216.353 29.9381 216.162 29.4663C215.954 28.9061 215.855 28.3117 215.87 27.7146C215.848 27.1056 215.972 26.5001 216.233 25.9492C216.493 25.3982 216.882 24.9178 217.367 24.5482C218.559 23.716 219.995 23.3074 221.447 23.3879C222.281 23.3833 223.113 23.461 223.932 23.62C224.576 23.7292 225.21 23.8945 225.826 24.114L225.122 27.2505C224.631 27.0811 224.131 26.9362 223.625 26.8163C223.017 26.6879 222.397 26.6252 221.776 26.6292C220.518 26.6292 219.882 26.981 219.882 27.6847C219.881 27.8334 219.909 27.981 219.964 28.1188C220.037 28.2659 220.148 28.3903 220.286 28.4782C220.497 28.6239 220.72 28.7517 220.953 28.8599C221.23 28.9947 221.589 29.1444 222.023 29.3091C222.773 29.5811 223.503 29.9037 224.209 30.2747C224.716 30.5421 225.177 30.8911 225.571 31.3077C225.896 31.6578 226.137 32.0778 226.275 32.5354C226.417 33.0444 226.486 33.5714 226.477 34.0999C226.511 34.7215 226.384 35.3413 226.107 35.8989C225.83 36.4565 225.413 36.9328 224.898 37.2813C223.541 38.0806 221.977 38.456 220.406 38.3592C219.347 38.3788 218.288 38.2707 217.255 38.0373C216.66 37.9005 216.077 37.7178 215.51 37.4909L216.184 34.2271C216.869 34.4955 217.575 34.7058 218.295 34.8559C218.988 35.0159 219.695 35.1012 220.406 35.1104Z" fill="#202C45"/>
				<path d="M233.553 35.1107C234.083 35.146 234.616 35.0721 235.117 34.8936C235.271 34.8189 235.399 34.6982 235.482 34.5481C235.565 34.398 235.6 34.2258 235.581 34.0552C235.565 33.8738 235.502 33.6997 235.396 33.5511C235.291 33.4024 235.148 33.2845 234.982 33.2094C234.405 32.877 233.799 32.5988 233.171 32.3784C232.587 32.159 232.015 31.9091 231.457 31.6299C230.975 31.3978 230.534 31.0922 230.147 30.7241C229.784 30.3663 229.499 29.9384 229.308 29.4665C229.098 28.9069 228.996 28.3125 229.009 27.7149C228.987 27.1058 229.111 26.5004 229.372 25.9494C229.632 25.3985 230.021 24.918 230.506 24.5484C231.697 23.7152 233.134 23.3065 234.586 23.3882C235.42 23.3839 236.252 23.4616 237.071 23.6202C237.715 23.7305 238.349 23.8958 238.965 24.1143L238.261 27.2508C237.769 27.0814 237.27 26.9365 236.764 26.8166C236.156 26.6882 235.536 26.6255 234.915 26.6295C233.65 26.6295 233.021 26.9813 233.021 27.6849C233.019 27.8337 233.047 27.9813 233.103 28.1191C233.176 28.2662 233.287 28.3905 233.425 28.4784C233.635 28.6259 233.858 28.7537 234.091 28.8602C234.368 28.9949 234.728 29.1446 235.154 29.3093C235.89 29.5896 236.605 29.9197 237.295 30.2974C237.803 30.5649 238.263 30.9138 238.658 31.3305C238.983 31.6806 239.224 32.1005 239.361 32.5581C239.504 33.0671 239.572 33.5941 239.563 34.1226C239.598 34.7442 239.47 35.364 239.193 35.9216C238.917 36.4792 238.5 36.9555 237.984 37.304C236.628 38.1029 235.064 38.4783 233.493 38.382C232.436 38.4011 231.38 38.2931 230.349 38.0601C229.754 37.9232 229.171 37.7405 228.604 37.5136L229.278 34.2499C229.963 34.5192 230.669 34.7296 231.389 34.8787C232.1 35.0348 232.825 35.1126 233.553 35.1107Z" fill="#202C45"/>
			</svg>
		</div>
	</div>
	<div class="bpa-fws__body">
		<el-tabs type="card" class="bpa-fws-tab-wrapper" v-model="bookingpress_active_tab">
			<el-tab-pane class="bpa-fws-tab-pane-item" name="company_settings" :disabled="bookingpress_disabled_tabs">
				<div class="bpa-tpi__tab-menu-item" slot="label">	
					<span class="bpa-tpi__counter">01</span>
					<div class="bpa-tpi__item-link"><?php esc_html_e('Your Site', 'bookingpress-appointment-booking'); ?></div>
				</div>
				<div class="bpa-fws-tab-pane-body">
					<div class="bpa-tpb__head">
						<h3><?php esc_html_e('Your Site', 'bookingpress-appointment-booking'); ?></h3>
						<p><?php esc_html_e('Give some information about your company', 'bookingpress-appointment-booking'); ?></p>
					</div>
					<div class="bpa-tpb__form-body">
						<el-row :gutter="32">
							<el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12">
								<label class="bpa-form-label"><?php esc_html_e( 'Company Name', 'bookingpress-appointment-booking' ); ?></label>
								<el-input class="bpa-form-control" v-model="wizard_steps_data.company_fields_data.company_name" placeholder="<?php esc_html_e( 'Enter your company name', 'bookingpress-appointment-booking' ); ?>"></el-input>
							</el-col>
							<el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12">
								<label class="bpa-form-label"><?php esc_html_e( 'Address', 'bookingpress-appointment-booking' ); ?></label>
								<el-input class="bpa-form-control" v-model="wizard_steps_data.company_fields_data.address" type="textarea" rows="1" placeholder="<?php esc_html_e( 'Enter your company address', 'bookingpress-appointment-booking' ); ?>"></el-input>
							</el-col>
						</el-row>
						<el-row :gutter="32">
							<el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12">
								<label class="bpa-form-label"><?php esc_html_e( 'Time Format', 'bookingpress-appointment-booking' ); ?></label>
								<el-select class="bpa-form-control" v-model="wizard_steps_data.company_fields_data.time_format" placeholder="<?php esc_html_e('Select Time Format', 'bookingpress-appointment-booking'); ?>">
									<el-option label="<?php esc_html_e('12 hour Format','bookingpress-appointment-booking'); ?>" value="g:i a"><?php esc_html_e('12 hour Format','bookingpress-appointment-booking'); ?></el-option>
									<el-option label="<?php esc_html_e('24 hour Format','bookingpress-appointment-booking'); ?>" value="H:i"><?php esc_html_e('24 hour Format','bookingpress-appointment-booking'); ?></el-option>
								</el-select>
							</el-col>
							<el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12">
								<label class="bpa-form-label"><?php esc_html_e( 'Date Format', 'bookingpress-appointment-booking' ); ?></label>
								<el-select class="bpa-form-control" v-model="wizard_steps_data.company_fields_data.date_format" placeholder="<?php esc_html_e('Select date format', 'bookingpress-appointment-booking'); ?>">
									<el-option label="<?php echo esc_html('F j, Y'); ?>" value="F j, Y"><?php echo esc_html('F j, Y'); ?></el-option>
									<el-option label="<?php echo esc_html('Y-m-d'); ?>" value="Y-m-d"><?php echo esc_html('Y-m-d'); ?></el-option>
									<el-option label="<?php echo esc_html('m/d/Y'); ?>" value="m/d/Y"><?php echo esc_html('m/d/Y'); ?></el-option>
									<el-option label="<?php echo esc_html('d/m/Y'); ?>" value="d/m/Y"><?php echo esc_html('d/m/Y'); ?></el-option>
									<el-option label="<?php echo esc_html('d.m.Y'); ?>" value="d.m.Y"><?php echo esc_html('d.m.Y'); ?></el-option>
									<el-option label="<?php echo esc_html('d-m-Y'); ?>" value="d-m-Y"><?php echo esc_html('d-m-Y'); ?></el-option>
								</el-select>
							</el-col>                      
                        </el-row>
                        <el-row :gutter="32">
						<el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12">
                                <label class="bpa-form-label"><?php esc_html_e( 'Country', 'bookingpress-appointment-booking' ); ?></label>
                                <el-select class="bpa-form-control" filterable v-model="wizard_steps_data.company_fields_data.country" placeholder="<?php esc_html_e('Select your country', 'bookingpress-appointment-booking'); ?>">
                                    <el-option value="auto_detect" label="<?php esc_html_e( 'Identify country code by user\'s IP address', 'bookingpress-appointment-booking' ); ?>"></el-option>
                                    <el-option v-for="countries in phone_countries_details" :value="countries.code" :label="countries.name">
                                        <span class="flag" :class="countries.code"></span> {{ countries.name }}
                                    </el-option>
                                </el-select>
                            </el-col>
							<el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12">
								<label class="bpa-form-label"><?php esc_html_e( 'Phone No', 'bookingpress-appointment-booking' ); ?></label>
								<el-input class="bpa-form-control" v-model="wizard_steps_data.company_fields_data.phone_no" placeholder="<?php esc_html_e( 'Enter phone no', 'bookingpress-appointment-booking' ); ?>"></el-input>
							</el-col>
						</el-row>
						<el-row :gutter="32">	
							<el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12">
								<label class="bpa-form-label"><?php esc_html_e( 'Website', 'bookingpress-appointment-booking' ); ?></label>
								<el-input class="bpa-form-control" v-model="wizard_steps_data.company_fields_data.website" placeholder="<?php esc_html_e( 'Enter your website url', 'bookingpress-appointment-booking' ); ?>"></el-input>
							</el-col>
						</el-row>	
						<el-row>													
							<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
								<div class="bpa-wizard-setup-upload-component__wrapper">
									<label class="bpa-form-label"><?php esc_html_e( 'Company Logo', 'bookingpress-appointment-booking' ); ?></label>
									<el-upload class="bpa-upload-component" ref="avatarRef" action="<?php echo wp_nonce_url(admin_url('admin-ajax.php') . '?action=bookingpress_upload_company_avatar', 'bookingpress_upload_company_avatar'); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped --Reason - esc_html is already used by wp_nonce_url function and it's false positive ?>" :on-success="bookingpress_upload_company_avatar_func" multiple="false" :show-file-list="comShowFileList" limit="1" :on-exceed="bookingpress_company_avatar_upload_limit" :on-error="bookingpress_company_avatar_upload_err" :on-remove="bookingpress_remove_company_avatar" :before-upload="checkUploadedFile" drag v-if="wizard_steps_data.company_fields_data.logo == ''">
									
										<span class="material-icons-round bpa-upload-component__icon">cloud_upload</span>
										<div class="bpa-upload-component__text" v-if="wizard_steps_data.company_fields_data.logo == ''"><?php esc_html_e('Please upload jpg/png/webp file', 'bookingpress-appointment-booking'); ?>  </div>
									</el-upload>
									<div class="bpa-uploaded-avatar__preview bpa-uploaded-avatar__preview--company-settings" v-if="wizard_steps_data.company_fields_data.logo != ''">
										<button class="bpa-avatar-close-icon" @click="bookingpress_remove_company_avatar">
											<span class="material-icons-round">close</span>
										</button>
										<el-avatar shape="square" :src="wizard_steps_data.company_fields_data.logo" class="bpa-uploaded-avatar__picture"></el-avatar>
									</div>
								</div>
							</el-col>
						</el-row>
						<div class="bpa-tpb__full-width-inline">
							<el-row :gutter="32">
								<el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12">
									<label class="bpa-form-label"><?php esc_html_e( 'Help us improve BookingPress by sending anonymous usage stats', 'bookingpress-appointment-booking' ); ?></label>
								</el-col>
								<el-col :xs="12" :sm="12" :md="12" :lg="12" :xl="12" class="bpa-fwi__right">
									<el-switch class="bpa-swtich-control" v-model="wizard_steps_data.company_fields_data.anonymous_usage"></el-switch>
								</el-col>
							</el-row>
						</div>
					</div>
					<div class="bpa-tpb__foot-action-btns">
						<div class="bpa-fab--wrapper">
							<el-button class="bpa-btn bpa-btn--primary bpa-btn__medium" @click="bookingpress_next_tab('company_settings')">
								<?php esc_html_e( 'Next', 'bookingpress-appointment-booking' ); ?>
								<span class="material-icons-round">east</span>
							</el-button>
						</div>
					</div>
				</div>
			</el-tab-pane>
			<el-tab-pane class="bpa-fws-tab-pane-item" name="booking_options" :disabled="bookingpress_disabled_tabs">
				<div class="bpa-tpi__tab-menu-item" slot="label">	
					<span class="bpa-tpi__counter">02</span>
					<div class="bpa-tpi__item-link"><?php esc_html_e('Booking Options', 'bookingpress-appointment-booking'); ?></div>
				</div>
				<div class="bpa-fws-tab-pane-body">
					<div class="bpa-tpb__head">
						<h3><?php esc_html_e('Booking Options', 'bookingpress-appointment-booking'); ?></h3>
						<p><?php esc_html_e('Help us understand your booking options', 'bookingpress-appointment-booking'); ?></p>
					</div>
					<div class="bpa-tpb__form-body">
						<div class="bpa-tpb__form-body-section">
							<h4><?php esc_html_e('Default working hours', 'bookingpress-appointment-booking'); ?></h4>							
							<el-row type="flex" align="middle" :gutter="32">
								<el-col :xs="08" :sm="08" :md="08" :lg="08" :xl="08">
									<label class="bpa-form-label"><?php esc_html_e( 'Monday', 'bookingpress-appointment-booking' ); ?></label>									
								</el-col>
								<el-col :xs="08" :sm="08" :md="08" :lg="08" :xl="08" >
									<el-select @change="bookingpress_set_workhour_value_wizard($event,'monday')" filterable v-model="wizard_steps_data.booking_options.monday.start_time" class="bpa-form-control">
										<el-option v-for="workhours_arr in working_hours_arr" :label="workhours_arr.formatted_start_time" :value="workhours_arr.start_time"></el-option>
									</el-select>
								</el-col>
								<el-col :xs="08" :sm="08" :md="08" :lg="08" :xl="08" v-if="wizard_steps_data.booking_options.monday.start_time != 'off'">
									<el-select filterable v-model="wizard_steps_data.booking_options.monday.end_time" class="bpa-form-control" @change="bookingpress_check_workhour_value_wizard($event,'monday')">
										<el-option v-for="workhours_arr in working_hours_arr" v-if="( workhours_arr.end_time > wizard_steps_data.booking_options.monday.start_time || workhours_arr.end_time == '24:00:00')" :label="workhours_arr.formatted_end_time" :value="workhours_arr.end_time"></el-option>
								</el-col>
							</el-row>
							<el-row type="flex" align="middle" :gutter="32">
								<el-col :xs="08" :sm="08" :md="08" :lg="08" :xl="08">
									<label class="bpa-form-label"><?php esc_html_e( 'Tuesday', 'bookingpress-appointment-booking' ); ?></label>									
								</el-col>
								<el-col :xs="08" :sm="08" :md="08" :lg="08" :xl="08">									
									<el-select @change="bookingpress_set_workhour_value_wizard($event,'tuesday')" filterable v-model="wizard_steps_data.booking_options.tuesday.start_time" class="bpa-form-control">
										<el-option v-for="workhours_arr in working_hours_arr" :label="workhours_arr.formatted_start_time" :value="workhours_arr.start_time"></el-option>
									</el-select>
								</el-col>
								<el-col :xs="08" :sm="08" :md="08" :lg="08" :xl="08" v-if="wizard_steps_data.booking_options.tuesday.start_time != 'off'">
									<el-select filterable v-model="wizard_steps_data.booking_options.tuesday.end_time" class="bpa-form-control" @change="bookingpress_check_workhour_value_wizard($event,'tuesday')">
										<el-option v-for="workhours_arr in working_hours_arr" v-if="( workhours_arr.end_time > wizard_steps_data.booking_options.tuesday.start_time || workhours_arr.end_time == '24:00:00')" :label="workhours_arr.formatted_end_time" :label="workhours_arr.formatted_end_time" :value="workhours_arr.end_time"></el-option>
									</el-select>
								</el-col>
							</el-row>
							<el-row type="flex" align="middle" :gutter="32">
								<el-col :xs="08" :sm="08" :md="08" :lg="08" :xl="08">
									<label class="bpa-form-label"><?php esc_html_e( 'Wednesday', 'bookingpress-appointment-booking' ); ?></label>									
								</el-col>
								<el-col :xs="08" :sm="08" :md="08" :lg="08" :xl="08">									
									<el-select @change="bookingpress_set_workhour_value_wizard($event,'wednesday')" filterable v-model="wizard_steps_data.booking_options.wednesday.start_time" class="bpa-form-control">
										<el-option v-for="workhours_arr in working_hours_arr" :label="workhours_arr.formatted_start_time" :value="workhours_arr.start_time"></el-option>
									</el-select>
								</el-col>
								<el-col :xs="08" :sm="08" :md="08" :lg="08" :xl="08" v-if="wizard_steps_data.booking_options.wednesday.start_time != 'off'">
									<el-select filterable v-model="wizard_steps_data.booking_options.wednesday.end_time" class="bpa-form-control" @change="bookingpress_check_workhour_value_wizard($event,'wednesday')">
										<el-option v-for="workhours_arr in working_hours_arr" v-if="( workhours_arr.end_time > wizard_steps_data.booking_options.wednesday.start_time || workhours_arr.end_time == '24:00:00')" :label="workhours_arr.formatted_end_time" :label="workhours_arr.formatted_end_time" :value="workhours_arr.end_time"></el-option>
									</el-select>
								</el-col>
							</el-row>
							<el-row type="flex" align="middle" :gutter="32">
								<el-col :xs="08" :sm="08" :md="08" :lg="08" :xl="08">
									<label class="bpa-form-label"><?php esc_html_e( 'Thursday', 'bookingpress-appointment-booking' ); ?></label>									
								</el-col>
								<el-col :xs="08" :sm="08" :md="08" :lg="08" :xl="08">									
									<el-select @change="bookingpress_set_workhour_value_wizard($event,'thursday')" filterable v-model="wizard_steps_data.booking_options.thursday.start_time" class="bpa-form-control">
										<el-option v-for="workhours_arr in working_hours_arr" :label="workhours_arr.formatted_start_time" :value="workhours_arr.start_time"></el-option>
									</el-select>
								</el-col>
								<el-col :xs="08" :sm="08" :md="08" :lg="08" :xl="08" v-if="wizard_steps_data.booking_options.thursday.start_time != 'off'">
									<el-select filterable v-model="wizard_steps_data.booking_options.thursday.end_time" class="bpa-form-control" @change="bookingpress_check_workhour_value_wizard($event,'thursday')">
										<el-option v-for="workhours_arr in working_hours_arr" v-if="( workhours_arr.end_time > wizard_steps_data.booking_options.thursday.start_time || workhours_arr.end_time == '24:00:00')" :label="workhours_arr.formatted_end_time" :label="workhours_arr.formatted_end_time" :value="workhours_arr.end_time"></el-option>
									</el-select>
								</el-col>
							</el-row>
							<el-row type="flex" align="middle" :gutter="32">
								<el-col :xs="08" :sm="08" :md="08" :lg="08" :xl="08">
									<label class="bpa-form-label"><?php esc_html_e( 'Friday', 'bookingpress-appointment-booking' ); ?></label>									
								</el-col>
								<el-col :xs="08" :sm="08" :md="08" :lg="08" :xl="08">									
									<el-select  @change="bookingpress_set_workhour_value_wizard($event,'friday')" filterable v-model="wizard_steps_data.booking_options.friday.start_time" class="bpa-form-control">
										<el-option v-for="workhours_arr in working_hours_arr" :label="workhours_arr.formatted_start_time" :value="workhours_arr.start_time"></el-option>
									</el-select>
								</el-col>
								<el-col :xs="08" :sm="08" :md="08" :lg="08" :xl="08" v-if="wizard_steps_data.booking_options.friday.start_time != 'off'">
									<el-select filterable v-model="wizard_steps_data.booking_options.friday.end_time" class="bpa-form-control" @change="bookingpress_check_workhour_value_wizard($event,'friday')">
										<el-option v-for="workhours_arr in working_hours_arr" v-if="( workhours_arr.end_time > wizard_steps_data.booking_options.friday.start_time || workhours_arr.end_time == '24:00:00')" :label="workhours_arr.formatted_end_time" :label="workhours_arr.formatted_end_time" :value="workhours_arr.end_time"></el-option>
									</el-select>
								</el-col>
							</el-row>
							<el-row type="flex" align="middle" :gutter="32">
								<el-col :xs="08" :sm="08" :md="08" :lg="08" :xl="08">
									<label class="bpa-form-label"><?php esc_html_e( 'Saturday', 'bookingpress-appointment-booking' ); ?></label>
								</el-col>
								<el-col :xs="08" :sm="08" :md="08" :lg="08" :xl="08">									
									<el-select @change="bookingpress_set_workhour_value_wizard($event,'saturday')" filterable v-model="wizard_steps_data.booking_options.saturday.start_time" class="bpa-form-control">
										<el-option v-for="workhours_arr in working_hours_arr" :label="workhours_arr.formatted_start_time" :value="workhours_arr.start_time"></el-option>
									</el-select>
								</el-col>
								<el-col :xs="08" :sm="08" :md="08" :lg="08" :xl="08" v-if="wizard_steps_data.booking_options.saturday.start_time != 'off'">									
									<el-select filterable v-model="wizard_steps_data.booking_options.saturday.end_time" class="bpa-form-control" @change="bookingpress_check_workhour_value_wizard($event,'saturday')">
										<el-option v-for="workhours_arr in working_hours_arr" v-if="( workhours_arr.end_time > wizard_steps_data.booking_options.saturday.start_time || workhours_arr.end_time == '24:00:00')" :label="workhours_arr.formatted_end_time" :label="workhours_arr.formatted_end_time" :value="workhours_arr.end_time"></el-option>
									</el-select>
								</el-col>
							</el-row>
							<el-row type="flex" align="middle" :gutter="32">
								<el-col :xs="08" :sm="08" :md="08" :lg="08" :xl="08">
									<label class="bpa-form-label"><?php esc_html_e( 'Sunday', 'bookingpress-appointment-booking' ); ?></label>									
								</el-col>
								<el-col :xs="08" :sm="08" :md="08" :lg="08" :xl="08">									
									<el-select @change="bookingpress_set_workhour_value_wizard($event,'sunday')" filterable v-model="wizard_steps_data.booking_options.sunday.start_time" class="bpa-form-control">
										<el-option v-for="workhours_arr in working_hours_arr" :label="workhours_arr.formatted_start_time" :value="workhours_arr.start_time"></el-option>
									</el-select>
								</el-col>
								<el-col :xs="08" :sm="08" :md="08" :lg="08" :xl="08" v-if="wizard_steps_data.booking_options.sunday.start_time != 'off'">
									<el-select filterable v-model="wizard_steps_data.booking_options.sunday.end_time" class="bpa-form-control" @change="bookingpress_check_workhour_value_wizard($event,'sunday')">
										<el-option v-for="workhours_arr in working_hours_arr" v-if="( workhours_arr.end_time > wizard_steps_data.booking_options.sunday.start_time || workhours_arr.end_time == '24:00:00')" :label="workhours_arr.formatted_end_time" :label="workhours_arr.formatted_end_time" :value="workhours_arr.end_time"></el-option>
									</el-select>
								</el-col>
							</el-row>
						</div>
						<el-row>
							<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
								<label class="bpa-form-label"><?php esc_html_e( 'Currency', 'bookingpress-appointment-booking' ); ?></label>	
								<el-select class="bpa-form-control" v-model="wizard_steps_data.booking_options.currency" placeholder="<?php esc_html_e('Select Currency', 'bookingpress-appointment-booking'); ?>">
									<el-option v-for="currency_data in bookingpress_currency_options" :value="currency_data.code" :label="currency_data.name">
										<div class="bpa-fc__item--currency-custom-dropdown-item">
											<el-image :src="'<?php echo esc_url_raw(BOOKINGPRESS_IMAGES_URL); ?>/country-flags/'+currency_data.iso+'.png'"></el-image>
											<div class="bpa-fc__item--currency-custom-dropdown-item__body">
												<p>{{ currency_data.name }}</p>
												<span>{{ currency_data.symbol }}</span>
											</div>
										</div>
									</el-option>
								</el-select>
							</el-col>
						</el-row>
					</div>
					<div class="bpa-tpb__foot-action-btns">
						<div class="bpa-fab--wrapper">
							<el-button class="bpa-btn bpa-btn__medium" @click="bookingpress_previous_tab('booking_options')">
								<span class="material-icons-round">west</span>
								<?php esc_html_e( 'Prev', 'bookingpress-appointment-booking' ); ?>
							</el-button>
							<el-button class="bpa-btn bpa-btn--primary bpa-btn__medium" @click="bookingpress_next_tab('booking_options')">
								<?php esc_html_e( 'Next', 'bookingpress-appointment-booking' ); ?>
								<span class="material-icons-round">east</span>
							</el-button>
						</div>
					</div>
				</div>
			</el-tab-pane>
			<el-tab-pane class="bpa-fws-tab-pane-item" name="service_options" :disabled="bookingpress_disabled_tabs">
				<div class="bpa-tpi__tab-menu-item" slot="label">
					<span class="bpa-tpi__counter">03</span>
					<div class="bpa-tpi__item-link"><?php esc_html_e('Service', 'bookingpress-appointment-booking'); ?></div>
				</div>
				<div class="bpa-fws-tab-pane-body">
					<div class="bpa-tpb__head">
						<h3><?php esc_html_e('Service Options', 'bookingpress-appointment-booking'); ?></h3>
						<p><?php esc_html_e('Add your services here with basic options or you can add them later too', 'bookingpress-appointment-booking'); ?></p>
					</div>
					<div class="bpa-tpb__form-body">						
						<div class="bpa-tpb__form-body-section bpa-fbs--has-form-label-spacing">
							<div v-for="(service_details, index) in wizard_steps_data.service_options.service_details">
								<div class="bpa-fbs__staff-form-row bpa-fbs__row-marign-zero">
									<el-row :gutter="24">
										<el-col :xs="08" :sm="08" :md="08" :lg="08" :xl="08">
											<label class="bpa-form-label">{{ service_details.service_name_label }}</label>	
											<el-input class="bpa-form-control" v-model="wizard_steps_data.service_options.service_fields_details[index].service_name" :placeholder="service_details.service_name_label"></el-input>
										</el-col>
										<el-col :xs="08" :sm="08" :md="08" :lg="08" :xl="08">
											<label class="bpa-form-label">{{ service_details.price_label }}</label>	
											<el-input class="bpa-form-control" v-model="wizard_steps_data.service_options.service_fields_details[index].price" :placeholder="service_details.price_label"></el-input>
										</el-col>
										<el-col :xs="08" :sm="08" :md="08" :lg="08" :xl="08">
											<span class="bpa-form-label">{{ service_details.duration_label }} </span>
											<el-row :gutter="10">
												<el-col :xs="16" :sm="16" :md="16" :lg="16" :xl="16">
													<el-input-number class="bpa-form-control bpa-form-control--number" :min="1" v-model="wizard_steps_data.service_options.service_fields_details[index].duration_val"></el-input-number>
												</el-col>
												<el-col :xs="08" :sm="08" :md="08" :lg="08" :xl="08">
													<el-select v-model="wizard_steps_data.service_options.service_fields_details[index].duration_unit" class="bpa-form-control" popper-class="bpa-el-select--is-with-modal bpa-el-select--wizard">
														<el-option value="m" label="<?php esc_html_e('Mins', 'bookingpress-appointment-booking'); ?>"></el-option>
														<el-option value="h" label="<?php esc_html_e('Hours', 'bookingpress-appointment-booking'); ?>"></el-option>
													</el-select>
												</el-col>
											</el-row>
										</el-col>
										<div class="bpa-sfr__action-icon">
											<a href="javascript:void(0)" @click="bpa_remove_service(index)" v-if="index != 0">
												<span class="material-icons-round">remove_circle</span>
											</a>
                                            <a href="javascript:void(0)" @click="bookingpress_add_service" v-if="index == 0">
                                                <span class="material-icons-round">add_circle</span>
                                            </a>
										</div>
									</el-row>
								</div>
								<div class="bpa-fbs__staff-form-row">
									<el-row :gutter="24">
										<el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
											<label class="bpa-form-label">{{ service_details.description_label }}</label>	
											<el-input v-model="wizard_steps_data.service_options.service_fields_details[index].description" class="bpa-form-control" type="textarea" :placeholder="service_details.description_label" rows="3"></el-input>
										</el-col>
									</el-row>
								</div>
							</div>
						</div>
					</div>
					<div class="bpa-tpb__foot-action-btns">
						<div class="bpa-fab--wrapper">
							<el-button class="bpa-btn bpa-btn__medium" @click="bookingpress_previous_tab('service_options')">
								<span class="material-icons-round">west</span>
								<?php esc_html_e( 'Prev', 'bookingpress-appointment-booking' ); ?>
							</el-button>
							<el-button class="bpa-btn bpa-btn--primary bpa-btn__medium" @click="bookingpress_next_tab('service_options')">
								<?php esc_html_e( 'Next', 'bookingpress-appointment-booking' ); ?>
								<span class="material-icons-round">east</span>
							</el-button>
						</div>
					</div>
				</div>
			</el-tab-pane>
			<el-tab-pane class="bpa-fws-tab-pane-item" name="styling_options" :disabled="bookingpress_disabled_tabs">
				<div class="bpa-tpi__tab-menu-item" slot="label">
					<span class="bpa-tpi__counter">04</span>
					<div class="bpa-tpi__item-link"><?php esc_html_e('Styling', 'bookingpress-appointment-booking'); ?></div>
				</div>
				<div class="bpa-fws-tab-pane-body">
					<div class="bpa-tpb__head">
						<h3><?php esc_html_e('Styling', 'bookingpress-appointment-booking'); ?></h3>
						<p><?php esc_html_e('Almost there. Choose basic colors & fonts.', 'bookingpress-appointment-booking'); ?></p>
					</div>
					<div class="bpa-tpb__form-body bpa-tbp--styling-tab-body">
						<div class="bpa-tpb__full-width-inline">
							<el-row type="flex" align="middle">
								<el-col :xs="16" :sm="16" :md="16" :lg="16" :xl="16">
									<label class="bpa-form-label"><?php esc_html_e( 'Font Selection', 'bookingpress-appointment-booking' ); ?></label>	
								</el-col>
								<el-col :xs="08" :sm="08" :md="08" :lg="08" :xl="08" class="bpa-fwi__right">
									<el-select v-model="wizard_steps_data.styling_options.font_family" class="bpa-form-control" placeholder="<?php esc_html_e('Select Font', 'bookingpress-appointment-booking'); ?>" filterable>
										<el-option-group v-for="item_data in fonts_list" :key="item_data.label" :label="item_data.label">
											<el-option v-for="item in item_data.options" :key="item" :label="item" :value="item"></el-option>
										</el-option-group>
									</el-select>
								</el-col>
							</el-row>
						</div>
						<div class="bpa-tpb__full-width-inline">
						 
							<div class="el-form-style-option">
								<label class="bpa-wizard-styling-tab"><?php esc_html_e( 'Color Options', 'bookingpress-appointment-booking' ); ?></label>	
							</div>
							<el-row type="flex" align="middle" class="bpa-wizard-space-content">
								<el-col :xs="16" :sm="16" :md="16" :lg="16" :xl="16">
									<span class="bpa-tp-color-picker-text bpa-form-label bpa-wizard-stlye-text"><?php esc_html_e('Primary', 'bookingpress-appointment-booking'); ?></span>
								</el-col>
								<el-col :xs="08" :sm="08" :md="08" :lg="08" :xl="08" class="bpa-fwi__right">
									<div class="bpa-cpr__item">
										<el-color-picker class="bpa-customize-tp__color-picker" v-model="wizard_steps_data.styling_options.primary_color" @change="bookingpress_generate_alpha_color"></el-color-picker>
									</div>
								</el-col>
							</el-row>
							<el-row type="flex" align="middle" class="bpa-wizard-space-content">
								<el-col :xs="16" :sm="16" :md="16" :lg="16" :xl="16">
									<span class="bpa-tp-color-picker-text bpa-form-label bpa-wizard-stlye-text"><?php esc_html_e('Title', 'bookingpress-appointment-booking'); ?></span>
								</el-col>
								<el-col :xs="08" :sm="08" :md="08" :lg="08" :xl="08" class="bpa-fwi__right">
									<div class="bpa-cpr__item">
										<el-color-picker class="bpa-customize-tp__color-picker" v-model="wizard_steps_data.styling_options.title_color"></el-color-picker>
									</div>
								</el-col>
							</el-row>
							<el-row type="flex" align="middle" class="bpa-wizard-space-content">
								<el-col :xs="16" :sm="16" :md="16" :lg="16" :xl="16">
									<span class="bpa-tp-color-picker-text bpa-form-label bpa-wizard-stlye-text"><?php esc_html_e('Sub Title', 'bookingpress-appointment-booking'); ?></span>
								</el-col>
								<el-col :xs="08" :sm="08" :md="08" :lg="08" :xl="08" class="bpa-fwi__right">
									<div class="bpa-cpr__item">
										<el-color-picker class="bpa-customize-tp__color-picker" v-model="wizard_steps_data.styling_options.subtitle_color"></el-color-picker>
									</div>
								</el-col>
							</el-row>
							<el-row type="flex" align="middle" class="bpa-wizard-space-content">
								<el-col :xs="16" :sm="16" :md="16" :lg="16" :xl="16">
									<span class="bpa-tp-color-picker-text bpa-form-label bpa-wizard-stlye-text"><?php esc_html_e('Content', 'bookingpress-appointment-booking'); ?></span>
								</el-col>
								<el-col :xs="08" :sm="08" :md="08" :lg="08" :xl="08" class="bpa-fwi__right">
									<div class="bpa-cpr__item">
										<el-color-picker class="bpa-customize-tp__color-picker" v-model="wizard_steps_data.styling_options.content_color"></el-color-picker>
									</div>
								</el-col>
							</el-row>
						</div>
					</div>
					<div class="bpa-tpb__foot-action-btns">
						<div class="bpa-fab--wrapper">
							<el-button class="bpa-btn bpa-btn__medium" @click="bookingpress_previous_tab('styling_options')">
								<span class="material-icons-round">west</span>
								<?php esc_html_e( 'Prev', 'bookingpress-appointment-booking' ); ?>
							</el-button>
							<el-button class="bpa-btn bpa-btn--primary bpa-btn__medium" @click="bookingpress_next_tab('styling_options')">
								<?php esc_html_e( 'Finish', 'bookingpress-appointment-booking' ); ?>								
							</el-button>
						</div>
					</div>
				</div>
			</el-tab-pane>
			<el-tab-pane class="bpa-fws-tab-pane-item" name="final_step" :disabled="bookingpress_disabled_tabs">
				<div class="bpa-back-loader-container" v-show="final_step_loader == '1'">
					<div class="bpa-back-loader"></div>
				</div>
				<div class="bpa-tpi__tab-menu-item" slot="label">	
					<span class="bpa-tpi__counter">05</span>
					<div class="bpa-tpi__item-link"><?php esc_html_e('Ready', 'bookingpress-appointment-booking'); ?></div>
				</div>
				<div class="bpa-fws-tab-pane-body bpa-fws-final-summary-body">
					<div class="bpa-fsb__wrapper" v-show="final_step_loader == '0'">
						<div class="bpa-fsb__head">
							<svg width="64" height="64" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path fill-rule="evenodd" clip-rule="evenodd" d="M58.0482 54.6951C54.0401 59.5264 46.3332 58.2356 40.2098 59.7576C34.2615 61.236 28.4075 65.4212 22.5866 63.5088C16.7744 61.5993 14.6173 54.7815 10.7497 50.0791C6.94962 45.4587 0.73474 42.0453 0.0626469 36.1232C-0.60875 30.2072 4.26978 25.2942 7.35726 20.1865C10.1553 15.5575 12.8977 10.9689 17.238 7.70908C21.8869 4.21748 27.1357 0.976317 32.9699 0.949609C38.8481 0.922699 43.8328 4.62091 48.9047 7.56412C54.2778 10.6821 61.2547 12.8307 63.4138 18.6162C65.5703 24.395 60.548 30.2399 59.6415 36.3351C58.7191 42.5377 62.0669 49.8512 58.0482 54.6951Z" fill="#F4F7FB"/>
								<mask id="path-2-inside-1_3545_20611" fill="white">
								<path d="M5.20347 20.2292L5.4 20.8283H6.03598L5.52146 21.1986L5.71799 21.7977L5.20347 21.4274L4.68896 21.7977L4.88548 21.1986L4.37097 20.8283H5.00694L5.20347 20.2292Z"/>
								</mask>
								<path d="M5.20347 20.2292L5.92379 19.993L5.20347 17.7972L4.48316 19.993L5.20347 20.2292ZM5.4 20.8283L4.67968 21.0646L4.85085 21.5864H5.4V20.8283ZM6.03598 20.8283L6.47878 21.4437L8.38728 20.0703H6.03598V20.8283ZM5.52146 21.1986L5.07866 20.5833L4.62813 20.9075L4.80114 21.4349L5.52146 21.1986ZM5.71799 21.7977L5.27519 22.413L7.16342 23.7718L6.4383 21.5614L5.71799 21.7977ZM5.20347 21.4274L5.64627 20.8121L5.20347 20.4935L4.76067 20.8121L5.20347 21.4274ZM4.68896 21.7977L3.96864 21.5614L3.24352 23.7718L5.13176 22.413L4.68896 21.7977ZM4.88548 21.1986L5.6058 21.4349L5.77881 20.9075L5.32828 20.5833L4.88548 21.1986ZM4.37097 20.8283V20.0703H2.01966L3.92817 21.4437L4.37097 20.8283ZM5.00694 20.8283V21.5864H5.55609L5.72726 21.0646L5.00694 20.8283ZM4.48316 20.4655L4.67968 21.0646L6.12032 20.592L5.92379 19.993L4.48316 20.4655ZM5.4 21.5864H6.03598V20.0703H5.4V21.5864ZM5.59318 20.213L5.07866 20.5833L5.96426 21.8139L6.47878 21.4437L5.59318 20.213ZM4.80114 21.4349L4.99767 22.034L6.4383 21.5614L6.24178 20.9623L4.80114 21.4349ZM6.16079 21.1824L5.64627 20.8121L4.76067 22.0427L5.27519 22.413L6.16079 21.1824ZM4.76067 20.8121L4.24616 21.1824L5.13176 22.413L5.64627 22.0427L4.76067 20.8121ZM5.40927 22.034L5.6058 21.4349L4.16517 20.9623L3.96864 21.5614L5.40927 22.034ZM5.32828 20.5833L4.81377 20.213L3.92817 21.4437L4.44268 21.8139L5.32828 20.5833ZM4.37097 21.5864H5.00694V20.0703H4.37097V21.5864ZM5.72726 21.0646L5.92379 20.4655L4.48316 19.993L4.28663 20.592L5.72726 21.0646Z" fill="#12D488" mask="url(#path-2-inside-1_3545_20611)"/>
								<mask id="path-4-inside-2_3545_20611" fill="white">
								<path d="M63.1244 24.7097L63.3209 25.3088H63.9569L63.4424 25.6791L63.6389 26.2782L63.1244 25.9079L62.6099 26.2782L62.8064 25.6791L62.2919 25.3088H62.9278L63.1244 24.7097Z"/>
								</mask>
								<path d="M63.1244 24.7097L63.8447 24.4734L63.1244 22.2776L62.4041 24.4734L63.1244 24.7097ZM63.3209 25.3088L62.6006 25.5451L62.7718 26.0669H63.3209V25.3088ZM63.9569 25.3088L64.3997 25.9241L66.3082 24.5507H63.9569V25.3088ZM63.4424 25.6791L62.9996 25.0637L62.549 25.388L62.722 25.9154L63.4424 25.6791ZM63.6389 26.2782L63.1961 26.8935L65.0843 28.2523L64.3592 26.0419L63.6389 26.2782ZM63.1244 25.9079L63.5672 25.2926L63.1244 24.9739L62.6816 25.2926L63.1244 25.9079ZM62.6099 26.2782L61.8895 26.0419L61.1644 28.2523L63.0527 26.8935L62.6099 26.2782ZM62.8064 25.6791L63.5267 25.9154L63.6997 25.388L63.2492 25.0637L62.8064 25.6791ZM62.2919 25.3088V24.5507H59.9406L61.8491 25.9241L62.2919 25.3088ZM62.9278 25.3088V26.0669H63.477L63.6482 25.5451L62.9278 25.3088ZM62.4041 24.946L62.6006 25.5451L64.0412 25.0725L63.8447 24.4734L62.4041 24.946ZM63.3209 26.0669H63.9569V24.5507H63.3209V26.0669ZM63.5141 24.6935L62.9996 25.0637L63.8852 26.2944L64.3997 25.9241L63.5141 24.6935ZM62.722 25.9154L62.9186 26.5145L64.3592 26.0419L64.1627 25.4428L62.722 25.9154ZM64.0817 25.6628L63.5672 25.2926L62.6816 26.5232L63.1961 26.8935L64.0817 25.6628ZM62.6816 25.2926L62.1671 25.6628L63.0527 26.8935L63.5672 26.5232L62.6816 25.2926ZM63.3302 26.5145L63.5267 25.9154L62.0861 25.4428L61.8895 26.0419L63.3302 26.5145ZM63.2492 25.0637L62.7347 24.6935L61.8491 25.9241L62.3636 26.2944L63.2492 25.0637ZM62.2919 26.0669H62.9278V24.5507H62.2919V26.0669ZM63.6482 25.5451L63.8447 24.946L62.4041 24.4734L62.2075 25.0725L63.6482 25.5451Z" fill="#12D488" mask="url(#path-4-inside-2_3545_20611)"/>
								<mask id="path-6-inside-3_3545_20611" fill="white">
								<path d="M45.1791 58.3792L45.3756 58.9782H46.0116L45.497 59.3485L45.6936 59.9476L45.1791 59.5773L44.6645 59.9476L44.8611 59.3485L44.3466 58.9782H44.9825L45.1791 58.3792Z"/>
								</mask>
								<path d="M45.1791 58.3792L45.8994 58.1429L45.1791 55.9471L44.4587 58.1429L45.1791 58.3792ZM45.3756 58.9782L44.6553 59.2145L44.8264 59.7363H45.3756V58.9782ZM46.0116 58.9782L46.4544 59.5936L48.3629 58.2202H46.0116V58.9782ZM45.497 59.3485L45.0542 58.7332L44.6037 59.0574L44.7767 59.5848L45.497 59.3485ZM45.6936 59.9476L45.2508 60.5629L47.139 61.9217L46.4139 59.7113L45.6936 59.9476ZM45.1791 59.5773L45.6219 58.962L45.1791 58.6434L44.7363 58.962L45.1791 59.5773ZM44.6645 59.9476L43.9442 59.7113L43.2191 61.9217L45.1073 60.5629L44.6645 59.9476ZM44.8611 59.3485L45.5814 59.5848L45.7544 59.0574L45.3039 58.7332L44.8611 59.3485ZM44.3466 58.9782V58.2202H41.9953L43.9038 59.5936L44.3466 58.9782ZM44.9825 58.9782V59.7363H45.5317L45.7028 59.2145L44.9825 58.9782ZM44.4587 58.6154L44.6553 59.2145L46.0959 58.7419L45.8994 58.1429L44.4587 58.6154ZM45.3756 59.7363H46.0116V58.2202H45.3756V59.7363ZM45.5688 58.3629L45.0542 58.7332L45.9398 59.9638L46.4544 59.5936L45.5688 58.3629ZM44.7767 59.5848L44.9733 60.1839L46.4139 59.7113L46.2174 59.1122L44.7767 59.5848ZM46.1364 59.3323L45.6219 58.962L44.7363 60.1927L45.2508 60.5629L46.1364 59.3323ZM44.7363 58.962L44.2217 59.3323L45.1073 60.5629L45.6219 60.1927L44.7363 58.962ZM45.3849 60.1839L45.5814 59.5848L44.1408 59.1122L43.9442 59.7113L45.3849 60.1839ZM45.3039 58.7332L44.7894 58.3629L43.9038 59.5936L44.4183 59.9638L45.3039 58.7332ZM44.3466 59.7363H44.9825V58.2202H44.3466V59.7363ZM45.7028 59.2145L45.8994 58.6154L44.4587 58.1429L44.2622 58.7419L45.7028 59.2145Z" fill="#F5AE41" mask="url(#path-6-inside-3_3545_20611)"/>
								<mask id="path-8-inside-4_3545_20611" fill="white">
								<ellipse cx="29.8593" cy="0.72251" rx="0.729456" ry="0.72251"/>
								</mask>
								<path d="M29.8307 0.72251C29.8307 0.717947 29.8318 0.711852 29.8343 0.706027C29.8366 0.700729 29.8393 0.697136 29.8417 0.694797C29.844 0.69246 29.8469 0.690492 29.8504 0.689036C29.8543 0.687377 29.8578 0.686936 29.8593 0.686936V2.2031C30.674 2.2031 31.3469 1.54704 31.3469 0.72251H29.8307ZM29.8593 0.686936C29.8609 0.686936 29.8644 0.687377 29.8683 0.689036C29.8718 0.690492 29.8747 0.69246 29.877 0.694797C29.8794 0.697136 29.8821 0.700729 29.8844 0.706027C29.8869 0.711852 29.888 0.717947 29.888 0.72251H28.3718C28.3718 1.54704 29.0446 2.2031 29.8593 2.2031V0.686936ZM29.888 0.72251C29.888 0.727072 29.8869 0.733167 29.8844 0.738993C29.8821 0.74429 29.8794 0.747883 29.877 0.750223C29.8747 0.752559 29.8718 0.754527 29.8683 0.755983C29.8644 0.757642 29.8609 0.758084 29.8593 0.758084V-0.758084C29.0446 -0.758084 28.3718 -0.102022 28.3718 0.72251H29.888ZM29.8593 0.758084C29.8578 0.758084 29.8543 0.757642 29.8504 0.755983C29.8469 0.754527 29.844 0.752559 29.8417 0.750223C29.8393 0.747883 29.8366 0.74429 29.8343 0.738993C29.8318 0.733167 29.8307 0.727072 29.8307 0.72251H31.3469C31.3469 -0.102022 30.674 -0.758084 29.8593 -0.758084V0.758084Z" fill="#EE2445" fill-opacity="0.7" mask="url(#path-8-inside-4_3545_20611)"/>
								<mask id="path-10-inside-5_3545_20611" fill="white">
								<ellipse cx="59.7685" cy="41.9051" rx="0.729456" ry="0.72251"/>
								</mask>
								<path d="M59.7399 41.9051C59.7399 41.9006 59.741 41.8945 59.7435 41.8886C59.7457 41.8833 59.7485 41.8798 59.7508 41.8774C59.7532 41.8751 59.7561 41.8731 59.7595 41.8717C59.7635 41.87 59.767 41.8696 59.7685 41.8696V43.3857C60.5832 43.3857 61.2561 42.7297 61.2561 41.9051H59.7399ZM59.7685 41.8696C59.77 41.8696 59.7735 41.87 59.7775 41.8717C59.781 41.8731 59.7838 41.8751 59.7862 41.8774C59.7886 41.8798 59.7913 41.8833 59.7936 41.8886C59.7961 41.8945 59.7971 41.9006 59.7971 41.9051H58.281C58.281 42.7297 58.9538 43.3857 59.7685 43.3857V41.8696ZM59.7971 41.9051C59.7971 41.9097 59.7961 41.9158 59.7936 41.9216C59.7913 41.9269 59.7886 41.9305 59.7862 41.9328C59.7838 41.9352 59.781 41.9371 59.7775 41.9386C59.7735 41.9403 59.77 41.9407 59.7685 41.9407V40.4245C58.9538 40.4245 58.281 41.0806 58.281 41.9051H59.7971ZM59.7685 41.9407C59.767 41.9407 59.7635 41.9403 59.7595 41.9386C59.7561 41.9371 59.7532 41.9352 59.7508 41.9328C59.7485 41.9305 59.7457 41.9269 59.7435 41.9216C59.741 41.9158 59.7399 41.9097 59.7399 41.9051H61.2561C61.2561 41.0806 60.5832 40.4245 59.7685 40.4245V41.9407Z" fill="#EE2445" fill-opacity="0.6" mask="url(#path-10-inside-5_3545_20611)"/>
								<mask id="path-12-inside-6_3545_20611" fill="white">
								<ellipse cx="1.12008" cy="41.9051" rx="0.729456" ry="0.72251"/>
								</mask>
								<path d="M1.09145 41.9051C1.09145 41.9006 1.09255 41.8945 1.09504 41.8886C1.0973 41.8833 1.10005 41.8798 1.10241 41.8774C1.10477 41.8751 1.10762 41.8731 1.1111 41.8717C1.11506 41.87 1.11856 41.8696 1.12008 41.8696V43.3857C1.93477 43.3857 2.60762 42.7297 2.60762 41.9051H1.09145ZM1.12008 41.8696C1.1216 41.8696 1.12511 41.87 1.12907 41.8717C1.13254 41.8731 1.13539 41.8751 1.13775 41.8774C1.14012 41.8798 1.14286 41.8833 1.14513 41.8886C1.14761 41.8945 1.14871 41.9006 1.14871 41.9051H-0.367459C-0.367459 42.7297 0.305391 43.3857 1.12008 43.3857V41.8696ZM1.14871 41.9051C1.14871 41.9097 1.14761 41.9158 1.14513 41.9216C1.14286 41.9269 1.14012 41.9305 1.13775 41.9328C1.13539 41.9352 1.13254 41.9371 1.12907 41.9386C1.12511 41.9403 1.1216 41.9407 1.12008 41.9407V40.4245C0.305391 40.4245 -0.367459 41.0806 -0.367459 41.9051H1.14871ZM1.12008 41.9407C1.11856 41.9407 1.11506 41.9403 1.1111 41.9386C1.10762 41.9371 1.10477 41.9352 1.10241 41.9328C1.10005 41.9305 1.0973 41.9269 1.09504 41.9216C1.09255 41.9158 1.09145 41.9097 1.09145 41.9051H2.60762C2.60762 41.0806 1.93477 40.4245 1.12008 40.4245V41.9407Z" fill="#EE2445" fill-opacity="0.6" mask="url(#path-12-inside-6_3545_20611)"/>
								<path d="M50.4083 7.51367H50.7873C50.7873 7.31457 50.6333 7.1494 50.4346 7.13555C50.236 7.12169 50.0605 7.26387 50.0329 7.46105L50.4083 7.51367ZM49.7031 8.20747V7.82843C49.5013 7.82843 49.3348 7.98665 49.3246 8.18826C49.3143 8.38987 49.4639 8.56412 49.6647 8.58457L49.7031 8.20747ZM50.4083 8.95869H50.0292C50.0292 9.16561 50.1952 9.33429 50.4021 9.33768C50.6089 9.34107 50.7803 9.17792 50.7871 8.97111L50.4083 8.95869ZM51.162 8.21704V8.59608C51.3621 8.59608 51.5277 8.44061 51.5403 8.24096C51.5529 8.04131 51.4082 7.86621 51.2098 7.84102L51.162 8.21704ZM50.0329 7.46105C50.0239 7.52554 49.9892 7.63353 49.9257 7.71585C49.8713 7.78644 49.8065 7.82843 49.7031 7.82843V8.58651C50.086 8.58651 50.3576 8.39724 50.5261 8.17874C50.6855 7.97198 50.7603 7.73307 50.7836 7.56629L50.0329 7.46105ZM49.6647 8.58457C49.7211 8.59031 49.8253 8.62147 49.9067 8.68677C49.9746 8.74127 50.0292 8.81881 50.0292 8.95869H50.7873C50.7873 8.56267 50.6069 8.27657 50.381 8.09541C50.1686 7.92506 49.9202 7.84856 49.7415 7.83038L49.6647 8.58457ZM50.7871 8.97111C50.7892 8.90717 50.8146 8.80086 50.8739 8.72082C50.9201 8.65842 50.9957 8.59608 51.162 8.59608V7.838C50.7448 7.838 50.4475 8.02288 50.2648 8.2695C50.0951 8.49848 50.0354 8.76299 50.0294 8.94627L50.7871 8.97111ZM51.2098 7.84102C51.1336 7.83135 51.0076 7.79316 50.9111 7.72427C50.824 7.66216 50.7873 7.59799 50.7873 7.51367H50.0292C50.0292 7.90784 50.2438 8.1794 50.4708 8.34138C50.6883 8.49658 50.9392 8.57083 51.1143 8.59307L51.2098 7.84102Z" fill="#F4B125"/>
								<line x1="50.1981" y1="6.70079" x2="50.1981" y2="6.73637" stroke="#F4B125" stroke-width="0.758084" stroke-linecap="round"/>
								<line x1="50.1981" y1="10.3133" x2="50.1981" y2="9.91541" stroke="#F4B125" stroke-width="0.758084" stroke-linecap="round"/>
								<path d="M51.5977 8.25439H52.473" stroke="#F4B125" stroke-width="0.758084" stroke-linecap="round"/>
								<path d="M48.3887 8.25439H48.9358" stroke="#F4B125" stroke-width="0.758084" stroke-linecap="round"/>
								<path d="M48.254 27.5063C47.626 26.8577 46.9766 26.1872 46.7313 25.5996C46.5058 25.0598 46.4922 24.1658 46.479 23.3011C46.4545 21.692 46.4267 19.8683 45.1466 18.6004C43.8665 17.3325 42.0252 17.3049 40.4006 17.2806C39.5276 17.2676 38.625 17.254 38.08 17.0307C37.487 16.7877 36.8099 16.1446 36.155 15.5226C35.0048 14.4298 33.7008 13.1914 31.9625 13.1914C30.2241 13.1914 28.9202 14.4298 27.7699 15.5226C27.115 16.1446 26.4381 16.7877 25.8449 17.0307C25.2999 17.254 24.3973 17.2676 23.5243 17.2806C21.8997 17.3049 20.0584 17.3325 18.7783 18.6004C17.4983 19.8683 17.4705 21.692 17.4459 23.3011C17.4327 24.1658 17.4191 25.0598 17.1936 25.5996C16.9483 26.187 16.299 26.8577 15.6709 27.5063C14.5677 28.6455 13.3174 29.9371 13.3174 31.6589C13.3174 33.3807 14.5677 34.6722 15.6709 35.8116C16.299 36.4602 16.9483 37.1307 17.1936 37.7183C17.4191 38.258 17.4327 39.152 17.4459 40.0167C17.4705 41.6258 17.4983 43.4496 18.7783 44.7175C20.0584 45.9854 21.8997 46.0129 23.5243 46.0372C24.3973 46.0503 25.2999 46.0638 25.8449 46.2871C26.4379 46.5301 27.115 47.1733 27.7699 47.7953C28.9201 48.888 30.2241 50.1264 31.9625 50.1264C33.7008 50.1264 35.0047 48.888 36.155 47.7953C36.8099 47.1733 37.4868 46.5301 38.08 46.2871C38.625 46.0638 39.5276 46.0503 40.4006 46.0372C42.0252 46.0129 43.8665 45.9854 45.1466 44.7175C46.4267 43.4496 46.4545 41.6258 46.479 40.0167C46.4922 39.152 46.5058 38.258 46.7313 37.7183C46.9766 37.1308 47.6259 36.4602 48.254 35.8116C49.3572 34.6723 50.6075 33.3807 50.6075 31.6589C50.6075 29.9371 49.3572 28.6457 48.254 27.5063ZM40.2068 28.6558L30.4403 37.8896C30.1926 38.1238 29.8633 38.2545 29.5208 38.2545C29.1783 38.2545 28.849 38.1238 28.6013 37.8896L23.7181 33.2727C23.5915 33.1531 23.4899 33.01 23.4192 32.8515C23.3485 32.6931 23.31 32.5223 23.3058 32.3491C23.3017 32.1759 23.3321 32.0036 23.3953 31.842C23.4584 31.6804 23.5531 31.5327 23.6739 31.4074C23.7946 31.2821 23.9392 31.1815 24.0992 31.1115C24.2592 31.0415 24.4316 31.0034 24.6065 30.9994C24.7813 30.9954 24.9553 31.0256 25.1184 31.0882C25.2815 31.1508 25.4306 31.2446 25.5571 31.3642L29.5208 35.1118L38.3678 26.7474C38.4943 26.6277 38.6434 26.5339 38.8065 26.4713C38.9696 26.4087 39.1436 26.3785 39.3184 26.3825C39.4933 26.3865 39.6657 26.4246 39.8257 26.4946C39.9857 26.5646 40.1303 26.6652 40.2511 26.7905C40.3718 26.9159 40.4665 27.0635 40.5296 27.2251C40.5928 27.3867 40.6232 27.559 40.6191 27.7322C40.615 27.9055 40.5764 28.0762 40.5057 28.2347C40.435 28.3931 40.3334 28.5362 40.2068 28.6558Z" fill="#12D488"/>
							</svg>
							<h3><?php esc_html_e('Congratulations!', 'bookingpress-appointment-booking'); ?></h3>
							<p><?php esc_html_e('Hurray!! Everything is ready to accept your first online booking now', 'bookingpress-appointment-booking'); ?></p>
						</div>
						<div class="bpa-fsb__body">
							<div class="bpa-fsb-site-detail-item-row">
								<h4><?php esc_html_e('Your site is ready & below are the details', 'bookingpress-appointment-booking'); ?></h4>
								<div class="bpa-sd__items">
									<div class="bpa-sd__item">
										<p><?php echo esc_html(BOOKINGPRESS_HOME_URL."/book-appointment"); ?></p>
										<span class="material-icons-round" @click="bookingpress_copy_content('<?php echo esc_html(BOOKINGPRESS_HOME_URL."/book-appointment"); ?>')">content_copy</span>
									</div>
									<div class="bpa-sd__item">
										<p><?php echo esc_html(BOOKINGPRESS_HOME_URL."/my-bookings"); ?></p>
										<span class="material-icons-round" @click="bookingpress_copy_content('<?php echo esc_html(BOOKINGPRESS_HOME_URL."/my-bookings"); ?>')">content_copy</span>
									</div>
								</div>
							</div>
							<div class="bpa-fsb-site-detail-separator">
								<span>or</span>
							</div>
							<div class="bpa-fsb-site-detail-item-row __bpa-has-primary-tone">
								<div class="bpa-sd__items">
									<div class="bpa-sd__item">
										<p><?php esc_html_e('[bookingpress_form]', 'bookingpress-appointment-booking'); ?></p>
										<span class="material-icons-round" @click="bookingpress_copy_content('[bookingpress_form]')">content_copy</span>
									</div>
									<div class="bpa-sd__item">
										<p><?php esc_html_e('[bookingpress_my_appointments]', 'bookingpress-appointment-booking'); ?></p>
										<span class="material-icons-round" @click="bookingpress_copy_content('[bookingpress_my_appointments]')">content_copy</span>
									</div>
								</div>
							</div>
							<div class="bpa-fsb-site-detail-item-row __bpa-has-btns">
								<h4><?php esc_html_e('Useful Links', 'bookingpress-appointment-booking'); ?></h4>
								<div class="bpa-sdi__btn-group">
									<el-button class="bpa-btn bpa-btn__medium" @click="window.open('https://www.bookingpressplugin.com/documents/installing-updating-bookingpress/', '_blank')">
										<?php esc_html_e( 'Documentation', 'bookingpress-appointment-booking' ); ?>
									</el-button>
									<el-button class="bpa-btn bpa-btn__medium" @click="window.open('https://www.bookingpressplugin.com/contact-us/', '_blank')">
										<?php esc_html_e( 'Get 24x7 Support', 'bookingpress-appointment-booking' ); ?>
									</el-button>
									<el-button class="bpa-btn bpa-btn__medium" @click="window.open('https://www.bookingpressplugin.com/features/', '_blank')">
										<?php esc_html_e( 'Explore Features', 'bookingpress-appointment-booking' ); ?>
									</el-button>
								</div>
								<div class="bpa-sdi__social-links">
									<a href="https://www.facebook.com/BookingPressPlugin" target="_blank"><img src="<?php echo esc_url(BOOKINGPRESS_IMAGES_URL . '/fb-filled-circle.png'); ?>"></a>
									<a href="https://www.youtube.com/c/BookingPress" target="_blank"><img src="<?php echo esc_url(BOOKINGPRESS_IMAGES_URL . '/yt-filled-circle.png'); ?>"></a>
								</div>
							</div>
						</div>
					</div>
					<div class="bpa-tpb__foot-action-btns">
						<div class="bpa-fab--wrapper">
							<el-button class="bpa-btn bpa-btn__medium" @click="bookingpress_skip_wizard">
								<?php esc_html_e( 'Close', 'bookingpress-appointment-booking' ); ?>
							</el-button>
						</div>
					</div>
				</div>
			</el-tab-pane>
		</el-tabs>
	</div>
	<div class="bpa-fws__footer" v-if="bookingpress_active_tab != 'final_step'">
		<a href="javascript:void(0)" @click="bookingpress_skip_wizard"><?php esc_html_e('Close and Exit Wizard Without Saving', 'bookingpress-appointment-booking'); ?></a>
	</div>
</el-main>